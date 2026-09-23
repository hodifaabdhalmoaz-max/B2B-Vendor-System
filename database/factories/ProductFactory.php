<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        $regularPrice = $this->faker->randomFloat(2, 10, 1000);

        return [
            'name' => $this->faker->words(3, true),
            'slug' => $this->faker->unique()->slug,
            'short_description' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'SKU' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'regular_price' => $regularPrice,
            'sale_price' => null,
            'stock_status' => $this->faker->randomElement(['instock', 'outofstock']),
            'featured' => $this->faker->boolean(20),
            'quantity' => $this->faker->numberBetween(0, 100),
            'image' => 'product.jpg',
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'views' => $this->faker->numberBetween(0, 1000),
        ];
    }
}
