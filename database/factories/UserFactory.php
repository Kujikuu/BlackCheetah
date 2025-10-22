<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['admin', 'franchisor', 'franchisee', 'sales']),
            'status' => fake()->randomElement(['active', 'inactive', 'pending', 'suspended']),
            'phone' => fake()->phoneNumber(),
            'avatar' => fake()->imageUrl(),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'nationality' => fake()->countryCode(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'address' => fake()->address(),
            'last_login_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'preferences' => [],
            'profile_completion' => [],
            'profile_completed' => fake()->boolean(),
            'franchise_id' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
