<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $propertyTypes = ['retail', 'office', 'kiosk', 'food_court', 'standalone'];
        $cities = ['Riyadh', 'Jeddah', 'Dammam', 'Mecca', 'Medina', 'Khobar', 'Taif'];
        $provinces = ['Riyadh Province', 'Makkah Province', 'Eastern Province', 'Medina Province', 'Asir Province'];

        $city = $this->faker->randomElement($cities);
        $sizeSqm = $this->faker->numberBetween(50, 500);

        return [
            'broker_id' => User::factory(),
            'title' => $this->faker->catchPhrase() . ' ' . $this->faker->randomElement(['Location', 'Space', 'Property', 'Venue']),
            'description' => $this->faker->paragraphs(3, true),
            'property_type' => $this->faker->randomElement($propertyTypes),
            'size_sqm' => $sizeSqm,
            'state_province' => $this->faker->randomElement($provinces),
            'city' => $city,
            'address' => $this->faker->streetAddress(),
            'postal_code' => $this->faker->postcode(),
            'latitude' => $this->faker->latitude(21, 32),
            'longitude' => $this->faker->longitude(34, 56),
            'monthly_rent' => $this->faker->numberBetween(5000, 50000),
            'deposit_amount' => $this->faker->numberBetween(10000, 100000),
            'lease_term_months' => $this->faker->randomElement([12, 24, 36, 48, 60]),
            'available_from' => $this->faker->dateTimeBetween('now', '+3 months'),
            'amenities' => $this->generateAmenities(),
            'images' => $this->generateImages(),
            'status' => 'available',
            'contact_info' => $this->faker->phoneNumber() . ' | ' . $this->faker->email(),
        ];
    }

    /**
     * Generate random amenities array
     */
    private function generateAmenities(): array
    {
        $allAmenities = [
            'parking',
            'security',
            'wifi',
            'air_conditioning',
            'heating',
            'elevator',
            'wheelchair_accessible',
            'storage',
            'kitchen',
            'bathroom',
            'meeting_room',
            'reception',
        ];

        $count = $this->faker->numberBetween(3, 8);
        return $this->faker->randomElements($allAmenities, $count);
    }

    /**
     * Generate placeholder images array
     */
    private function generateImages(): array
    {
        $count = $this->faker->numberBetween(2, 5);
        $images = [];

        for ($i = 0; $i < $count; $i++) {
            $images[] = [
                'url' => 'properties/property_' . $this->faker->uuid() . '.jpg',
                'alt' => 'Property Image ' . ($i + 1),
            ];
        }

        return $images;
    }

    /**
     * Indicate that the property is available
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'available_from' => $this->faker->dateTimeBetween('now', '+2 months'),
        ]);
    }

    /**
     * Indicate that the property is under negotiation
     */
    public function underNegotiation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'under_negotiation',
        ]);
    }

    /**
     * Indicate that the property is leased
     */
    public function leased(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'leased',
            'available_from' => null,
        ]);
    }

    /**
     * Indicate that the property is unavailable
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'unavailable',
            'available_from' => null,
        ]);
    }

    /**
     * Create property for a specific broker
     */
    public function forBroker(User $broker): static
    {
        return $this->state(fn (array $attributes) => [
            'broker_id' => $broker->id,
        ]);
    }

    /**
     * Create retail property
     */
    public function retail(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'retail',
            'size_sqm' => $this->faker->numberBetween(100, 500),
            'monthly_rent' => $this->faker->numberBetween(15000, 50000),
        ]);
    }

    /**
     * Create office property
     */
    public function office(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'office',
            'size_sqm' => $this->faker->numberBetween(50, 200),
            'monthly_rent' => $this->faker->numberBetween(10000, 30000),
        ]);
    }

    /**
     * Create kiosk property
     */
    public function kiosk(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'kiosk',
            'size_sqm' => $this->faker->numberBetween(10, 50),
            'monthly_rent' => $this->faker->numberBetween(5000, 15000),
        ]);
    }

    /**
     * Create food court property
     */
    public function foodCourt(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'food_court',
            'size_sqm' => $this->faker->numberBetween(50, 150),
            'monthly_rent' => $this->faker->numberBetween(12000, 35000),
        ]);
    }

    /**
     * Create standalone property
     */
    public function standalone(): static
    {
        return $this->state(fn (array $attributes) => [
            'property_type' => 'standalone',
            'size_sqm' => $this->faker->numberBetween(200, 800),
            'monthly_rent' => $this->faker->numberBetween(30000, 80000),
        ]);
    }
}

