<?php

namespace Database\Factories;

use App\Models\Franchise;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Royalty>
 */
class RoyaltyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grossRevenue = $this->faker->numberBetween(50000, 200000);
        $royaltyPercentage = $this->faker->randomFloat(2, 5, 10);
        $marketingFeePercentage = $this->faker->randomFloat(2, 1, 5);
        $technologyFeeAmount = 50;

        $royaltyAmount = $grossRevenue * ($royaltyPercentage / 100);
        $marketingFeeAmount = $grossRevenue * ($marketingFeePercentage / 100);
        $totalAmount = $royaltyAmount + $marketingFeeAmount + $technologyFeeAmount;

        $periodYear = $this->faker->numberBetween(2023, 2024);
        $periodMonth = $this->faker->numberBetween(1, 12);
        $periodStart = \Carbon\Carbon::createFromDate($periodYear, $periodMonth, 1);
        $periodEnd = $periodStart->copy()->endOfMonth();

        return [
            'franchise_id' => Franchise::factory(),
            'unit_id' => Unit::factory(),
            'franchisee_id' => User::factory(),
            'type' => $this->faker->randomElement(['royalty', 'marketing_fee', 'technology_fee', 'other']),
            'period_year' => $periodYear,
            'period_month' => $periodMonth,
            'period_start_date' => $periodStart,
            'period_end_date' => $periodEnd,
            'gross_revenue' => $grossRevenue,
            'royalty_percentage' => $royaltyPercentage,
            'royalty_amount' => $royaltyAmount,
            'marketing_fee_percentage' => $marketingFeePercentage,
            'marketing_fee_amount' => $marketingFeeAmount,
            'technology_fee_amount' => $technologyFeeAmount,
            'total_amount' => $totalAmount,
            'adjustments' => 0,
            'adjustment_notes' => null,
            'status' => $this->faker->randomElement(['draft', 'pending', 'paid', 'overdue', 'disputed', 'cancelled']),
            'due_date' => $periodEnd->copy()->addDays(15),
            'paid_date' => null,
            'payment_method' => null,
            'payment_reference' => null,
            'late_fee' => 0,
            'revenue_breakdown' => null,
            'attachments' => [],
            'notes' => $this->faker->optional()->sentence(),
            'is_auto_generated' => $this->faker->boolean(70),
            'generated_by' => null,
        ];
    }

    /**
     * Indicate that the royalty is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_date' => now()->subDays($this->faker->numberBetween(1, 30)),
            'payment_method' => $this->faker->randomElement(['bank_transfer', 'credit_card', 'check', 'ach', 'wire', 'mada', 'stc_pay', 'sadad', 'other']),
            'payment_reference' => 'REF-'.$this->faker->unique()->numerify('######'),
        ]);
    }

    /**
     * Indicate that the royalty is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_date' => null,
            'payment_method' => null,
            'payment_reference' => null,
        ]);
    }

    /**
     * Indicate that the royalty is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'due_date' => now()->subDays($this->faker->numberBetween(1, 30)),
            'paid_date' => null,
            'payment_method' => null,
            'payment_reference' => null,
        ]);
    }
}
