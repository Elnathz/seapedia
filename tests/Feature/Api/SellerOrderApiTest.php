<?php

namespace Tests\Feature\Api;

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
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private function sellerWithToken(): array
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id,
        );

        $token = app(RoleService::class)->issueApiToken($user, RoleName::Seller)->plainTextToken;

        return [$user, $token];
    }

    private function placeOrder(Store $store): Order
    {
        $buyer = User::factory()->create();
        $buyer->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, 1);

        return app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);
    }

    public function test_api_owning_seller_processes_an_order(): void
    {
        [$seller, $token] = $this->sellerWithToken();
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $order = $this->placeOrder($store);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.seller.orders.process', $order));

        $response->assertOk();
        $response->assertJsonPath('status', 'menunggu_pengirim');
    }

    public function test_api_a_different_sellers_store_cannot_process_the_order(): void
    {
        $owner = User::factory()->create();
        $owner->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id,
        );
        $store = Store::factory()->create(['user_id' => $owner->id]);
        $order = $this->placeOrder($store);

        [, $token] = $this->sellerWithToken();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.seller.orders.process', $order));

        $response->assertForbidden();
    }
}
