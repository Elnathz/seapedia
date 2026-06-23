<?php

namespace Tests\Feature\Locale;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_locale_update_persists_to_users_locale(): void
    {
        $user = User::factory()->create(['locale' => 'id']);

        $response = $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en']);

        $response->assertRedirect();
        $response->assertCookie('locale', 'en', false);
        $this->assertSame('en', $user->refresh()->locale);
    }

    public function test_guest_locale_update_sets_cookie_only(): void
    {
        $response = $this->post(route('locale.update'), ['locale' => 'en']);

        $response->assertRedirect();
        $response->assertCookie('locale', 'en', false);
    }

    public function test_invalid_locale_value_is_rejected(): void
    {
        $response = $this->post(route('locale.update'), ['locale' => 'fr']);

        $response->assertSessionHasErrors('locale');
    }
}
