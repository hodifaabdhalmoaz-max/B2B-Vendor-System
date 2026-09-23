<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
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
        $quantity = $this->faker->numberBetween(1, 5);
        $price = $this->faker->randomFloat(2, 10, 200);
        
        return [
            'product_id' => Product::factory(),
            'order_id' => Order::factory(),
            'price' => $price,
            'quantity' => $quantity,
            'options' => $this->faker->optional(0.3)->passthrough([
                'color' => $this->faker->randomElement(['أحمر', 'أزرق', 'أخضر', 'أصفر']),
                'size' => $this->faker->randomElement(['صغير', 'متوسط', 'كبير']),
            ]),
            'rstatus' => $this->faker->boolean(10), // 10% chance of being returned
        ];
    }

    /**
     * Indicate that the order item is returned.
     */
    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'rstatus' => true,
        ]);
    }

    /**
     * Indicate that the order item is not returned.
     */
    public function notReturned(): static
    {
        return $this->state(fn (array $attributes) => [
            'rstatus' => false,
        ]);
    }

    /**
     * Set specific product for the order item.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Set specific order for the order item.
     */
    public function forOrder(Order $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order_id' => $order->id,
        ]);
    }
}
