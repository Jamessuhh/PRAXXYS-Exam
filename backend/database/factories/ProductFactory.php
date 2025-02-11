<?php

namespace Database\Factories;

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
        return [
            'name' => fake()->word(),
            'category' => fake()->randomElement(['Electronics', 'Clothing', 'Food', 'Books', 'Sports']),
            'description' => fake()->paragraph(),
            'date_time' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
