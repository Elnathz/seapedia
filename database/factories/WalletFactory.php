<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => 0,
        ];
    }

    /**
     * `UserObserver` already creates a wallet for every new user, so a
     * plain insert here would collide with `wallets.user_id`'s unique
     * constraint. Update that existing row instead of inserting a second one.
     */
    public function create($attributes = [], ?Model $parent = null)
    {
        if (! empty($attributes)) {
            return $this->state($attributes)->create([], $parent);
        }

        $wallet = $this->make([], $parent);

        return Wallet::query()->updateOrCreate(
            ['user_id' => $wallet->user_id],
            ['balance' => $wallet->balance],
        );
    }
}
