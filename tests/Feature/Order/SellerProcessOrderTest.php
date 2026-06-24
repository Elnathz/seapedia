<?php

namespace Tests\Feature\Order;

use App\Enums\DeliveryMethod;
use App\Enums\DeliveryStatus;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerProcessOrderTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(RoleName $role): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => $role->value])->id,
        );

        return $user;
    }

    private function actingAsRole(User $user, RoleName $role)
    {
        return $this->actingAs($user)
            ->withSession(['active_role' => $role->value]);
    }

    private function placeOrder(Store $store): Order
    {
        $buyer = $this->userWithRole(RoleName::Buyer);
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, 1);

        return app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);
    }

    public function test_owning_seller_processes_an_order_and_writes_history(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $order = $this->placeOrder($store);

        $response = $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        $response->assertRedirect(route('seller.orders.show', $order));
        $this->assertSame('menunggu_pengirim', $order->refresh()->status->value);
        $this->assertSame(2, $order->statusHistories()->count());
        $this->assertSame('menunggu_pengirim', $order->statusHistories()->latest('id')->first()->status);
    }

    public function test_a_different_sellers_store_cannot_process_the_order(): void
    {
        $owner = $this->userWithRole(RoleName::Seller);
        $intruder = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $owner->id]);
        $order = $this->placeOrder($store);

        $response = $this->actingAsRole($intruder, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        $response->assertForbidden();
        $this->assertSame('sedang_dikemas', $order->refresh()->status->value);
    }

    public function test_processing_an_already_processed_order_is_rejected(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $order = $this->placeOrder($store);

        $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        $response = $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        $response->assertInvalid(['status']);
        $this->assertSame(1, Delivery::query()->where('order_id', $order->id)->count());
        $this->assertSame('menunggu_pengirim', $order->refresh()->status->value);
    }

    public function test_owning_seller_can_view_order_detail_with_items_and_timeline(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $order = $this->placeOrder($store);

        $response = $this->actingAsRole($seller, RoleName::Seller)
            ->get(route('seller.orders.show', $order));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('order.items.0.quantity', 1)
            ->where('order.status_histories.0.status', 'sedang_dikemas')
        );
    }

    public function test_a_different_sellers_store_cannot_view_the_order(): void
    {
        $owner = $this->userWithRole(RoleName::Seller);
        $intruder = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $owner->id]);
        $order = $this->placeOrder($store);

        $response = $this->actingAsRole($intruder, RoleName::Seller)
            ->get(route('seller.orders.show', $order));

        $response->assertForbidden();
    }

    public function test_processing_an_order_creates_exactly_one_available_delivery(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $order = $this->placeOrder($store);

        $this->assertSame(0, Delivery::query()->where('order_id', $order->id)->count());

        $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        $this->assertSame(1, Delivery::query()->where('order_id', $order->id)->count());
        $delivery = Delivery::query()->where('order_id', $order->id)->first();
        $this->assertSame(DeliveryStatus::Available, $delivery->status);
        $this->assertNull($delivery->driver_id);
    }

    public function test_an_unprocessed_order_has_no_delivery_row(): void
    {
        $seller = $this->userWithRole(RoleName::Seller);
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $order = $this->placeOrder($store);

        $this->assertSame(0, Delivery::query()->where('order_id', $order->id)->count());
    }
}
