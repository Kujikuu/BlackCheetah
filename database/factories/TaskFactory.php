<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled', 'on_hold'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $types = ['onboarding', 'training', 'compliance', 'maintenance', 'marketing', 'operations', 'finance', 'support', 'other'];

        $status = $this->faker->randomElement($statuses);
        $startedAt = $status !== 'pending' ? $this->faker->dateTimeBetween('-30 days', 'now') : null;
        $completedAt = $status === 'completed' ? $this->faker->dateTimeBetween($startedAt ?? '-30 days', 'now') : null;

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(2),
            'type' => $this->faker->randomElement($types),
            'priority' => $this->faker->randomElement($priorities),
            'status' => $status,
            'assigned_to' => \App\Models\User::factory(),
            'created_by' => \App\Models\User::factory(),
            'franchise_id' => \App\Models\Franchise::factory(),
            'unit_id' => null, // Will be set when needed
            'lead_id' => null, // Will be set when needed
            'due_date' => $this->faker->optional(0.7)->dateTimeBetween('now', '+30 days'),
            'started_at' => $startedAt,
            'completed_at' => $completedAt,
            'estimated_hours' => $this->faker->optional(0.6)->numberBetween(1, 40),
            'actual_hours' => $completedAt ? $this->faker->numberBetween(1, 50) : null,
            'checklist' => $this->faker->optional(0.4)->randomElements([
                ['item' => 'Review requirements', 'completed' => true],
                ['item' => 'Prepare materials', 'completed' => $this->faker->boolean()],
                ['item' => 'Execute task', 'completed' => false],
                ['item' => 'Quality check', 'completed' => false],
            ], $this->faker->numberBetween(1, 4)),
            'attachments' => $this->faker->optional(0.3)->randomElements([
                ['name' => 'document.pdf', 'path' => '/storage/tasks/document.pdf'],
                ['name' => 'image.jpg', 'path' => '/storage/tasks/image.jpg'],
            ], $this->faker->numberBetween(1, 2)),
            'notes' => $this->faker->optional(0.5)->paragraph(),
            'completion_notes' => $completedAt ? $this->faker->optional(0.7)->paragraph() : null,
            'is_recurring' => $this->faker->boolean(20), // 20% chance of being recurring
            'recurrence_type' => $this->faker->optional(0.2)->randomElement(['daily', 'weekly', 'monthly', 'quarterly', 'yearly']),
            'recurrence_interval' => $this->faker->optional(0.2)->numberBetween(1, 4),
            'recurrence_end_date' => $this->faker->optional(0.1)->dateTimeBetween('+1 month', '+1 year'),
        ];
    }
}
