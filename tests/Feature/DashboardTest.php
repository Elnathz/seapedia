<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_seller_dashboard_counts_only_active_products(): void
    {
        $seller = User::factory()->create();
        $seller->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id,
        );
        $store = Store::factory()->create(['user_id' => $seller->id]);
        Product::factory()->count(2)->create(['store_id' => $store->id, 'is_active' => true]);
        Product::factory()->create(['store_id' => $store->id, 'is_active' => false]);

        $response = $this->actingAs($seller)
            ->withSession(['active_role' => RoleName::Seller->value])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('activeProducts', 2));
    }

    public function test_seller_dashboard_reports_onboarding_progress(): void
    {
        $seller = User::factory()->create();
        $seller->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id,
        );

        // No store yet → nothing is done.
        $this->actingAs($seller)
            ->withSession(['active_role' => RoleName::Seller->value])
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('onboarding.has_store', false)
                ->where('onboarding.has_product', false)
            );

        // A fully set-up store with a product → every milestone is done.
        $store = Store::factory()->create([
            'user_id' => $seller->id,
            'full_address' => 'Jl. Contoh No. 1',
            'origin_latitude' => -6.2,
            'origin_longitude' => 106.8,
            'logo_path' => 'stores/logo.png',
        ]);
        Product::factory()->create(['store_id' => $store->id]);

        // Re-resolve the user so the store relation isn't the null cached on
        // the instance from the first request.
        $this->actingAs($seller->fresh())
            ->withSession(['active_role' => RoleName::Seller->value])
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('onboarding.has_store', true)
                ->where('onboarding.has_address', true)
                ->where('onboarding.has_logo', true)
                ->where('onboarding.has_product', true)
            );
    }

    public function test_seller_dashboard_summarises_the_order_pipeline(): void
    {
        $seller = User::factory()->create();
        $seller->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id,
        );
        $store = Store::factory()->create(['user_id' => $seller->id]);
        $buyer = User::factory()->create();

        Order::factory()->count(2)->create(['store_id' => $store->id, 'buyer_id' => $buyer->id, 'status' => 'sedang_dikemas']);
        Order::factory()->create(['store_id' => $store->id, 'buyer_id' => $buyer->id, 'status' => 'menunggu_pengirim']);
        Order::factory()->create(['store_id' => $store->id, 'buyer_id' => $buyer->id, 'status' => 'pesanan_selesai', 'seller_income_amount' => 75_000]);

        $this->actingAs($seller)
            ->withSession(['active_role' => RoleName::Seller->value])
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('stats.awaiting_process', 2)
                ->where('stats.awaiting_pickup', 1)
                ->where('stats.in_delivery', 0)
                ->where('stats.completed', 1)
                ->where('stats.revenue', 75_000)
            );
    }

    public function test_buyer_dashboard_excludes_finished_orders_from_active_count(): void
    {
        $buyer = User::factory()->create();
        $buyer->roles()->attach(
            Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id,
        );
        $store = Store::factory()->create();
        Order::factory()->create(['buyer_id' => $buyer->id, 'store_id' => $store->id, 'status' => 'sedang_dikemas']);
        Order::factory()->create(['buyer_id' => $buyer->id, 'store_id' => $store->id, 'status' => 'sedang_dikirim']);
        Order::factory()->create(['buyer_id' => $buyer->id, 'store_id' => $store->id, 'status' => 'pesanan_selesai']);

        $response = $this->actingAs($buyer)
            ->withSession(['active_role' => RoleName::Buyer->value])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->where('activeOrdersCount', 2));
    }

    public function test_admin_dashboard_shows_a_live_resource_count_snapshot(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $seller = User::factory()->create();
        $seller->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id);
        $buyer = User::factory()->create();
        $store = Store::factory()->create(['user_id' => $seller->id]);
        Product::factory()->count(2)->create(['store_id' => $store->id]);
        Order::factory()->create(['buyer_id' => $buyer->id, 'store_id' => $store->id, 'status' => 'sedang_dikemas']);
        Order::factory()->create(['buyer_id' => $buyer->id, 'store_id' => $store->id, 'status' => 'sedang_dikemas']);
        Voucher::factory()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('snapshot.users.total', 3)
            ->where('snapshot.users.admins', 1)
            ->where('snapshot.users.sellers', 1)
            ->where('snapshot.stores_count', 1)
            ->where('snapshot.products_count', 2)
            ->where('snapshot.orders_by_status.sedang_dikemas', 2)
            ->where('snapshot.vouchers.total', 1)
        );
    }
}
