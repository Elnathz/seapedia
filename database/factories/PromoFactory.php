<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\Promo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Promo>
 */
class PromoFactory extends Factory
{
    protected $model = Promo::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('PROMO##??')),
            'type' => DiscountType::Fixed,
            'value' => 20_000,
            'max_discount' => null,
            'min_spend' => null,
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(['expiry_date' => now()->subDay()]);
    }
}
