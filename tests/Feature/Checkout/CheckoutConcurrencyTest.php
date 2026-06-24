<?php

namespace Tests\Feature\Checkout;

use App\Enums\DeliveryMethod;
use App\Enums\DiscountType;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Throwable;

/**
 * Proves the checkout oversell guard (§6, the plan's "CRITICAL" test) is a
 * real database-level lock, not just sequential logic. RefreshDatabase
 * wraps each test in an uncommitted transaction, which would hide data from
 * a second raw connection — DatabaseTruncation commits for real instead, so
 * a genuinely separate connection can contend for the same row.
 */
class CheckoutConcurrencyTest extends TestCase
{
    use DatabaseTruncation;

    /**
     * DatabaseTruncation commits real rows instead of rolling back like
     * RefreshDatabase, so without this, data created here (e.g. the
     * "buyer" role) would leak into whichever test runs next.
     */
    protected function tearDown(): void
    {
        $this->truncateTablesForAllConnections();

        parent::tearDown();
    }

    private function buyer(int $balance = 1_000_000): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );
        $user->wallet->update(['balance' => $balance]);

        return $user;
    }

    public function test_a_second_checkout_cannot_read_the_product_row_while_the_first_holds_its_lock(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 1]);
        $buyer = $this->buyer();
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);
        app(CartService::class)->addItem($buyer, $product, 1);

        config(['database.connections.lock_holder' => config('database.connections.mysql')]);
        $holder = DB::connection('lock_holder');
        $holder->beginTransaction();
        $holder->select('select * from products where id = ? for update', [$product->id]);

        DB::statement('SET SESSION innodb_lock_wait_timeout = 2');

        $blocked = false;

        try {
            app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);
        } catch (Throwable) {
            $blocked = true;
        } finally {
            $holder->rollBack();
            DB::statement('SET SESSION innodb_lock_wait_timeout = DEFAULT');
        }

        $this->assertTrue($blocked, 'Expected the checkout to block while another transaction held the product row lock.');
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(1, $product->refresh()->stock);
    }

    public function test_once_the_first_checkout_commits_a_second_buyer_is_rejected_with_no_oversell(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 1]);

        $buyerA = $this->buyer();
        $addressA = Address::factory()->create(['user_id' => $buyerA->id, 'is_default' => true]);
        app(CartService::class)->addItem($buyerA, $product, 1);

        $buyerB = $this->buyer();
        $addressB = Address::factory()->create(['user_id' => $buyerB->id, 'is_default' => true]);
        app(CartService::class)->addItem($buyerB, $product, 1);

        // Buyer A's checkout completes first and consumes the last unit.
        app(CheckoutService::class)->commit($buyerA, $addressA, DeliveryMethod::Regular);

        $this->assertSame(0, $product->refresh()->stock);

        // Buyer B's checkout — for the same last unit — must now be
        // rejected cleanly, never driving stock negative.
        $this->expectException(ValidationException::class);

        try {
            app(CheckoutService::class)->commit($buyerB, $addressB, DeliveryMethod::Regular);
        } finally {
            $this->assertSame(1, Order::query()->count());
            $this->assertSame(0, $product->refresh()->stock);
        }
    }

    public function test_a_second_checkout_cannot_read_the_voucher_row_while_the_first_holds_its_lock(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $voucher = Voucher::factory()->create([
            'type' => DiscountType::Fixed, 'value' => 5_000, 'max_discount' => null,
            'min_spend' => null, 'usage_limit' => 1, 'used_count' => 0,
        ]);
        $buyer = $this->buyer();
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);
        app(CartService::class)->addItem($buyer, $product, 1);

        config(['database.connections.lock_holder' => config('database.connections.mysql')]);
        $holder = DB::connection('lock_holder');
        $holder->beginTransaction();
        $holder->select('select * from vouchers where id = ? for update', [$voucher->id]);

        DB::statement('SET SESSION innodb_lock_wait_timeout = 2');

        $blocked = false;

        try {
            app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular, null, $voucher->code);
        } catch (Throwable) {
            $blocked = true;
        } finally {
            $holder->rollBack();
            DB::statement('SET SESSION innodb_lock_wait_timeout = DEFAULT');
        }

        $this->assertTrue($blocked, 'Expected the checkout to block while another transaction held the voucher row lock.');
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(0, $voucher->refresh()->used_count);
    }

    public function test_once_a_voucher_is_redeemed_a_second_checkout_cannot_exceed_its_usage_limit(): void
    {
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $voucher = Voucher::factory()->create([
            'type' => DiscountType::Fixed, 'value' => 5_000, 'max_discount' => null,
            'min_spend' => null, 'usage_limit' => 1, 'used_count' => 0,
        ]);

        $buyerA = $this->buyer();
        $addressA = Address::factory()->create(['user_id' => $buyerA->id, 'is_default' => true]);
        app(CartService::class)->addItem($buyerA, $product, 1);

        $buyerB = $this->buyer();
        $addressB = Address::factory()->create(['user_id' => $buyerB->id, 'is_default' => true]);
        app(CartService::class)->addItem($buyerB, $product, 1);

        // Buyer A redeems the voucher's last use first.
        app(CheckoutService::class)->commit($buyerA, $addressA, DeliveryMethod::Regular, null, $voucher->code);

        $this->assertSame(1, $voucher->refresh()->used_count);

        // Buyer B's checkout — for the same exhausted voucher — must be
        // rejected cleanly, never pushing used_count past usage_limit.
        $this->expectException(ValidationException::class);

        try {
            app(CheckoutService::class)->commit($buyerB, $addressB, DeliveryMethod::Regular, null, $voucher->code);
        } finally {
            $this->assertSame(1, Order::query()->count());
            $this->assertSame(1, $voucher->refresh()->used_count);
        }
    }
}
