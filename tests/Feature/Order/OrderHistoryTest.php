<?php

namespace Tests\Feature\Order;

use App\Enums\DeliveryMethod;
use App\Enums\RoleName;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
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

    private function placeOrder(User $buyer, Store $store, int $price = 50_000): Order
    {
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => $price, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        return app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);
    }

    public function test_buyer_only_sees_their_own_orders(): void
    {
        $buyerA = $this->userWithRole(RoleName::Buyer);
        $buyerB = $this->userWithRole(RoleName::Buyer);
        $store = Store::factory()->create();

        $orderA = $this->placeOrder($buyerA, $store);
        $this->placeOrder($buyerB, $store);

        $response = $this->actingAsRole($buyerA, RoleName::Buyer)->get(route('buyer.orders.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('orders.total', 1)
            ->where('orders.data.0.id', $orderA->id)
        );
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        $owner = $this->userWithRole(RoleName::Buyer);
        $other = $this->userWithRole(RoleName::Buyer);
        $store = Store::factory()->create();

        $order = $this->placeOrder($owner, $store);

        $response = $this->actingAsRole($other, RoleName::Buyer)->get(route('buyer.orders.show', $order));

        $response->assertForbidden();
    }

    public function test_order_detail_includes_items_and_status_history(): void
    {
        $buyer = $this->userWithRole(RoleName::Buyer);
        $store = Store::factory()->create();

        $order = $this->placeOrder($buyer, $store);

        $response = $this->actingAsRole($buyer, RoleName::Buyer)->get(route('buyer.orders.show', $order));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('order.items.0.quantity', 1)
            ->where('order.status_histories.0.status', 'sedang_dikemas')
        );
    }

    public function test_seller_only_sees_orders_for_their_own_store(): void
    {
        $sellerA = $this->userWithRole(RoleName::Seller);
        $sellerB = $this->userWithRole(RoleName::Seller);
        $storeA = Store::factory()->create(['user_id' => $sellerA->id]);
        $storeB = Store::factory()->create(['user_id' => $sellerB->id]);
        $buyer = $this->userWithRole(RoleName::Buyer);

        $orderA = $this->placeOrder($buyer, $storeA);
        $this->placeOrder($buyer, $storeB);

        $response = $this->actingAsRole($sellerA, RoleName::Seller)->get(route('seller.orders.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('orders.total', 1)
            ->where('orders.data.0.id', $orderA->id)
        );
    }
}
