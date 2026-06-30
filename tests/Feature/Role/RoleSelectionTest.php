<?php

namespace Tests\Feature\Role;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_multi_role_login_redirects_to_role_selection_not_a_dashboard(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach([
            Role::factory()->create(['name' => RoleName::Buyer->value])->id,
            Role::factory()->create(['name' => RoleName::Seller->value])->id,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('role.select'));
        $this->assertNull(session('active_role'));
    }

    public function test_single_role_login_auto_selects_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create();
        $user->roles()->attach(
            Role::factory()->create(['name' => RoleName::Buyer->value])->id,
        );

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('catalog.index', absolute: false));
        $this->assertSame(RoleName::Buyer->value, session('active_role'));
    }
}
