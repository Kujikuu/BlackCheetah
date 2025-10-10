<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['contract', 'manual', 'certificate', 'policy', 'report']),
            'file_path' => 'documents/'.$this->faker->uuid().'.pdf',
            'file_name' => $this->faker->word().'.pdf',
            'file_extension' => 'pdf',
            'file_size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'mime_type' => 'application/pdf',
            'status' => $this->faker->randomElement(['active', 'archived']),
            'expiry_date' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'is_confidential' => $this->faker->boolean(30), // 30% chance of being confidential
            'metadata' => $this->faker->optional()->randomElement([
                ['tags' => ['important', 'legal']],
                ['version' => '1.0', 'author' => $this->faker->name()],
                null,
            ]),
        ];
    }
}
