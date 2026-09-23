<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Size>
 */
class SizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = $this->faker->unique()->randomElement(['XS', 'S', 'M', 'L', 'XL']).$this->faker->unique()->numberBetween(10, 99);

        return [
            'name' => 'Size '.$code,
            'code' => $code,
            'description' => $this->faker->optional()->sentence,
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 50),
        ];
    }
}
