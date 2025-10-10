<?php

namespace Database\Factories;

use App\Models\Franchise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Franchise>
 */
class FranchiseFactory extends Factory
{
    protected $model = Franchise::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'franchisor_id' => User::factory(),
            'business_name' => $this->faker->company(),
            'brand_name' => $this->faker->companySuffix(),
            'industry' => $this->faker->randomElement(['Food & Beverage', 'Retail', 'Services', 'Technology', 'Healthcare']),
            'description' => $this->faker->paragraph(),
            'website' => $this->faker->url(),
            'logo' => null,
            'business_registration_number' => $this->faker->unique()->numerify('REG-#########'),
            'tax_id' => $this->faker->numerify('TAX-#########'),
            'business_type' => $this->faker->randomElement(['corporation', 'llc', 'partnership', 'sole_proprietorship']),
            'established_date' => $this->faker->dateTimeBetween('-10 years', '-1 year'),
            'headquarters_country' => $this->faker->country(),
            'headquarters_city' => $this->faker->city(),
            'headquarters_address' => $this->faker->address(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->companyEmail(),
            'franchise_fee' => $this->faker->randomFloat(2, 10000, 100000),
            'royalty_percentage' => $this->faker->randomFloat(2, 3, 10),
            'marketing_fee_percentage' => $this->faker->randomFloat(2, 1, 5),
            'total_units' => $this->faker->numberBetween(1, 100),
            'active_units' => $this->faker->numberBetween(1, 50),
            'status' => $this->faker->randomElement(['active', 'inactive', 'pending_approval', 'suspended']),
            'plan' => $this->faker->randomElement(['Basic', 'Pro', 'Enterprise']),
            'business_hours' => [
                'monday' => ['open' => '09:00', 'close' => '17:00'],
                'tuesday' => ['open' => '09:00', 'close' => '17:00'],
                'wednesday' => ['open' => '09:00', 'close' => '17:00'],
                'thursday' => ['open' => '09:00', 'close' => '17:00'],
                'friday' => ['open' => '09:00', 'close' => '17:00'],
                'saturday' => ['open' => '10:00', 'close' => '16:00'],
                'sunday' => ['open' => '10:00', 'close' => '16:00'],
            ],
            'social_media' => [
                'facebook' => $this->faker->url(),
                'twitter' => $this->faker->url(),
                'instagram' => $this->faker->url(),
            ],
            'documents' => [],
        ];
    }

    /**
     * Create a franchise with a specific franchisor
     */
    public function forFranchisor(User $franchisor): static
    {
        return $this->state(fn (array $attributes) => [
            'franchisor_id' => $franchisor->id,
        ]);
    }
}
