<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FranchisorProfileCompletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test profile completion status for incomplete franchise.
     */
    public function test_profile_completion_status_incomplete(): void
    {
        // Create a franchisor user
        $franchisor = User::factory()->create(['role' => 'franchisor']);

        // Create an incomplete franchise (only minimum database-required fields)
        Franchise::create([
            'franchisor_id' => $franchisor->id,
            'business_name' => 'Test Business',
            'brand_name' => 'Test Brand',
            'industry' => 'Technology',
            'business_registration_number' => '123456789',
            'business_type' => 'llc',
            'headquarters_country' => 'USA',
            'headquarters_city' => 'New York',
            'headquarters_address' => '123 Main St',
            'contact_phone' => '+1234567890',
            'contact_email' => 'test@test.com',
            // Missing: description, website, tax_id, established_date, franchise_fee, royalty_percentage
        ]);

        // Authenticate as the franchisor
        $this->actingAs($franchisor);

        // Make the API request
        $response = $this->getJson('/api/v1/franchisor/profile/completion-status');

        // Assert the response structure and that it's incomplete
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_complete' => false,
                    'total_fields' => 16,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'is_complete',
                    'completion_percentage',
                    'completed_fields',
                    'total_fields',
                    'missing_fields',
                ],
            ]);

        // Assert that there are missing fields
        $this->assertGreaterThan(0, count($response->json('data.missing_fields')));
    }

    /**
     * Test profile completion status for complete franchise.
     */
    public function test_profile_completion_status_complete(): void
    {
        // Create a franchisor user
        $user = User::factory()->create([
            'role' => 'franchisor',
        ]);

        // Create a complete franchise with all required fields
        $franchise = Franchise::factory()->create([
            'franchisor_id' => $user->id,
            'business_name' => 'Test Business',
            'brand_name' => 'Test Brand',
            'industry' => 'Technology',
            'description' => 'Test Description',
            'website' => 'https://test.com',
            'business_registration_number' => '123456789',
            'tax_id' => 'TAX123456',
            'business_type' => 'llc',
            'established_date' => '2020-01-01',
            'headquarters_country' => 'USA',
            'headquarters_city' => 'New York',
            'headquarters_address' => '123 Main St',
            'contact_phone' => '+1234567890',
            'contact_email' => 'test@test.com',
            'franchise_fee' => 50000,
            'royalty_percentage' => 5.5,
        ]);

        // Authenticate the user
        Sanctum::actingAs($user);

        // Make the API request
        $response = $this->getJson('/api/v1/franchisor/profile/completion-status');

        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_complete' => true,
                    'completion_percentage' => 100,
                    'completed_fields' => 16,
                    'total_fields' => 16,
                    'missing_fields' => [],
                ],
            ]);
    }

    /**
     * Test unauthorized access to profile completion status.
     */
    public function test_profile_completion_status_unauthorized(): void
    {
        // Make the API request without authentication
        $response = $this->getJson('/api/v1/franchisor/profile/completion-status');

        // Assert unauthorized response
        $response->assertStatus(401);
    }
}
