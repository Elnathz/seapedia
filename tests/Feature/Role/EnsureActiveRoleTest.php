<?php

namespace Tests\Feature\Role;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureActiveRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_role_mismatch_is_rejected_with_403_on_web(): void
    {
        Route::middleware(['web', 'auth', 'active_role:seller'])
            ->get('/__test/web/seller-only', fn () => 'ok');

        $user = User::factory()->create();
        $user->roles()->attach(
            Role::factory()->create(['name' => RoleName::Buyer->value])->id,
        );

        $response = $this->actingAs($user)
            ->withSession(['active_role' => RoleName::Buyer->value])
            ->get('/__test/web/seller-only');

        $response->assertForbidden();
    }

    public function test_buyer_scoped_token_is_blocked_from_seller_only_api_route(): void
    {
        Route::middleware(['auth:sanctum', 'active_role:seller'])
            ->get('/__test/api/seller-only', fn () => 'ok');

        $user = User::factory()->create();
        $user->roles()->attach(
            Role::factory()->create(['name' => RoleName::Buyer->value])->id,
        );

        $token = app(RoleService::class)
            ->issueApiToken($user, RoleName::Buyer)
            ->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->get('/__test/api/seller-only');

        $response->assertForbidden();
    }
}
