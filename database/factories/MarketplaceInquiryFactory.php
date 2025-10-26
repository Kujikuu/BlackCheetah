<?php

namespace Database\Factories;

use App\Models\Franchise;
use App\Models\MarketplaceInquiry;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarketplaceInquiry>
 */
class MarketplaceInquiryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = MarketplaceInquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inquiryType = $this->faker->randomElement(['franchise', 'property']);

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+966' . $this->faker->numberBetween(500000000, 599999999),
            'inquiry_type' => $inquiryType,
            'franchise_id' => $inquiryType === 'franchise' ? Franchise::factory() : null,
            'property_id' => $inquiryType === 'property' ? Property::factory() : null,
            'message' => $this->faker->paragraphs(2, true),
            'investment_budget' => $this->faker->randomElement([
                '100,000 - 250,000 SAR',
                '250,000 - 500,000 SAR',
                '500,000 - 1,000,000 SAR',
                '1,000,000+ SAR',
            ]),
            'preferred_location' => $this->faker->randomElement([
                'Riyadh',
                'Jeddah',
                'Dammam',
                'Mecca',
                'Medina',
                'Khobar',
                'Any location',
            ]),
            'timeline' => $this->faker->randomElement([
                'Immediate',
                'Within 1 month',
                'Within 3 months',
                'Within 6 months',
                'Flexible',
            ]),
            'status' => 'new',
        ];
    }

    /**
     * Indicate that the inquiry is about a franchise
     */
    public function franchise(?Franchise $franchise = null): static
    {
        return $this->state(fn (array $attributes) => [
            'inquiry_type' => 'franchise',
            'franchise_id' => $franchise?->id ?? Franchise::factory(),
            'property_id' => null,
        ]);
    }

    /**
     * Indicate that the inquiry is about a property
     */
    public function property(?Property $property = null): static
    {
        return $this->state(fn (array $attributes) => [
            'inquiry_type' => 'property',
            'franchise_id' => null,
            'property_id' => $property?->id ?? Property::factory(),
        ]);
    }

    /**
     * Indicate that the inquiry is new
     */
    public function statusNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
        ]);
    }

    /**
     * Indicate that the inquiry has been contacted
     */
    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'contacted',
        ]);
    }

    /**
     * Indicate that the inquiry is closed
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }

    /**
     * Indicate that the inquiry is for a specific franchise
     */
    public function forFranchise(Franchise $franchise): static
    {
        return $this->state(fn (array $attributes) => [
            'inquiry_type' => 'franchise',
            'franchise_id' => $franchise->id,
            'property_id' => null,
        ]);
    }

    /**
     * Indicate that the inquiry is for a specific property
     */
    public function forProperty(Property $property): static
    {
        return $this->state(fn (array $attributes) => [
            'inquiry_type' => 'property',
            'franchise_id' => null,
            'property_id' => $property->id,
        ]);
    }
}

