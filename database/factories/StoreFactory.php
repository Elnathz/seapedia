<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->sentence(15),
            'is_active' => true,
        ];
    }

    /**
     * Set the store's origin region text (shown in the store profile).
     */
    public function origin(string $province, ?string $city = null, ?string $district = null, ?string $village = null): static
    {
        return $this->state(fn () => [
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'village' => $village,
        ]);
    }

    /**
     * Set the store's origin coordinates (drives the distance delivery fee).
     */
    public function originAt(float $latitude, float $longitude): static
    {
        return $this->state(fn () => [
            'origin_latitude' => $latitude,
            'origin_longitude' => $longitude,
        ]);
    }
}
