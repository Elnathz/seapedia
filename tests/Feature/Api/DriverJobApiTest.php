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

class DriverJobApiTest extends TestCase
{
    use RefreshDatabase;

    private function userWithToken(RoleName $role): array
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::query()->firstOrCreate(['name' => $role->value])->id,
        );

        $token = app(RoleService::class)->issueApiToken($user, $role)->plainTextToken;

        return [$user, $token];
    }

    /**
     * Sanctum's `RequestGuard` caches the resolved user for the lifetime of
     * the guard instance, so a second bearer-token request within the same
     * test method would otherwise silently keep resolving to whichever
     * actor authenticated first. Forgetting the guard forces a fresh
     * resolution from the new token on every call — required here because
     * each test impersonates more than one actor (seller then driver(s)).
     */
    private function asToken(string $token): static
    {
        app('auth')->forgetGuards();

        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    private function availableJob(Store $store): Delivery
    {
        $buyer = User::factory()->create();
        $buyer->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);
        $order = app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);

        $sellerToken = app(RoleService::class)->issueApiToken($store->user, RoleName::Seller)->plainTextToken;

        $this->asToken($sellerToken)->postJson(route('api.v1.seller.orders.process', $order));

        return Delivery::query()->where('order_id', $order->id)->firstOrFail();
    }

    public function test_api_lists_only_available_jobs(): void
    {
        [, $token] = $this->userWithToken(RoleName::Driver);
        $store = Store::factory()->create();
        $this->availableJob($store);

        $response = $this->asToken($token)->getJson(route('api.v1.driver.jobs.index'));

        $response->assertOk();
        $this->assertSame(1, $response->json('total'));
    }

    public function test_api_take_advances_the_order_to_sedang_dikirim(): void
    {
        [$driver, $token] = $this->userWithToken(RoleName::Driver);
        $store = Store::factory()->create();
        $delivery = $this->availableJob($store);

        $response = $this->asToken($token)->postJson(route('api.v1.driver.jobs.take', $delivery));

        $response->assertOk();
        $response->assertJsonPath('status', 'taken');
        $response->assertJsonPath('order.status', 'sedang_dikirim');
        $this->assertSame($driver->id, $delivery->refresh()->driver_id);
    }

    public function test_api_a_second_driver_taking_the_same_job_gets_409(): void
    {
        [, $tokenA] = $this->userWithToken(RoleName::Driver);
        [, $tokenB] = $this->userWithToken(RoleName::Driver);
        $store = Store::factory()->create();
        $delivery = $this->availableJob($store);

        $this->asToken($tokenA)->postJson(route('api.v1.driver.jobs.take', $delivery));

        $response = $this->asToken($tokenB)->postJson(route('api.v1.driver.jobs.take', $delivery));

        $response->assertStatus(409);
    }

    public function test_api_a_driver_beyond_the_active_job_cap_is_refused_with_422(): void
    {
        [, $token] = $this->userWithToken(RoleName::Driver);
        $store = Store::factory()->create();

        // A new driver's cap is one — the first take succeeds... (build the job
        // before switching the auth header — availableJob acts as the seller.)
        $first = $this->availableJob($store);
        $this->asToken($token)
            ->postJson(route('api.v1.driver.jobs.take', $first))
            ->assertSuccessful();

        // ...the one beyond it is refused.
        $extra = $this->availableJob($store);
        $response = $this->asToken($token)
            ->postJson(route('api.v1.driver.jobs.take', $extra));

        $response->assertStatus(422);
    }

    public function test_api_complete_pays_driver_and_seller_and_advances_order(): void
    {
        [$driver, $token] = $this->userWithToken(RoleName::Driver);
        $store = Store::factory()->create();
        $delivery = $this->availableJob($store);

        $this->asToken($token)->postJson(route('api.v1.driver.jobs.take', $delivery));

        $response = $this->asToken($token)->postJson(route('api.v1.driver.jobs.complete', $delivery));

        $response->assertOk();
        $response->assertJsonPath('status', 'completed');
        $response->assertJsonPath('order.status', 'pesanan_selesai');

        $order = Order::query()->findOrFail($delivery->order_id);
        $this->assertGreaterThan(0, $driver->wallet->refresh()->balance);
        $this->assertSame($order->seller_income_amount, $store->user->wallet->refresh()->balance);
    }

    public function test_api_completing_someone_elses_job_is_403(): void
    {
        [, $tokenA] = $this->userWithToken(RoleName::Driver);
        [, $tokenB] = $this->userWithToken(RoleName::Driver);
        $store = Store::factory()->create();
        $delivery = $this->availableJob($store);

        $this->asToken($tokenA)->postJson(route('api.v1.driver.jobs.take', $delivery));

        $response = $this->asToken($tokenB)->postJson(route('api.v1.driver.jobs.complete', $delivery));

        $response->assertStatus(403);
    }
}
