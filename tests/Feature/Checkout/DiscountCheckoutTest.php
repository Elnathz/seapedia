<?php

namespace Tests\Feature\Checkout;

use App\Enums\DiscountType;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use App\Services\CartService;
use App\Services\DiscountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DiscountCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(int $balance = 1_000_000): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );
        $user->wallet->update(['balance' => $balance]);

        return $user;
    }

    private function checkout(User $buyer, array $extra = []): TestResponse
    {
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        return $this->actingAs($buyer)
            ->withSession(['active_role' => RoleName::Buyer->value])
            ->post(route('buyer.checkout.store'), [
                'address_id' => $address->id,
                'delivery_method' => 'regular',
                ...$extra,
            ]);
    }

    public function test_expired_promo_code_is_rejected_at_checkout(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100_000, 'stock' => 10]);
        $promo = Promo::factory()->expired()->create();

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->checkout($buyer, ['promo_code' => $promo->code]);

        $response->assertInvalid(['promo_code']);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_expired_voucher_code_is_rejected_at_checkout(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100_000, 'stock' => 10]);
        $voucher = Voucher::factory()->expired()->create();

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->checkout($buyer, ['voucher_code' => $voucher->code]);

        $response->assertInvalid(['voucher_code']);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_voucher_with_zero_remaining_usage_is_rejected(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100_000, 'stock' => 10]);
        $voucher = Voucher::factory()->state(['usage_limit' => 3])->usedUp()->create();

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->checkout($buyer, ['voucher_code' => $voucher->code]);

        $response->assertInvalid(['voucher_code']);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_min_spend_not_met_is_rejected(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 10_000, 'stock' => 10]);
        $promo = Promo::factory()->create(['min_spend' => 50_000]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->checkout($buyer, ['promo_code' => $promo->code]);

        $response->assertInvalid(['promo_code']);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_promo_and_voucher_combine_per_the_locked_rule(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 100_000, 'stock' => 10]);
        $promo = Promo::factory()->create(['type' => DiscountType::Percentage, 'value' => 10, 'max_discount' => null]);
        $voucher = Voucher::factory()->create(['type' => DiscountType::Fixed, 'value' => 5_000, 'max_discount' => null]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->checkout($buyer, [
            'promo_code' => $promo->code,
            'voucher_code' => $voucher->code,
        ]);

        $response->assertRedirect(route('buyer.cart.index'));

        $order = Order::query()->first();
        // subtotal 100_000; promo 10% = 10_000; voucher fixed = 5_000.
        // discount_total = min(10_000 + 5_000, 100_000) = 15_000.
        $this->assertSame(15_000, $order->discount_total);
        $this->assertSame($promo->id, $order->promo_id);
        $this->assertSame($voucher->id, $order->voucher_id);

        $taxableBase = 100_000 - 15_000;
        $this->assertSame((int) round($taxableBase * 0.12), $order->tax_amount);
        $this->assertSame($taxableBase + $order->tax_amount + 5_000, $order->grand_total);
        $this->assertSame(1, $voucher->refresh()->used_count);
    }

    public function test_discount_total_is_capped_at_the_subtotal(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 10_000, 'stock' => 10]);
        $promo = Promo::factory()->create(['type' => DiscountType::Fixed, 'value' => 8_000, 'max_discount' => null]);
        $voucher = Voucher::factory()->create(['type' => DiscountType::Fixed, 'value' => 8_000, 'max_discount' => null]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        $response = $this->checkout($buyer, [
            'promo_code' => $promo->code,
            'voucher_code' => $voucher->code,
        ]);

        $response->assertRedirect(route('buyer.cart.index'));

        $order = Order::query()->first();
        // promo 8_000 + voucher 8_000 = 16_000, but subtotal is only 10_000.
        $this->assertSame(10_000, $order->discount_total);
        $this->assertSame(0, $order->tax_amount);
        $this->assertSame(5_000, $order->grand_total);
    }

    public function test_capped_discount_splits_so_the_displayed_lines_sum_to_the_total(): void
    {
        $promo = Promo::factory()->create(['type' => DiscountType::Fixed, 'value' => 8_000, 'max_discount' => null, 'min_spend' => null]);
        $voucher = Voucher::factory()->create(['type' => DiscountType::Fixed, 'value' => 8_000, 'max_discount' => null, 'min_spend' => null]);

        $result = app(DiscountService::class)->resolve($promo->code, $voucher->code, 10_000);

        // promo + voucher (16_000) exceeds the 10_000 subtotal. Promo applies
        // first (8_000), the voucher absorbs the remaining 2_000, so the two
        // lines the buyer sees reconcile exactly with discount_total — no
        // line overstating the real deduction.
        $this->assertSame(10_000, $result['discount_total']);
        $this->assertSame(8_000, $result['promo_amount']);
        $this->assertSame(2_000, $result['voucher_amount']);
        $this->assertSame(
            $result['discount_total'],
            $result['promo_amount'] + $result['voucher_amount'],
        );
    }
}
