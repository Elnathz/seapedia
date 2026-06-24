<?php

namespace Database\Factories;

use App\Enums\WalletDirection;
use App\Enums\WalletTransactionType;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WalletTransaction>
 */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(10_000, 200_000);

        return [
            'wallet_id' => Wallet::factory(),
            'type' => WalletTransactionType::Topup,
            'direction' => WalletDirection::Credit,
            'amount' => $amount,
            'balance_after' => $amount,
            'reference_type' => null,
            'reference_id' => null,
            'description' => null,
        ];
    }
}
