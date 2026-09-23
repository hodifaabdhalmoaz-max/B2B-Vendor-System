<?php

namespace Database\Factories;

use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'color_id' => Color::factory(),
            'size_id' => Size::factory(),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}-[A-Z]{2}'),
            'price_adjustment' => '0.00',
            'is_active' => true,
        ];
    }

    public function defaultVariant(): static
    {
        return $this->state(fn () => [
            'color_id' => null,
            'size_id' => null,
        ]);
    }
}
