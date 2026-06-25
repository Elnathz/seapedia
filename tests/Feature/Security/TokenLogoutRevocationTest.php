<?php

namespace Tests\Feature\Security;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * The §5.0 token lifecycle for non-SPA `/api/v1` consumers: login mints a
 * token, logout deletes it, and the deleted token is rejected immediately
 * on the next call (§L7B "logout invalidates token").
 */
class TokenLogoutRevocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_single_role_user_gets_a_role_scoped_token_on_login(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $user->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id);

        $response = $this->postJson(route('api.v1.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonPath('active_role', RoleName::Buyer->value);

        $token = $response->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.buyer.wallet.show'))
            ->assertOk();
    }

    public function test_a_multi_role_user_must_select_a_role_before_using_a_role_gated_route(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $user->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id);
        $user->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Seller->value])->id);

        $login = $this->postJson(route('api.v1.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $login->assertOk();
        $this->assertNull($login->json('active_role'));
        $pendingToken = $login->json('token');

        $this->withHeader('Authorization', "Bearer {$pendingToken}")
            ->getJson(route('api.v1.buyer.wallet.show'))
            ->assertForbidden();

        $select = $this->withHeader('Authorization', "Bearer {$pendingToken}")
            ->postJson(route('api.v1.role.select'), ['role' => RoleName::Buyer->value]);

        $select->assertOk();
        $roleToken = $select->json('token');

        // Laravel's RequestGuard caches the resolved user for the lifetime
        // of the (shared) test application; force it to re-authenticate
        // against the new bearer token instead of returning the cached one.
        Auth::forgetGuards();

        $this->withHeader('Authorization', "Bearer {$roleToken}")
            ->getJson(route('api.v1.buyer.wallet.show'))
            ->assertOk();

        // The rotated pending token is revoked — it cannot be reused.
        Auth::forgetGuards();

        $this->withHeader('Authorization', "Bearer {$pendingToken}")
            ->getJson(route('api.v1.me'))
            ->assertUnauthorized();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create(['password' => 'password']);

        $this->postJson(route('api.v1.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_logout_revokes_the_token_so_the_next_call_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $user->roles()->attach(Role::query()->firstOrCreate(['name' => RoleName::Buyer->value])->id);

        $token = $this->postJson(route('api.v1.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.me'))
            ->assertOk();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('api.v1.logout'))
            ->assertOk();

        // Force re-authentication against the DB instead of the guard's
        // cached (pre-logout) user — see comment in the test above.
        Auth::forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson(route('api.v1.me'))
            ->assertUnauthorized();
    }
}
