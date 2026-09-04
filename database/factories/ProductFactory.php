<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $price = fake()->numberBetween(10, 50) * 1000; // e.g. 10.000 - 50.000

        return [
            'category_id' => Category::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price' => $price,
            'discount_price' => fake()->boolean(40) ? (int) ($price * 0.8) : null,
            'weight_grams' => fake()->randomElement([100, 150, 200, 250, 500]),
            'spiciness_level' => fake()->numberBetween(0, 5),
            'stock' => fake()->numberBetween(10, 100),
            'image_url' => null,
            'is_featured' => fake()->boolean(25),
            'is_available' => true,
        ];
    }
}
