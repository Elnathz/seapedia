<?php

namespace Tests\Feature\Security;

use App\Models\AppReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * §L7A: classic SQLi probes must have no effect anywhere user input reaches
 * a query — Eloquent parameter-binds every value (incl. `LIKE` search and
 * `where()` lookups), so these payloads are only ever treated as literal
 * strings, never as SQL.
 */
class SecuritySqliTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOAD = "' OR 1=1 --";

    public function test_sqli_payload_in_login_does_not_bypass_authentication(): void
    {
        User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => self::PAYLOAD,
            'password' => self::PAYLOAD,
        ]);

        $this->assertGuest();
        $response->assertStatus(302);
    }

    public function test_sqli_payload_in_catalog_search_has_no_effect(): void
    {
        $response = $this->get(route('catalog.index', ['q' => self::PAYLOAD]));

        $response->assertOk();
    }

    public function test_sqli_payload_in_a_review_is_stored_as_inert_text_and_does_not_affect_other_tables(): void
    {
        $usersBefore = User::count();

        $response = $this->post(route('reviews.store'), [
            'reviewer_name' => 'Mallory',
            'rating' => 1,
            'comment' => "'; DROP TABLE users; --",
        ]);

        $response->assertRedirect(route('home'));
        $this->assertSame($usersBefore, User::count());
        $this->assertDatabaseHas('app_reviews', [
            'reviewer_name' => 'Mallory',
            'comment' => "'; DROP TABLE users; --",
        ]);
    }

    public function test_sqli_payload_as_a_reviewer_name_is_handled_safely(): void
    {
        AppReview::factory()->create(['reviewer_name' => self::PAYLOAD]);

        $this->assertDatabaseHas('app_reviews', ['reviewer_name' => self::PAYLOAD]);
    }
}
