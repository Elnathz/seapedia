<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->numberBetween(10_000, 100_000);
        $quantity = $this->faker->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name_snapshot' => $this->faker->words(3, true),
            'price_snapshot' => $price,
            'quantity' => $quantity,
            'line_subtotal' => $price * $quantity,
        ];
    }
}
