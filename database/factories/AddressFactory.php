<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // A jittered point around Semarang so every factory address carries the
        // now-mandatory coordinates the distance-based delivery fee needs.
        return [
            'user_id' => User::factory(),
            'recipient_name' => $this->faker->name(),
            'phone' => '08'.$this->faker->numerify('##########'),
            'full_address' => $this->faker->address(),
            'province' => 'Jawa Tengah',
            'city' => 'Kota Semarang',
            'district' => 'Tembalang',
            'village' => 'Sumurboto',
            'postal_code' => '50269',
            'latitude' => -7.05 + $this->faker->randomFloat(4, -0.05, 0.05),
            'longitude' => 110.42 + $this->faker->randomFloat(4, -0.05, 0.05),
            'is_default' => false,
        ];
    }
}
