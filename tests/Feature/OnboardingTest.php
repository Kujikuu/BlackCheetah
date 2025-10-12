<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_franchisee_with_completed_profile_gets_completed_status(): void
    {
        $franchisee = User::factory()->create([
            'role' => 'franchisee',
            'profile_completed' => true,
        ]);

        $response = $this->actingAs($franchisee, 'sanctum')
            ->getJson('/api/v1/onboarding/status');

        $response->assertStatus(200)
            ->assertJson([
                'requires_onboarding' => false,
            ]);
    }

    public function test_franchisee_can_check_onboarding_status_when_profile_complete(): void
    {
        $user = User::factory()->create([
            'role' => 'franchisee',
            'profile_completed' => true,
            'name' => 'John Doe',
            'phone' => '+1234567890',
            'country' => 'USA',
            'state' => 'California',
            'city' => 'Los Angeles',
            'address' => '123 Main St',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/onboarding/status');

        $response->assertStatus(200)
            ->assertJson([
                'profile_completed' => true,
                'message' => 'Profile already completed',
            ]);
    }

    public function test_non_franchisee_cannot_access_onboarding_status(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/onboarding/status');

        $response->assertStatus(200)
            ->assertJson([
                'requires_onboarding' => false,
                'message' => 'User is not a franchisee',
            ]);
    }

    public function test_unauthenticated_user_cannot_access_onboarding_status(): void
    {
        $response = $this->getJson('/api/v1/onboarding/status');

        $response->assertStatus(401);
    }

    public function test_franchisee_can_complete_profile_with_valid_data(): void
    {
        $franchisee = User::factory()->create([
            'role' => 'franchisee',
            'profile_completed' => false,
        ]);

        $profileData = [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'country' => $this->faker->country,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'address' => $this->faker->address,
        ];

        $response = $this->actingAs($franchisee, 'sanctum')
            ->postJson('/api/v1/onboarding/complete', $profileData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Profile completed successfully',
            ])
            ->assertJsonStructure([
                'user' => [
                    'name',
                    'email',
                    'phone',
                    'country',
                    'state',
                    'city',
                    'address',
                    'profile_completed',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $franchisee->id,
            'name' => $profileData['name'],
            'phone' => $profileData['phone'],
            'country' => $profileData['country'],
            'state' => $profileData['state'],
            'city' => $profileData['city'],
            'address' => $profileData['address'],
            'profile_completed' => true,
        ]);
    }

    public function test_franchisee_cannot_complete_profile_with_invalid_data(): void
    {
        $franchisee = User::factory()->create([
            'role' => 'franchisee',
            'profile_completed' => false,
        ]);

        $invalidData = [
            'name' => '', // Required field empty
            'phone' => '',
            'country' => '',
            'state' => '',
            'city' => '',
            'address' => '',
        ];

        $response = $this->actingAs($franchisee, 'sanctum')
            ->postJson('/api/v1/onboarding/complete', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'country', 'state', 'city', 'address']);
    }

    public function test_non_franchisee_cannot_complete_onboarding(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $profileData = [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'country' => $this->faker->country,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'address' => $this->faker->address,
        ];

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/onboarding/complete', $profileData);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_complete_profile(): void
    {
        $profileData = [
            'name' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'country' => $this->faker->country,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'address' => $this->faker->address,
        ];

        $response = $this->postJson('/api/v1/onboarding/complete', $profileData);

        $response->assertStatus(401);
    }
}
