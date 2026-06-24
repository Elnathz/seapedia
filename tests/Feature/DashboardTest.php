<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
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
        $response->assertInertia(fn ($page) => $page->where('activeOrders', 2));
    }
}
