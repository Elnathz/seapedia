<?php

namespace Tests\Feature\Delivery;

use App\Enums\DeliveryMethod;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Tests\TestCase;
use Throwable;

/**
 * Proves the §6 double-take guard (the plan's "CRITICAL" test for T2) is a
 * real database-level row lock, not just sequential logic — same proof
 * technique as CheckoutConcurrencyTest: DatabaseTruncation commits for
 * real, so a genuinely separate connection can contend for the same row.
 */
class DeliveryConcurrencyTest extends TestCase
{
    use DatabaseTruncation;

    protected function tearDown(): void
    {
        $this->truncateTablesForAllConnections();

        parent::tearDown();
    }

    private function driver(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Driver->value])->id,
        );

        return $user;
    }

    private function availableJob(): Delivery
    {
        $sellerRole = Role::query()->firstOrCreate(['name' => RoleName::Seller->value]);
        $seller = User::factory()->create();
        $seller->roles()->attach($sellerRole->id);
        $store = Store::factory()->create(['user_id' => $seller->id]);

        $buyerRole = Role::query()->firstOrCreate(['name' => RoleName::Buyer->value]);
        $buyer = User::factory()->create();
        $buyer->roles()->attach($buyerRole->id);
        $buyer->wallet->update(['balance' => 1_000_000]);

        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, 1);
        $order = app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);

        $this->actingAs($seller)
            ->withSession(['active_role' => RoleName::Seller->value])
            ->post(route('seller.orders.process', $order));

        return Delivery::query()->where('order_id', $order->id)->firstOrFail();
    }

    public function test_a_second_take_cannot_read_the_delivery_row_while_the_first_holds_its_lock(): void
    {
        $delivery = $this->availableJob();
        $driver = $this->driver();

        config(['database.connections.lock_holder' => config('database.connections.mysql')]);
        $holder = DB::connection('lock_holder');
        $holder->beginTransaction();
        $holder->select('select * from deliveries where id = ? for update', [$delivery->id]);

        DB::statement('SET SESSION innodb_lock_wait_timeout = 2');

        $blocked = false;

        try {
            app(DeliveryService::class)->take($delivery, $driver);
        } catch (Throwable) {
            $blocked = true;
        } finally {
            $holder->rollBack();
            DB::statement('SET SESSION innodb_lock_wait_timeout = DEFAULT');
        }

        $this->assertTrue($blocked, 'Expected take() to block while another transaction held the delivery row lock.');
        $this->assertNull($delivery->refresh()->driver_id);
    }

    public function test_once_a_job_is_taken_a_second_driver_is_rejected_with_driver_id_set_once(): void
    {
        $delivery = $this->availableJob();
        $driverA = $this->driver();
        $driverB = $this->driver();

        app(DeliveryService::class)->take($delivery, $driverA);

        $this->assertSame($driverA->id, $delivery->refresh()->driver_id);

        $this->expectException(ConflictHttpException::class);

        try {
            app(DeliveryService::class)->take($delivery, $driverB);
        } finally {
            $this->assertSame($driverA->id, $delivery->refresh()->driver_id);
        }
    }
}
