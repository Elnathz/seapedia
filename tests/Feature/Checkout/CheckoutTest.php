<?php

namespace Tests\Feature\Checkout;

use App\Enums\OrderStatus;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
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

    private function actingAsBuyer(User $user)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value]);
    }

    public function test_checkout_charges_wallet_reduces_stock_and_creates_order(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, 2);

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'regular',
        ]);

        $response->assertRedirect(route('buyer.cart.index'));

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(OrderStatus::SedangDikemas, $order->status);
        $this->assertSame(100_000, $order->subtotal);
        $this->assertSame(12_000, $order->tax_amount);
        $this->assertSame(5_000, $order->delivery_fee);
        $this->assertSame(117_000, $order->grand_total);
        $this->assertSame(100_000, $order->seller_income_amount);

        $this->assertSame(8, $product->refresh()->stock);
        $this->assertSame(1_000_000 - 117_000, $buyer->wallet->refresh()->balance);
        $this->assertSame(100_000, $store->user->wallet->refresh()->balance);

        $this->assertSame(0, CartItem::query()->count());
        $this->assertSame(1, $order->statusHistories()->count());
    }

    public function test_insufficient_balance_blocks_checkout_with_zero_side_effects(): void
    {
        $buyer = $this->buyer(balance: 1_000);
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, 1);

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'regular',
        ]);

        $response->assertInvalid(['balance']);

        $this->assertSame(0, Order::query()->count());
        $this->assertSame(10, $product->refresh()->stock);
        $this->assertSame(1_000, $buyer->wallet->refresh()->balance);
        $this->assertSame(0, $store->user->wallet->refresh()->balance);
        $this->assertSame(1, CartItem::query()->count());
    }

    public function test_out_of_stock_blocks_checkout(): void
    {
        $buyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 1]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, 5);

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.checkout.store'), [
            'address_id' => $address->id,
            'delivery_method' => 'regular',
        ]);

        $response->assertInvalid(['stock']);
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(1, $product->refresh()->stock);
    }

    public function test_checking_out_with_someone_elses_address_is_forbidden(): void
    {
        $buyer = $this->buyer();
        $otherBuyer = $this->buyer();
        $store = Store::factory()->create();
        $product = Product::factory()->create(['store_id' => $store->id]);
        $foreignAddress = Address::factory()->create(['user_id' => $otherBuyer->id]);

        app(CartService::class)->addItem($buyer, $product, 1);

        $response = $this->actingAsBuyer($buyer)->post(route('buyer.checkout.store'), [
            'address_id' => $foreignAddress->id,
            'delivery_method' => 'regular',
        ]);

        $response->assertForbidden();
        $this->assertSame(0, Order::query()->count());
    }
}
