<?php

namespace Tests\Feature\Api;

use App\Enums\DeliveryMethod;
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
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user, RoleName $role): string
    {
        return app(RoleService::class)->issueApiToken($user, $role)->plainTextToken;
    }

    /**
     * Sanctum's RequestGuard caches the resolved user for the lifetime of
     * the test app — forgetGuards() is required before switching bearer
     * tokens within a single test method, or the previous actor "sticks".
     */
    private function asToken(string $token): static
    {
        app('auth')->forgetGuards();

        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    private function placeOrder(User $buyer, Store $store, int $price = 50_000): Order
    {
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => $price, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);

        return app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);
    }

    public function test_api_buyer_report_is_scoped_to_their_own_orders(): void
    {
        $buyerA = User::factory()->create();
        $buyerA->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id);
        $buyerB = User::factory()->create();
        $buyerB->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id);
        $store = Store::factory()->create();

        $orderA = $this->placeOrder($buyerA, $store);
        $this->placeOrder($buyerB, $store, 100_000);

        $token = $this->tokenFor($buyerA, RoleName::Buyer);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.buyer.reports.index'));

        $response->assertOk();
        $response->assertJsonPath('order_count', 1);
        $response->assertJsonPath('total_spent', $orderA->grand_total);
    }

    public function test_api_seller_report_is_scoped_to_their_own_store(): void
    {
        $sellerA = User::factory()->create();
        $sellerA->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id);
        $sellerB = User::factory()->create();
        $sellerB->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id);
        $storeA = Store::factory()->create(['user_id' => $sellerA->id]);
        $storeB = Store::factory()->create(['user_id' => $sellerB->id]);
        $buyer = User::factory()->create();
        $buyer->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id);
        $driver = User::factory()->create();
        $driver->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Driver->value])->id);

        // Income is only realized once the order reaches Pesanan Selesai
        // under escrow (Sprint 5 Decision 3) — drive order A through the
        // full delivery flow before asserting the report total.
        $orderA = $this->placeOrder($buyer, $storeA);
        $this->placeOrder($buyer, $storeB, 100_000);

        $this->asToken($this->tokenFor($sellerA, RoleName::Seller))
            ->postJson(route('api.v1.seller.orders.process', $orderA))
            ->assertOk();

        $delivery = Delivery::query()->where('order_id', $orderA->id)->firstOrFail();

        $driverToken = $this->tokenFor($driver, RoleName::Driver);
        $this->asToken($driverToken)->postJson(route('api.v1.driver.jobs.take', $delivery))->assertOk();
        $this->asToken($driverToken)->postJson(route('api.v1.driver.jobs.complete', $delivery))->assertOk();

        $response = $this->asToken($this->tokenFor($sellerA, RoleName::Seller))
            ->getJson(route('api.v1.seller.reports.index'));

        $response->assertOk();
        $response->assertJsonPath('order_count', 1);
        $response->assertJsonPath('total_income', $orderA->seller_income_amount);
    }
}
