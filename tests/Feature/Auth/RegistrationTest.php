<?php

namespace Tests\Feature\Auth;

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        Role::factory()->create(['name' => RoleName::Buyer->value]);

        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [RoleName::Buyer->value],
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::whereEmail('test@example.com')->first();
        $this->assertSame('testuser', $user->username);
        $this->assertTrue($user->hasRole(RoleName::Buyer));
        $this->assertNotNull($user->wallet);
    }

    public function test_duplicate_username_is_rejected()
    {
        Role::factory()->create(['name' => RoleName::Buyer->value]);
        User::factory()->create(['username' => 'testuser']);

        $response = $this->post(route('register.store'), [
            'name' => 'Another User',
            'username' => 'testuser',
            'email' => 'another@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => [RoleName::Buyer->value],
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
