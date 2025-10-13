<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jobTitles = [
            'Store Manager',
            'Assistant Manager',
            'Sales Associate',
            'Cashier',
            'Barista',
            'Cook',
            'Supervisor',
            'Customer Service Rep',
            'Maintenance Tech',
            'Security Guard',
        ];

        $departments = [
            'Operations',
            'Sales',
            'Customer Service',
            'Food & Beverage',
            'Maintenance',
            'Security',
            'Administration',
        ];

        $skills = [
            'Customer Service',
            'Sales',
            'Cash Handling',
            'Food Safety',
            'Coffee Making',
            'Inventory Management',
            'Team Leadership',
            'Problem Solving',
            'Communication',
            'Time Management',
        ];

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'job_title' => $this->faker->randomElement($jobTitles),
            'department' => $this->faker->randomElement($departments),
            'salary' => $this->faker->numberBetween(2500, 8000), // SAR per month
            'hire_date' => $this->faker->dateTimeBetween('-3 years', 'now'),
            'shift_start' => $this->faker->time(),
            'shift_end' => $this->faker->time(),
            'status' => $this->faker->randomElement(['active', 'on_leave', 'inactive']),
            'employment_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract']),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'skills' => $this->faker->randomElements($skills, $this->faker->numberBetween(2, 5)),
            'certifications' => $this->faker->optional(0.4)->randomElements([
                'Food Safety Certificate',
                'First Aid',
                'Coffee Brewing Certification',
                'Customer Service Excellence',
                'Management Training',
            ], $this->faker->numberBetween(1, 3)),
        ];
    }

    /**
     * Indicate that the staff member is a manager.
     */
    public function manager(): static
    {
        return $this->state(fn(array $attributes) => [
            'job_title' => 'Store Manager',
            'department' => 'Operations',
            'salary' => $this->faker->numberBetween(6000, 10000),
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the staff member is on leave.
     */
    public function onLeave(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'on_leave',
        ]);
    }

    /**
     * Indicate that the staff member works part time.
     */
    public function partTime(): static
    {
        return $this->state(fn(array $attributes) => [
            'employment_type' => 'part_time',
            'salary' => $this->faker->numberBetween(1500, 3500),
        ]);
    }

    /**
     * Create staff without skills and certifications (for testing).
     */
    public function withoutSkillsAndCertifications(): static
    {
        return $this->state(fn(array $attributes) => [
            'skills' => null,
            'certifications' => null,
        ]);
    }
}
