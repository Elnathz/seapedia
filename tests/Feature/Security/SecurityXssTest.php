<?php

namespace Tests\Feature\Security;

use App\Models\AppReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * §L7A: a `<script>` payload in public, user-generated content (app
 * reviews) must never execute. We never sanitize/strip on the way in (that
 * would silently mutate user input); instead we rely on output encoding —
 * no `v-html` in any Vue template (grepped in `security-pass`) and the
 * page payload only ever reaching the browser inside a `<script
 * type="application/json">` tag, JSON-encoded with escaped slashes (so the
 * literal bytes `</script>` never appear and can't close that tag early).
 */
class SecurityXssTest extends TestCase
{
    use RefreshDatabase;

    private const PAYLOAD = '<script>alert(1)</script>';

    public function test_a_script_tag_in_a_review_comment_is_stored_raw_and_never_rendered_as_a_live_tag(): void
    {
        $this->post(route('reviews.store'), [
            'reviewer_name' => 'Eve',
            'rating' => 5,
            'comment' => self::PAYLOAD,
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('app_reviews', [
            'reviewer_name' => 'Eve',
            'comment' => self::PAYLOAD,
        ]);

        $response = $this->get(route('reviews.index'));

        $response->assertOk();
        // The literal, executable tag never appears as a contiguous
        // substring — the page payload's JSON-encoded form escapes the
        // closing slash (`<\/script>`), which is what keeps it inert.
        $this->assertStringNotContainsString(self::PAYLOAD, $response->getContent());
        $this->assertStringContainsString('<script>alert(1)<\/script>', $response->getContent());
    }

    public function test_a_script_tag_in_a_reviewer_name_is_also_inert(): void
    {
        AppReview::factory()->create(['reviewer_name' => self::PAYLOAD, 'comment' => 'fine']);

        $response = $this->get(route('reviews.index'));

        $response->assertOk();
        $this->assertStringNotContainsString(self::PAYLOAD, $response->getContent());
    }
}
