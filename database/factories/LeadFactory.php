<?php

namespace Database\Factories;

use App\Models\Franchise;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'franchise_id' => Franchise::factory(),
            'assigned_to' => User::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'company_name' => $this->faker->company(),
            'job_title' => $this->faker->jobTitle(),
            'nationality' => $this->faker->country(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'lead_source' => $this->faker->randomElement(['website', 'referral', 'social_media', 'advertisement', 'cold_call', 'event', 'other']),
            'status' => $this->faker->randomElement(['new', 'contacted', 'qualified', 'proposal_sent', 'negotiating', 'closed_won', 'closed_lost']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'estimated_investment' => $this->faker->randomFloat(2, 50000, 500000),
            'franchise_fee_quoted' => $this->faker->randomFloat(2, 25000, 100000),
            'notes' => $this->faker->paragraph(),
            'expected_decision_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'last_contact_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'next_follow_up_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'contact_attempts' => $this->faker->numberBetween(0, 10),
            'interests' => $this->faker->randomElements(['franchise_opportunities', 'investment_details', 'location_assistance', 'training_programs'], $this->faker->numberBetween(1, 3)),
            'documents' => [],
            'communication_log' => [],
        ];
    }

    /**
     * Create a lead for a specific franchise
     */
    public function forFranchise(Franchise $franchise): static
    {
        return $this->state(fn (array $attributes) => [
            'franchise_id' => $franchise->id,
        ]);
    }

    /**
     * Create a lead assigned to a specific user
     */
    public function assignedTo(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $user->id,
        ]);
    }

    /**
     * Create a lead with high priority
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    /**
     * Create a qualified lead
     */
    public function qualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'qualified',
        ]);
    }
}
