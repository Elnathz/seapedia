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
            'reviewer_name' => $this->faker->name(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(12),
        ];
    }
}
