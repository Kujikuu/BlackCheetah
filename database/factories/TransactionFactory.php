<?php

namespace Database\Factories;

use App\Models\Franchise;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['expense', 'revenue', 'royalty', 'marketing_fee']);

        return [
            'transaction_number' => 'TXN'.$this->faker->unique()->numerify('########'),
            'type' => $type,
            'category' => $this->faker->randomElement([
                $type === 'expense' ? $this->faker->randomElement(['cost_of_goods', 'labor', 'rent', 'utilities', 'marketing', 'equipment', 'supplies', 'insurance', 'taxes', 'other']) : ($type === 'revenue' ? $this->faker->randomElement(['sales']) : ($type === 'royalty' ? 'other' : 'marketing')),
            ]),
            'amount' => $this->faker->randomFloat(2, 50, 5000),
            'currency' => 'SAR',
            'description' => $this->faker->sentence,
            'franchise_id' => Franchise::factory(),
            'unit_id' => Unit::factory(),
            'user_id' => User::factory(),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled', 'refunded']),
            'payment_method' => $this->faker->randomElement(['cash', 'credit_card', 'bank_transfer', 'check']),
            'reference_number' => $this->faker->optional()->numerify('REF-########'),
            'vendor_customer' => $this->faker->company,
            'metadata' => $this->faker->optional()->randomElements([
                'source' => 'manual',
                'import_batch' => 'batch_001',
                'approval_required' => false,
            ]),
            'attachments' => $this->faker->optional()->randomElements([
                'receipts/rec_001.jpg',
                'invoices/inv_001.pdf',
            ]),
            'notes' => $this->faker->optional()->sentence,
            'is_recurring' => $this->faker->boolean(20), // 20% chance of being recurring
            'recurrence_type' => $this->faker->optional()->randomElement(['daily', 'weekly', 'monthly', 'yearly']),
            'recurrence_interval' => $this->faker->optional()->numberBetween(1, 12),
            'recurrence_end_date' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'parent_transaction_id' => null, // Will be set later when needed
        ];
    }

    /**
     * Indicate that the transaction is an expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category' => $this->faker->randomElement(['cost_of_goods', 'labor', 'rent', 'utilities', 'marketing', 'equipment', 'supplies', 'insurance', 'taxes', 'other']),
        ]);
    }

    /**
     * Indicate that the transaction is a royalty payment.
     */
    public function royalty(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'royalty',
            'category' => 'other',
        ]);
    }

    /**
     * Indicate that the transaction is a marketing fee.
     */
    public function marketingFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'marketing_fee',
            'category' => 'marketing',
        ]);
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Create a transaction for a specific franchise.
     */
    public function forFranchise(Franchise $franchise): static
    {
        return $this->state(fn (array $attributes) => [
            'franchise_id' => $franchise->id,
        ]);
    }

    /**
     * Create a transaction for a specific unit.
     */
    public function forUnit(Unit $unit): static
    {
        return $this->state(fn (array $attributes) => [
            'unit_id' => $unit->id,
            'franchise_id' => $unit->franchise_id,
        ]);
    }

    /**
     * Create a transaction for a specific date.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => $date,
        ]);
    }

    /**
     * Create a transaction with a specific amount.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Create a transaction with a specific category.
     */
    public function withCategory(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }
}
