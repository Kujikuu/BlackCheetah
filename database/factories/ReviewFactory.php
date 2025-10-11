<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sources = ['in_person', 'phone', 'email', 'social_media', 'other'];
        $statuses = ['published', 'published', 'published', 'draft']; // Mostly published reviews

        $rating = fake()->numberBetween(1, 5);
        $sentiment = $rating >= 4 ? 'positive' : ($rating <= 2 ? 'negative' : 'neutral');

        return [
            'unit_id' => 1, // Will be overridden in seeders
            'franchisee_id' => 1, // Will be overridden in seeders - who added this review
            'customer_name' => fake()->name(),
            'customer_email' => fake()->optional(0.7)->email(),
            'customer_phone' => fake()->optional(0.5)->phoneNumber(),
            'rating' => $rating,
            'comment' => fake()->paragraph(3),
            'sentiment' => $sentiment,
            'status' => fake()->randomElement($statuses),
            'internal_notes' => fake()->optional(0.3)->paragraph(1),
            'review_source' => fake()->randomElement($sources),
            'verified_purchase' => true, // Franchisee confirms this was real customer
            'review_date' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
