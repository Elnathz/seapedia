<?php

namespace Tests\Feature\Locale;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_locale_is_indonesian_for_guests(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame('id', app()->getLocale());
    }

    public function test_guest_locale_cookie_is_applied(): void
    {
        $response = $this->withUnencryptedCookie('locale', 'en')->get('/');

        $response->assertOk();
        $this->assertSame('en', app()->getLocale());
    }

    public function test_authenticated_users_locale_column_is_applied(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $this->assertSame('en', app()->getLocale());
    }

    public function test_invalid_locale_value_falls_back_to_default(): void
    {
        $response = $this->withUnencryptedCookie('locale', 'fr')->get('/');

        $response->assertOk();
        $this->assertSame('id', app()->getLocale());
    }
}
