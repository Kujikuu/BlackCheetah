<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;

    private User $franchisor;

    private Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user for testing admin endpoints
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->countryCode(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
        ]);

        // Create a franchisor user with all required fields
        $this->franchisor = User::factory()->create([
            'role' => 'franchisor',
            'status' => 'active',
            'phone' => $this->faker->phoneNumber(),
            'country' => $this->faker->countryCode(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
        ]);

        // Create a franchise with all required fields
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
            'status' => 'active',
        ]);

        // Update the franchisor to belong to this franchise
        $this->franchisor->update(['franchise_id' => $this->franchise->id]);
    }

    public function test_can_create_franchisee_with_unit(): void
    {
        $requestData = [
            // Franchisee details
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',

            // Unit details
            'unit_name' => 'Downtown Branch',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '456 Business Ave',
            'city' => 'Los Angeles',
            'state_province' => 'CA',
            'postal_code' => '90210',
            'country' => 'US',
            'size_sqft' => 2000,
            'monthly_rent' => 5000,
            'opening_date' => '2024-06-01',
            'status' => 'planning',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'franchisee' => ['id', 'name', 'email'],
                    'unit' => ['id', 'unit_name', 'unit_code'],
                ],
            ]);

        // Verify franchisee was created
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'role' => 'franchisee',
        ]);

        // Verify unit was created
        $this->assertDatabaseHas('units', [
            'unit_name' => 'Downtown Branch',
            'franchise_id' => $this->franchise->id,
        ]);
    }

    public function test_create_franchisee_with_unit_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'unit_name',
                'franchise_id',
                'unit_type',
                'address',
                'city',
                'state_province',
                'postal_code',
                'country',
                'status',
            ]);
    }

    public function test_create_franchisee_with_unit_validates_unique_email(): void
    {
        // Create an existing user
        User::factory()->create(['email' => 'existing@example.com']);

        $requestData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com', // Duplicate email
            'phone' => '+1234567890',
            'unit_name' => 'Downtown Store',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '123 Main St',
            'city' => 'New York',
            'state_province' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'status' => 'planning',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_requires_franchisee_name(): void
    {
        $requestData = [
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'unit_name' => 'Downtown Store',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '123 Main St',
            'city' => 'New York',
            'state_province' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'status' => 'planning',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_requires_unique_email(): void
    {
        // Create existing user
        User::factory()->create(['email' => 'existing@example.com']);

        $requestData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'phone' => '+1234567890',
            'unit_name' => 'Downtown Store',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '123 Main St',
            'city' => 'New York',
            'state_province' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'status' => 'planning',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_franchisee_with_unit_generates_unique_unit_code(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'unit_name' => 'Downtown Store',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '123 Main St',
            'city' => 'New York',
            'state_province' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'status' => 'planning',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData);

        $response->assertStatus(201);

        $unit = Unit::where('unit_name', 'Downtown Store')->first();
        $this->assertNotNull($unit->unit_code);
        $this->assertNotEmpty($unit->unit_code);
    }

    public function test_create_franchisee_with_unit_rolls_back_on_error(): void
    {
        // Use an invalid franchise_id to trigger an error
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'unit_name' => 'Downtown Store',
            'franchise_id' => 99999, // Non-existent franchise
            'unit_type' => 'store',
            'address' => '123 Main St',
            'city' => 'New York',
            'state_province' => 'NY',
            'postal_code' => '10001',
            'country' => 'US',
            'status' => 'planning',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData);

        $response->assertStatus(422);

        // Assert no user was created
        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);

        // Assert no unit was created
        $this->assertDatabaseMissing('units', [
            'unit_name' => 'Downtown Store',
        ]);
    }

    public function test_generates_unique_unit_codes(): void
    {
        // Create first franchisee with unit
        $requestData1 = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'unit_name' => 'Downtown Store',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '456 Business Ave',
            'city' => 'Los Angeles',
            'state_province' => 'CA',
            'postal_code' => '90210',
            'country' => 'US',
            'status' => 'planning',
        ];

        $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData1);

        // Create second franchisee with unit
        $requestData2 = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '+1234567892',
            'unit_name' => 'Uptown Store',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '789 Oak Ave',
            'city' => 'Los Angeles',
            'state_province' => 'CA',
            'postal_code' => '90211',
            'country' => 'US',
            'status' => 'planning',
        ];

        $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData2);

        // Verify both units have unique codes
        $units = Unit::where('franchise_id', $this->franchise->id)->get();
        $this->assertCount(2, $units);
        $this->assertNotEquals($units[0]->unit_code, $units[1]->unit_code);
    }

    public function test_handles_database_transaction_rollback(): void
    {
        // Create franchisee data with invalid franchise_id to trigger error
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'unit_name' => 'Downtown Store',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '456 Business Ave',
            'city' => 'Los Angeles',
            'state_province' => 'CA',
            'postal_code' => '90210',
            'country' => 'US',
            'status' => 'planning',
        ];

        // Temporarily delete the franchise to cause a constraint violation
        $this->franchise->delete();

        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/admin/franchisees-with-unit', $requestData);

        $response->assertStatus(422);

        // Verify no partial data was created
        $this->assertDatabaseMissing('users', ['email' => 'john@example.com']);
        $this->assertDatabaseMissing('units', ['unit_name' => 'Downtown Store']);
    }
}
