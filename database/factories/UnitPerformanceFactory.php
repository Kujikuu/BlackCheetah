<?php

namespace Database\Factories;

use App\Models\Franchise;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnitPerformance>
 */
class UnitPerformanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $revenue = $this->faker->numberBetween(8000, 25000);
        $expenses = $this->faker->numberBetween(5000, 15000);
        $royalties = $revenue * 0.10; // 10% royalty rate
        $profit = $revenue - $expenses - $royalties;

        return [
            'franchise_id' => Franchise::factory(),
            'unit_id' => Unit::factory(),
            'period_type' => $this->faker->randomElement(['daily', 'monthly', 'yearly']),
            'period_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'revenue' => $revenue,
            'expenses' => $expenses,
            'royalties' => $royalties,
            'profit' => max($profit, 0),
            'total_transactions' => $this->faker->numberBetween(50, 300),
            'average_transaction_value' => $revenue / $this->faker->numberBetween(50, 300),
            'customer_reviews_count' => $this->faker->numberBetween(10, 100),
            'customer_rating' => $this->faker->randomFloat(1, 3.0, 5.0),
            'employee_count' => $this->faker->numberBetween(5, 25),
            'customer_satisfaction_score' => $this->faker->randomFloat(1, 70.0, 95.0),
            'growth_rate' => $this->faker->randomFloat(1, -5.0, 25.0),
            'additional_metrics' => null,
        ];
    }

    /**
     * Create monthly performance data for a specific unit
     */
    public function monthly(Unit $unit, int $monthsBack = 12): self
    {
        return $this->state(fn (array $attributes) => [
            'franchise_id' => $unit->franchise_id,
            'unit_id' => $unit->id,
            'period_type' => 'monthly',
            'period_date' => now()->subMonths($this->faker->numberBetween(0, $monthsBack))->startOfMonth(),
        ]);
    }

    /**
     * Create daily performance data for a specific unit
     */
    public function daily(Unit $unit, int $daysBack = 30): self
    {
        return $this->state(fn (array $attributes) => [
            'franchise_id' => $unit->franchise_id,
            'unit_id' => $unit->id,
            'period_type' => 'daily',
            'period_date' => now()->subDays($this->faker->numberBetween(0, $daysBack)),
        ]);
    }

    /**
     * Create yearly performance data for a specific unit
     */
    public function yearly(Unit $unit, int $yearsBack = 5): self
    {
        return $this->state(fn (array $attributes) => [
            'franchise_id' => $unit->franchise_id,
            'unit_id' => $unit->id,
            'period_type' => 'yearly',
            'period_date' => now()->subYears($this->faker->numberBetween(0, $yearsBack))->startOfYear(),
        ]);
    }

    /**
     * Create high-performing unit data
     */
    public function highPerforming(): self
    {
        $revenue = $this->faker->numberBetween(20000, 35000);
        $expenses = $revenue * $this->faker->randomFloat(2, 0.35, 0.45);
        $royalties = $revenue * 0.10;
        $profit = $revenue - $expenses - $royalties;

        return $this->state(fn (array $attributes) => [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'royalties' => $royalties,
            'profit' => $profit,
            'customer_rating' => $this->faker->randomFloat(1, 4.2, 5.0),
            'customer_satisfaction_score' => $this->faker->randomFloat(1, 85.0, 98.0),
            'growth_rate' => $this->faker->randomFloat(1, 10.0, 30.0),
        ]);
    }

    /**
     * Create low-performing unit data
     */
    public function lowPerforming(): self
    {
        $revenue = $this->faker->numberBetween(5000, 12000);
        $expenses = $revenue * $this->faker->randomFloat(2, 0.55, 0.75);
        $royalties = $revenue * 0.10;
        $profit = $revenue - $expenses - $royalties;

        return $this->state(fn (array $attributes) => [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'royalties' => $royalties,
            'profit' => max($profit, 0),
            'customer_rating' => $this->faker->randomFloat(1, 2.5, 3.8),
            'customer_satisfaction_score' => $this->faker->randomFloat(1, 60.0, 75.0),
            'growth_rate' => $this->faker->randomFloat(1, -15.0, 2.0),
        ]);
    }

    /**
     * Create aggregated data (no specific unit)
     */
    public function aggregated(Franchise $franchise): self
    {
        return $this->state(fn (array $attributes) => [
            'franchise_id' => $franchise->id,
            'unit_id' => null,
        ]);
    }
}
