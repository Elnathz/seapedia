<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Banner> */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'placement' => 'main',
            'image_path' => 'banners/placeholder.webp',
            'title' => $this->faker->sentence(3),
            'subtitle' => null,
            'badge_label' => null,
            'cta_label' => null,
            'cta_url' => '/catalog',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function side(): static
    {
        return $this->state(fn () => ['placement' => 'side']);
    }
}
