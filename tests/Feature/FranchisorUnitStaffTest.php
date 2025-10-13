<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Staff;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FranchisorUnitStaffTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test franchisor can get staff for a unit in their franchise network
     */
    public function test_franchisor_can_get_unit_staff(): void
    {
        // Create a franchisor
        $franchisor = User::factory()->create(['role' => 'franchisor']);

        // Create a franchise owned by the franchisor
        $franchise = Franchise::factory()->create(['franchisor_id' => $franchisor->id]);

        // Create a franchisee
        $franchisee = User::factory()->create(['role' => 'franchisee']);

        // Create a unit belonging to the franchise
        $unit = Unit::factory()->create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => $franchisee->id,
        ]);

        // Create some staff for the unit
        $staff1 = Staff::factory()->withoutSkillsAndCertifications()->create([
            'status' => 'active',
            'name' => 'John Doe',
            'job_title' => 'Manager',
        ]);
        $unit->staff()->attach($staff1->id, ['assigned_date' => now()]);

        $staff2 = Staff::factory()->withoutSkillsAndCertifications()->create([
            'status' => 'active',
            'name' => 'Jane Smith',
            'job_title' => 'Cashier',
        ]);
        $unit->staff()->attach($staff2->id, ['assigned_date' => now()]);

        // Create inactive staff (should not be returned - has end_date)
        $staff3 = Staff::factory()->withoutSkillsAndCertifications()->create(['status' => 'active']);
        $unit->staff()->attach($staff3->id, [
            'assigned_date' => now()->subDays(30),
            'end_date' => now()->subDays(1),
        ]);

        // Make request as franchisor
        $response = $this->actingAs($franchisor, 'sanctum')
            ->getJson("/api/v1/franchisor/units/{$unit->id}/staff");

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Unit staff retrieved successfully',
            ])
            ->assertJsonCount(2, 'data');

        // Verify staff data structure
        $response->assertJsonFragment([
            'name' => 'John Doe',
            'jobTitle' => 'Manager',
        ]);

        $response->assertJsonFragment([
            'name' => 'Jane Smith',
            'jobTitle' => 'Cashier',
        ]);
    }

    /**
     * Test franchisor cannot access staff from another franchise
     */
    public function test_franchisor_cannot_access_other_franchise_staff(): void
    {
        // Create two franchisors
        $franchisor1 = User::factory()->create(['role' => 'franchisor']);
        $franchisor2 = User::factory()->create(['role' => 'franchisor']);

        // Create franchises
        $franchise1 = Franchise::factory()->create(['franchisor_id' => $franchisor1->id]);
        $franchise2 = Franchise::factory()->create(['franchisor_id' => $franchisor2->id]);

        // Create franchisees
        $franchisee1 = User::factory()->create(['role' => 'franchisee']);
        $franchisee2 = User::factory()->create(['role' => 'franchisee']);

        // Create units
        $unit1 = Unit::factory()->create([
            'franchise_id' => $franchise1->id,
            'franchisee_id' => $franchisee1->id,
        ]);

        $unit2 = Unit::factory()->create([
            'franchise_id' => $franchise2->id,
            'franchisee_id' => $franchisee2->id,
        ]);

        // Create staff for unit2
        $staff = Staff::factory()->withoutSkillsAndCertifications()->create(['status' => 'active']);
        $unit2->staff()->attach($staff->id, ['assigned_date' => now()]);

        // Franchisor1 tries to access unit2's staff
        $response = $this->actingAs($franchisor1, 'sanctum')
            ->getJson("/api/v1/franchisor/units/{$unit2->id}/staff");

        // Assert access denied
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ]);
    }

    /**
     * Test unauthenticated user cannot access staff endpoint
     */
    public function test_unauthenticated_user_cannot_access_staff(): void
    {
        $response = $this->getJson('/api/v1/franchisor/units/1/staff');

        $response->assertStatus(401);
    }

    /**
     * Test non-franchisor cannot access staff endpoint
     */
    public function test_non_franchisor_cannot_access_staff(): void
    {
        $franchisee = User::factory()->create(['role' => 'franchisee']);

        $response = $this->actingAs($franchisee, 'sanctum')
            ->getJson('/api/v1/franchisor/units/1/staff');

        $response->assertStatus(403);
    }
}
