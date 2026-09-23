<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Color>
 */
class ColorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->colorName;

        return [
            'name' => $name,
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'hex_code' => $this->faker->hexColor,
            'description' => $this->faker->optional()->sentence,
            'is_active' => true,
            'order' => $this->faker->numberBetween(0, 50),
        ];
    }
}
