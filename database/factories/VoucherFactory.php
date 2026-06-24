<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(fake()->unique()->bothify('VOUCHER##??')),
            'type' => DiscountType::Percentage,
            'value' => 10,
            'max_discount' => 50_000,
            'min_spend' => null,
            'expiry_date' => now()->addDays(30),
            'usage_limit' => 100,
            'used_count' => 0,
            'is_active' => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(['expiry_date' => now()->subDay()]);
    }

    public function usedUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_count' => $attributes['usage_limit'] ?? 100,
        ]);
    }
}
