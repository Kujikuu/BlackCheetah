<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FranchiseRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a franchisor user for testing
        $this->franchisor = User::factory()->create([
            'role' => 'franchisor',
            'email' => 'franchisor@test.com',
        ]);
    }

    public function test_franchise_registration_success(): void
    {
        // Ensure no existing franchise for this user
        Franchise::where('franchisor_id', $this->franchisor->id)->delete();

        // Create a token for the user
        $token = $this->franchisor->createToken('test-token');

        $franchiseData = [
            'personalInfo' => [
                'contactNumber' => '+1-555-123-4567',
                'nationality' => 'United States',
                'state' => 'California',
                'city' => 'Los Angeles',
                'address' => '123 Business Ave, Suite 100',
            ],
            'franchiseDetails' => [
                'franchiseDetails' => [
                    'franchiseName' => 'Test Coffee Co.',
                    'website' => 'https://testcoffee.com',
                    'logo' => null,
                ],
                'legalDetails' => [
                    'legalEntityName' => 'Test Coffee Corporation',
                    'businessStructure' => 'corporation',
                    'taxId' => 'EIN-12-3456789',
                    'industry' => 'Food & Beverage',
                    'fundingAmount' => 'SAR 750,000',
                    'fundingSource' => 'Bank Loan',
                ],
                'contactDetails' => [
                    'contactNumber' => '+1-555-123-4567',
                    'email' => 'info@testcoffee.com',
                    'address' => '123 Business Ave, Suite 100',
                    'nationality' => 'United States',
                    'state' => 'California',
                    'city' => 'Los Angeles',
                ],
            ],
            'documents' => [
                'fdd' => null,
                'franchiseAgreement' => null,
                'operationsManual' => null,
                'brandGuidelines' => null,
                'legalDocuments' => null,
            ],
            'reviewComplete' => [
                'termsAccepted' => true,
            ],
        ];

        $response = $this->postJson('/api/v1/franchisor/franchise/register', $franchiseData, [
            'Authorization' => 'Bearer '.$token->plainTextToken,
            'Accept' => 'application/json',
        ]);

        // Debug output
        if ($response->status() !== 201) {
            dump('Response status: '.$response->status());
            dump('Response content: '.$response->content());
        }

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Franchise registered successfully',
            ]);

        // Verify franchise was created
        $this->assertDatabaseHas('franchises', [
            'franchisor_id' => $this->franchisor->id,
            'business_name' => 'Test Coffee Co.',
            'website' => 'https://testcoffee.com',
            'industry' => 'Food & Beverage',
        ]);

        // Verify user contact info was updated
        $this->franchisor->refresh();
        $this->assertEquals('+1-555-123-4567', $this->franchisor->phone);
        $this->assertEquals('123 Business Ave, Suite 100', $this->franchisor->address);
        $this->assertEquals('United States', $this->franchisor->nationality);
        $this->assertEquals('California', $this->franchisor->state);
        $this->assertEquals('Los Angeles', $this->franchisor->city);
    }

    public function test_franchise_registration_validation_errors(): void
    {
        $token = $this->franchisor->createToken('test-token');

        $invalidData = [
            'personalInfo' => [
                'contactNumber' => '', // Required field missing
                'country' => '',
                'state' => '',
                'city' => '',
                'address' => '',
            ],
            'franchiseDetails' => [
                'franchiseDetails' => [
                    'franchiseName' => '', // Required field missing
                    'website' => 'invalid-url', // Invalid URL
                ],
                'legalDetails' => [
                    'legalEntityName' => '',
                    'businessStructure' => '',
                    'taxId' => '',
                    'industry' => '',
                    'fundingAmount' => '',
                    'fundingSource' => '',
                ],
            ],
            'reviewComplete' => [
                'termsAccepted' => false, // Must be true
            ],
        ];

        $response = $this->postJson('/api/v1/franchisor/franchise/register', $invalidData, [
            'Authorization' => 'Bearer '.$token->plainTextToken,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'personalInfo.contactNumber',
                'franchiseDetails.franchiseDetails.franchiseName',
                'franchiseDetails.franchiseDetails.website',
                'reviewComplete.termsAccepted',
            ]);
    }

    public function test_franchise_data_retrieval(): void
    {
        $token = $this->franchisor->createToken('test-token');

        // Create a franchise for the user
        $franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
            'business_name' => 'Test Franchise',
            'website' => 'https://test.com',
            'industry' => 'Food & Beverage',
        ]);

        $response = $this->getJson('/api/v1/franchisor/franchise/data', [
            'Authorization' => 'Bearer '.$token->plainTextToken,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'franchise' => [
                        'franchiseDetails',
                        'legalDetails',
                        'contactDetails',
                    ],
                    'documents',
                    'products',
                ],
            ]);
    }

    public function test_franchise_update(): void
    {
        $token = $this->franchisor->createToken('test-token');

        // First create a franchise
        $franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        $updateData = [
            'personalInfo' => [
                'contactNumber' => '+1-555-999-8888',
                'nationality' => 'Updated Country',
                'address' => '456 Updated St',
                'city' => 'Updated City',
                'state' => 'Updated State',
            ],
            'franchiseDetails' => [
                'franchiseDetails' => [
                    'franchiseName' => 'Updated Franchise Name',
                    'website' => 'https://updated.com',
                ],
                'legalDetails' => [
                    'legalEntityName' => 'Updated Legal Entity',
                    'businessStructure' => 'llc',
                    'industry' => 'Technology',
                ],
                'contactDetails' => [
                    'contactNumber' => '+1-555-999-8888',
                    'email' => 'updated@example.com',
                    'address' => '456 Updated St',
                    'nationality' => 'Updated Country',
                    'city' => 'Updated City',
                    'state' => 'Updated State',
                ],
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $updateData, [
            'Authorization' => 'Bearer '.$token->plainTextToken,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Franchise updated successfully',
            ]);

        // Verify franchise was updated
        $franchise->refresh();
        $this->assertEquals('Updated Franchise Name', $franchise->brand_name);
        $this->assertEquals('Updated Legal Entity', $franchise->business_name);
        $this->assertEquals('https://updated.com', $franchise->website);
        $this->assertEquals('Technology', $franchise->industry);

        // Verify user contact info was updated
        $this->franchisor->refresh();
        $this->assertEquals('+1-555-999-8888', $this->franchisor->phone);
        $this->assertEquals('456 Updated St', $this->franchisor->address);
        $this->assertEquals('Updated Country', $this->franchisor->nationality);
        $this->assertEquals('Updated State', $this->franchisor->state);
        $this->assertEquals('Updated City', $this->franchisor->city);
    }

    public function test_unauthorized_access(): void
    {
        // Test without authentication
        $response = $this->postJson('/api/v1/franchisor/franchise/register', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/v1/franchisor/franchise/data');
        $response->assertStatus(401);

        $response = $this->putJson('/api/v1/franchisor/franchise/update', []);
        $response->assertStatus(401);
    }

    public function test_non_franchisor_access(): void
    {
        // Create a user with broker role (not franchisor)
        $brokerUser = User::factory()->create(['role' => 'broker']);
        $token = $brokerUser->createToken('test-token');

        $response = $this->postJson('/api/v1/franchisor/franchise/register', [], [
            'Authorization' => 'Bearer '.$token->plainTextToken,
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(403);

        $response = $this->getJson('/api/v1/franchisor/franchise/data', [
            'Authorization' => 'Bearer '.$token->plainTextToken,
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(403);

        $response = $this->putJson('/api/v1/franchisor/franchise/update', [], [
            'Authorization' => 'Bearer '.$token->plainTextToken,
            'Accept' => 'application/json',
        ]);
        $response->assertStatus(403);
    }

    public function test_franchisor_can_check_franchise_status(): void
    {
        $token = $this->franchisor->createToken('test-token');

        $response = $this->actingAs($this->franchisor, 'sanctum')
            ->getJson('/api/v1/onboarding/franchise-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_franchise_registration' => true,
                    'has_franchise' => false,
                ],
            ]);
    }

    public function test_franchisor_with_franchise_has_different_status(): void
    {
        // Create a franchise for the franchisor
        Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        $response = $this->actingAs($this->franchisor, 'sanctum')
            ->getJson('/api/v1/onboarding/franchise-status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_franchise_registration' => false,
                    'has_franchise' => true,
                ],
            ]);
    }

    public function test_middleware_blocks_franchisor_without_franchise_from_dashboard(): void
    {
        // Ensure franchisor has no franchise
        Franchise::where('franchisor_id', $this->franchisor->id)->delete();

        $response = $this->actingAs($this->franchisor, 'sanctum')
            ->getJson('/api/v1/franchisor/dashboard/stats');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Franchise registration required',
                'requires_franchise_registration' => true,
                'redirect_to' => '/franchisor/franchise-registration',
            ]);
    }

    public function test_middleware_allows_franchisor_with_franchise_to_access_dashboard(): void
    {
        // Create a franchise for the franchisor
        Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        $response = $this->actingAs($this->franchisor, 'sanctum')
            ->getJson('/api/v1/franchisor/dashboard/stats');

        // Should not be blocked by franchise check middleware
        // Actual response depends on DashboardController implementation
        $this->assertNotEquals(403, $response->status());
    }

    public function test_middleware_allows_franchise_registration_route_without_franchise(): void
    {
        // Ensure franchisor has no franchise
        Franchise::where('franchisor_id', $this->franchisor->id)->delete();

        // Should be able to access franchise registration even without a franchise
        $response = $this->actingAs($this->franchisor, 'sanctum')
            ->getJson('/api/v1/franchisor/franchise/data');

        // Should return 404 or empty data, not 403
        $this->assertNotEquals(403, $response->status());
    }

    public function test_login_response_includes_franchise_registration_flag(): void
    {
        // Ensure franchisor has no franchise
        Franchise::where('franchisor_id', $this->franchisor->id)->delete();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->franchisor->email,
            'password' => 'password', // Default factory password
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'requiresFranchiseRegistration' => true,
            ]);
    }

    public function test_login_response_shows_no_registration_needed_when_franchise_exists(): void
    {
        // Create a franchise for the franchisor
        Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->franchisor->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'requiresFranchiseRegistration' => false,
            ]);
    }

    public function test_non_franchisor_login_does_not_have_franchise_registration_flag(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'email' => 'broker@test.com',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $broker->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'requiresFranchiseRegistration' => false,
            ]);
    }
}
