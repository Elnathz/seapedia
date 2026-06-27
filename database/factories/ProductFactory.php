<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'store_id' => Store::factory(),
            'category_id' => Category::query()->whereNotNull('parent_id')->inRandomOrder()->value('id')
                ?? Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->sentence(15),
            'price' => $this->faker->numberBetween(2000, 150_000),
            'stock' => $this->faker->numberBetween(0, 100),
            'image_path' => null,
            'is_active' => true,
        ];
    }
}
