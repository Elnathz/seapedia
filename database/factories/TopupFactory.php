<?php

namespace Database\Factories;

use App\Enums\PaymentGatewayType;
use App\Enums\TopupStatus;
use App\Models\Topup;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Topup>
 */
class TopupFactory extends Factory
{
    protected $model = Topup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'amount' => $this->faker->numberBetween(10_000, 200_000),
            'status' => TopupStatus::Pending,
            'gateway' => PaymentGatewayType::Fake,
            'gateway_reference' => (string) Str::uuid(),
        ];
    }
}
