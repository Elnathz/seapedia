<?php

namespace Database\Factories;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'driver_id' => null,
            'status' => DeliveryStatus::Available,
            'taken_at' => null,
            'completed_at' => null,
            'earning_amount' => 0,
        ];
    }

    public function taken(): static
    {
        return $this->state(fn () => [
            'driver_id' => User::factory(),
            'status' => DeliveryStatus::Taken,
            'taken_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'driver_id' => User::factory(),
            'status' => DeliveryStatus::Completed,
            'taken_at' => now(),
            'completed_at' => now(),
            'earning_amount' => 16_000,
        ]);
    }
}
