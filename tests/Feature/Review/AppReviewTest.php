<?php

namespace Tests\Feature\Review;

use App\Models\AppReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_list_is_publicly_visible()
    {
        AppReview::factory()->count(3)->create();

        $response = $this->get(route('reviews.index'));

        $response->assertOk();
    }

    public function test_guest_can_submit_a_valid_review()
    {
        $response = $this->post(route('reviews.store'), [
            'reviewer_name' => 'Jane Doe',
            'rating' => 5,
            'comment' => 'Great marketplace experience.',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('app_reviews', [
            'reviewer_name' => 'Jane Doe',
            'rating' => 5,
            'user_id' => null,
        ]);
    }

    public function test_invalid_rating_is_rejected()
    {
        $response = $this->post(route('reviews.store'), [
            'reviewer_name' => 'Jane Doe',
            'rating' => 6,
            'comment' => 'Great marketplace experience.',
        ]);

        $response->assertSessionHasErrors('rating');
        $this->assertDatabaseMissing('app_reviews', ['reviewer_name' => 'Jane Doe']);
    }
}
