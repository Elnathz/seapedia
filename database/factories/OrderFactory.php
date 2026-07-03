<?php

namespace Database\Factories;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->numberBetween(20_000, 300_000);
        $deliveryMethod = DeliveryMethod::Regular;
        $taxAmount = (int) round($subtotal * 0.12);
        $deliveryFee = $deliveryMethod->fee();
        $now = now();

        return [
            'code' => 'INV-'.$now->format('Ymd').'-'.Str::upper(Str::random(6)),
            'buyer_id' => User::factory(),
            'store_id' => Store::factory(),
            'ship_recipient' => $this->faker->name(),
            'ship_phone' => '08'.$this->faker->numerify('##########'),
            'ship_address' => $this->faker->address(),
            'ship_latitude' => $this->faker->latitude(-8, -6),
            'ship_longitude' => $this->faker->longitude(106, 112),
            'delivery_method' => $deliveryMethod,
            'subtotal' => $subtotal,
            'discount_total' => 0,
            'delivery_fee' => $deliveryFee,
            'delivery_distance_km' => $this->faker->randomFloat(2, 1, 45),
            'tax_amount' => $taxAmount,
            'grand_total' => $subtotal + $taxAmount + $deliveryFee,
            'seller_income_amount' => $subtotal,
            'status' => OrderStatus::SedangDikemas,
            'created_sim_at' => $now,
            'sla_due_at' => $now->copy()->addDays($deliveryMethod->slaTicks()),
            'paid_at' => $now,
        ];
    }
}
