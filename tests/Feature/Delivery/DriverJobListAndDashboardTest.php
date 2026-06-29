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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverJobListAndDashboardTest extends TestCase
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

    private function availableJob(Store $store): Delivery
    {
        $buyer = $this->userWithRole(RoleName::Buyer);
        $buyer->wallet->update(['balance' => 1_000_000]);
        $product = Product::factory()->create(['store_id' => $store->id, 'price' => 50_000, 'stock' => 10]);
        $address = Address::factory()->create(['user_id' => $buyer->id, 'is_default' => true]);

        app(CartService::class)->addItem($buyer, $product, null, 1);
        $order = app(CheckoutService::class)->commit($buyer, $address, DeliveryMethod::Regular);

        $seller = $store->user;
        $this->actingAsRole($seller, RoleName::Seller)
            ->post(route('seller.orders.process', $order));

        return Delivery::query()->where('order_id', $order->id)->firstOrFail();
    }

    public function test_the_jobs_index_lists_only_available_jobs(): void
    {
        $store = Store::factory()->create();
        $available = $this->availableJob($store);
        $taken = $this->availableJob($store);
        $driver = $this->userWithRole(RoleName::Driver);
        app(DeliveryService::class)->take($taken, $driver);

        $response = $this->actingAsRole($driver, RoleName::Driver)
            ->get(route('driver.jobs.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('jobs.total', 1)
            ->where('jobs.data.0.id', $available->id)
        );
    }

    public function test_the_dashboard_shows_the_drivers_own_active_job_history_and_earnings(): void
    {
        $store = Store::factory()->create();
        $driver = $this->userWithRole(RoleName::Driver);

        $completedJob = $this->availableJob($store);
        app(DeliveryService::class)->take($completedJob, $driver);
        app(DeliveryService::class)->complete($completedJob->refresh(), $driver);

        $activeJob = $this->availableJob($store);
        app(DeliveryService::class)->take($activeJob, $driver);

        $response = $this->actingAsRole($driver, RoleName::Driver)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('dashboard/Driver')
            ->where('activeJob.id', $activeJob->id)
            ->where('history.data.0.id', $completedJob->id)
            ->where('totalEarnings', $completedJob->refresh()->earning_amount)
        );
    }

    public function test_a_non_driver_active_role_cannot_view_driver_routes(): void
    {
        $store = Store::factory()->create();
        $this->availableJob($store);
        $buyer = $this->userWithRole(RoleName::Buyer);

        $response = $this->actingAsRole($buyer, RoleName::Buyer)
            ->get(route('driver.jobs.index'));

        $response->assertForbidden();
    }
}
