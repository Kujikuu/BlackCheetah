<?php

namespace Database\Factories;

use App\Models\TechnicalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TechnicalRequest>
 */
class TechnicalRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TechnicalRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => 'TR-'.$this->faker->unique()->numberBetween(1000, 9999),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['hardware', 'software', 'network', 'pos_system', 'website', 'mobile_app', 'training', 'other']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => $this->faker->randomElement(['open', 'in_progress', 'pending_info', 'resolved', 'closed', 'cancelled']),
            'requester_id' => User::factory(),
            'assigned_to' => null,
            'franchise_id' => null,
            'unit_id' => null,
            'affected_system' => $this->faker->optional()->word(),
            'steps_to_reproduce' => $this->faker->optional()->paragraph(),
            'expected_behavior' => $this->faker->optional()->sentence(),
            'actual_behavior' => $this->faker->optional()->sentence(),
            'browser_version' => $this->faker->optional()->word(),
            'operating_system' => $this->faker->optional()->randomElement(['Windows 11', 'macOS', 'Ubuntu']),
            'device_type' => $this->faker->optional()->randomElement(['desktop', 'mobile', 'tablet']),
            'attachments' => null,
            'internal_notes' => $this->faker->optional()->paragraph(),
            'resolution_notes' => null,
            'first_response_at' => null,
            'resolved_at' => null,
            'closed_at' => null,
            'response_time_hours' => null,
            'resolution_time_hours' => null,
            'satisfaction_rating' => null,
            'satisfaction_feedback' => null,
            'is_escalated' => false,
            'escalated_at' => null,
        ];
    }

    /**
     * Indicate that the technical request is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    /**
     * Indicate that the technical request is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'first_response_at' => now(),
        ]);
    }

    /**
     * Indicate that the technical request is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the technical request is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'closed_at' => now(),
            'satisfaction_rating' => $this->faker->numberBetween(1, 5),
        ]);
    }

    /**
     * Indicate that the technical request is escalated.
     */
    public function escalated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_escalated' => true,
            'escalated_at' => now(),
            'priority' => 'urgent',
        ]);
    }
}
