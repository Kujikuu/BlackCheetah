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
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['Electronics', 'Clothing', 'Food', 'Books', 'Home & Garden']),
            'unit_price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement(['active', 'inactive', 'discontinued']),
            'sku' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'cost_price' => $this->faker->randomFloat(2, 5, 500),
            'weight' => $this->faker->randomFloat(2, 0.1, 50),
            'dimensions' => $this->faker->randomElement(['10x10x10', '20x15x5', '30x20x10']),
            'minimum_stock' => $this->faker->numberBetween(1, 10),
        ];
    }
}
