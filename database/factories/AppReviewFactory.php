<?php

namespace Database\Factories;

use App\Models\AppReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppReview>
 */
class AppReviewFactory extends Factory
{
    protected $model = AppReview::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'reviewer_name' => fake()->name(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence(12),
        ];
    }
}
