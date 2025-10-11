<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $franchisor;

    protected Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->franchisor = User::factory()->create(['role' => 'franchisor']);
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);
    }

    public function test_can_create_unit_with_correct_field_names(): void
    {
        $unitData = [
            'unit_name' => 'Test Unit',
            'franchise_id' => $this->franchise->id,
            'unit_type' => 'store',
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state_province' => 'Test State',
            'postal_code' => '12345',
            'country' => 'US',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'size_sqft' => 1000,
            'opening_date' => '2024-12-01',
            'status' => 'planning',
        ];

        $response = $this->actingAs($this->franchisor)
            ->postJson('/api/v1/units', $unitData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Unit created successfully',
            ]);

        $this->assertDatabaseHas('units', [
            'unit_name' => 'Test Unit',
            'franchise_id' => $this->franchise->id,
            'address' => '123 Test Street',
            'city' => 'Test City',
            'state_province' => 'Test State',
        ]);
    }

    public function test_can_list_units(): void
    {
        Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_name' => 'Test Unit',
        ]);

        $response = $this->actingAs($this->franchisor)
            ->getJson('/api/v1/units');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Units retrieved successfully',
            ]);

        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_unit_creation_fails_with_invalid_data(): void
    {
        $invalidData = [
            'franchise_id' => $this->franchise->id,
            // Missing required fields
        ];

        $response = $this->actingAs($this->franchisor)
            ->postJson('/api/v1/units', $invalidData);

        $response->assertStatus(422);
    }
}
