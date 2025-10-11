<?php

namespace Database\Factories;

use App\Models\Franchise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'unit_name' => $this->faker->company(),
            'unit_code' => strtoupper($this->faker->unique()->lexify('???-??????')),
            'franchise_id' => Franchise::factory(),
            'unit_type' => $this->faker->randomElement(['store', 'kiosk', 'mobile', 'online', 'warehouse', 'office']),
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state_province' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->countryCode(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'size_sqft' => $this->faker->numberBetween(500, 5000),
            'monthly_rent' => $this->faker->numberBetween(1000, 10000),
            'opening_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'status' => $this->faker->randomElement(['planning', 'construction', 'training', 'active', 'temporarily_closed', 'permanently_closed']),
        ];
    }
}
