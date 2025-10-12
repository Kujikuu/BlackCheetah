<?php

namespace Database\Factories;

use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Revenue>
 */
class RevenueFactory extends Factory
{
    protected $model = Revenue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'revenue_number' => 'REV'.$this->faker->unique()->numerify('########'),
            'franchise_id' => Franchise::factory(),
            'unit_id' => Unit::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(['sales', 'franchise_fee', 'royalty', 'marketing_fee', 'other']),
            'category' => $this->faker->randomElement(['product_sales', 'service_sales', 'initial_fee', 'ongoing_fee', 'commission', 'other']),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'currency' => 'SAR',
            'description' => $this->faker->sentence,
            'revenue_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'period_year' => $this->faker->numberBetween(2023, 2024),
            'period_month' => $this->faker->numberBetween(1, 12),
            'period_start_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'period_end_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'source' => $this->faker->randomElement(['online', 'in_store', 'phone', 'email']),
            'customer_name' => $this->faker->name,
            'customer_email' => $this->faker->optional()->email,
            'customer_phone' => $this->faker->optional()->phoneNumber,
            'invoice_number' => $this->faker->optional()->numerify('INV-########'),
            'receipt_number' => $this->faker->optional()->numerify('REC-########'),
            'payment_method' => $this->faker->randomElement(['cash', 'credit_card', 'bank_transfer', 'check']),
            'payment_reference' => $this->faker->optional()->numerify('REF-########'),
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'tax_amount' => $this->faker->randomFloat(2, 0, 500),
            'discount_amount' => $this->faker->randomFloat(2, 0, 200),
            'net_amount' => function (array $attributes) {
                return $attributes['amount'] - $attributes['discount_amount'] + $attributes['tax_amount'];
            },
            'line_items' => $this->faker->optional()->randomElement([
                [['name' => 'Product A', 'quantity' => 2, 'price' => 100]],
                [['name' => 'Product B', 'quantity' => 1, 'price' => 250]],
                [['name' => 'Product A', 'quantity' => 2, 'price' => 100], ['name' => 'Product B', 'quantity' => 1, 'price' => 250]],
            ]),
            'metadata' => $this->faker->optional()->randomElements([
                'source' => 'website',
                'campaign' => 'summer_sale',
                'region' => 'north',
            ]),
            'status' => $this->faker->randomElement(['pending', 'verified', 'disputed']),
            'verified_by' => null, // Will be set later when needed
            'verified_at' => $this->faker->optional()->dateTime(),
            'is_recurring' => $this->faker->boolean(20), // 20% chance of being recurring
            'recurrence_type' => $this->faker->optional()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'recurrence_interval' => $this->faker->optional()->numberBetween(1, 12),
            'recurrence_end_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'parent_revenue_id' => null, // Will be set later when needed
            'attachments' => $this->faker->optional()->randomElements([
                'invoices/inv_001.pdf',
                'receipts/rec_001.jpg',
            ]),
            'notes' => $this->faker->optional()->sentence,
            'is_auto_generated' => $this->faker->boolean(10), // 10% chance of being auto-generated
            'recorded_at' => $this->faker->dateTime(),
        ];
    }

    /**
     * Indicate that the revenue is a sale.
     */
    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sales',
            'category' => 'product_sales',
        ]);
    }

    /**
     * Indicate that the revenue is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the revenue payment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'completed',
        ]);
    }

    /**
     * Create a revenue for a specific franchise.
     */
    public function forFranchise(Franchise $franchise): static
    {
        return $this->state(fn (array $attributes) => [
            'franchise_id' => $franchise->id,
        ]);
    }

    /**
     * Create a revenue for a specific unit.
     */
    public function forUnit(Unit $unit): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_id' => $unit->id,
            'franchise_id' => $unit->franchise_id,
        ]);
    }

    /**
     * Create a revenue for a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'revenue_date' => $date,
            'period_year' => date('Y', strtotime($date)),
            'period_month' => date('n', strtotime($date)),
        ]);
    }
}
