<?php

namespace Tests\Feature\Admin;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_api_returns_resource_counts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $token = $admin->createToken('admin')->plainTextToken;
        Store::factory()->count(3)->create();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.admin.dashboard'));

        $response->assertOk();
        $response->assertJsonPath('stores_count', 3);
        $response->assertJsonStructure([
            'simulated_now', 'users', 'stores_count', 'products_count',
            'orders_by_status', 'deliveries_by_status', 'overdue_eligible_count',
            'promos', 'vouchers',
        ]);
    }

    public function test_non_admin_cannot_view_the_admin_dashboard_api(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $token = $user->createToken('session')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.admin.dashboard'));

        $response->assertForbidden();
    }
}
