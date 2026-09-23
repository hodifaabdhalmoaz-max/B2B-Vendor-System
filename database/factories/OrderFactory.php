<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 400);
        $discount = $this->faker->randomFloat(2, 0, 50);
        $tax = $subtotal * 0.1; // 10% tax
        $total = $subtotal - $discount + $tax;

        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('77#######'),
            'locality' => $this->faker->streetName(),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->randomElement(['صنعاء', 'عدن', 'تعز', 'الحديدة', 'إب']),
            'state' => $this->faker->randomElement(['أمانة العاصمة', 'عدن', 'تعز', 'الحديدة', 'إب']),
            'country' => 'اليمن',
            'landmark' => $this->faker->optional()->streetName(),
            'zip' => $this->faker->numerify('#####'),
            'type' => $this->faker->randomElement(['home', 'office']),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'status' => $this->faker->randomElement(['ordered', 'delivered', 'canceled']),
            'is_shipping_different' => $this->faker->boolean(20), // 20% chance
            'delivered_date' => null,
            'canceled_date' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ordered',
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'canceled',
            'canceled_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }
}
