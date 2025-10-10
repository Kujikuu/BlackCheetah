<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'attachments' => null,
        ];
    }

    /**
     * Indicate that the note has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                [
                    'name' => 'document.pdf',
                    'path' => 'notes/attachments/document.pdf',
                    'size' => 1024000,
                    'type' => 'application/pdf',
                ],
                [
                    'name' => 'image.jpg',
                    'path' => 'notes/attachments/image.jpg',
                    'size' => 512000,
                    'type' => 'image/jpeg',
                ],
            ],
        ]);
    }

    /**
     * Indicate that the note is for a specific lead.
     */
    public function forLead(Lead $lead): static
    {
        return $this->state(fn (array $attributes) => [
            'lead_id' => $lead->id,
        ]);
    }

    /**
     * Indicate that the note is created by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
