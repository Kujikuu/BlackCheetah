<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Staff;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnitOperationsApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $franchisee;

    private Unit $unit;

    private Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        // Create franchise
        $this->franchise = Franchise::factory()->create();

        // Create franchisee user
        $this->franchisee = User::factory()->create([
            'role' => 'franchisee',
            'status' => 'active',
            'franchise_id' => $this->franchise->id,
        ]);

        // Create unit for the franchisee
        $this->unit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
            'franchisee_id' => $this->franchisee->id,
            'status' => 'active',
        ]);
    }

    public function test_get_unit_details_returns_correct_structure(): void
    {
        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/units/details');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'branchName',
                    'franchiseeName',
                    'email',
                    'contactNumber',
                    'address',
                    'city',
                    'state',
                    'country',
                    'royaltyPercentage',
                    'contractStartDate',
                    'renewalDate',
                    'status',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->unit->id,
                    'branchName' => $this->unit->unit_name,
                    'franchiseeName' => $this->franchisee->name,
                    'email' => $this->franchisee->email,
                    'status' => $this->unit->status,
                ],
            ]);
    }

    public function test_get_unit_details_with_specific_unit_id(): void
    {
        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson("/api/v1/unit-manager/units/details/{$this->unit->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->unit->id,
                    'branchName' => $this->unit->unit_name,
                ],
            ]);
    }

    public function test_get_unit_details_unauthorized_access(): void
    {
        // Create another franchisee
        $otherFranchisee = User::factory()->create([
            'role' => 'franchisee',
            'status' => 'active',
        ]);

        Sanctum::actingAs($otherFranchisee);

        $response = $this->getJson("/api/v1/unit-manager/units/details/{$this->unit->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ]);
    }

    public function test_get_unit_tasks_returns_correct_structure(): void
    {
        // Create some tasks for the unit
        Task::factory(3)->create([
            'unit_id' => $this->unit->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/units/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'category',
                        'assignedTo',
                        'startDate',
                        'dueDate',
                        'priority',
                        'status',
                    ],
                ],
                'message',
            ])
            ->assertJson(['success' => true]);

        // Verify we have at least the created tasks
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_get_unit_staff_returns_correct_structure(): void
    {
        // Create staff members and assign to unit
        $staff = Staff::factory(3)->create();
        foreach ($staff as $member) {
            $member->assignToUnit($this->unit->id, 'sales_associate');
        }

        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/units/staff');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'jobTitle',
                        'email',
                        'shiftTime',
                        'status',
                    ],
                ],
                'message',
            ])
            ->assertJson(['success' => true]);

        // Verify we have the created staff
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_get_unit_staff_with_empty_data_returns_defaults(): void
    {
        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/units/staff');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data'); // Should return 3 default staff members

        // Verify default staff structure
        $defaultStaff = $response->json('data');
        $this->assertEquals('Alice Johnson', $defaultStaff[0]['name']);
        $this->assertEquals('Store Manager', $defaultStaff[0]['jobTitle']);
    }

    public function test_get_unit_products_returns_correct_structure(): void
    {
        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/units/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'unitPrice',
                        'category',
                        'status',
                        'stock',
                    ],
                ],
                'message',
            ])
            ->assertJson(['success' => true]);
    }

    public function test_get_unit_reviews_returns_correct_structure(): void
    {
        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/units/reviews');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'customerName',
                        'customerEmail',
                        'rating',
                        'comment',
                        'date',
                        'sentiment',
                    ],
                ],
                'message',
            ])
            ->assertJson(['success' => true]);
    }

    public function test_get_unit_documents_returns_correct_structure(): void
    {
        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/units/documents');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'fileName',
                        'fileSize',
                        'uploadDate',
                        'type',
                        'status',
                        'comment',
                    ],
                ],
                'message',
            ])
            ->assertJson(['success' => true]);
    }

    public function test_unauthorized_user_cannot_access_endpoints(): void
    {
        // Test without authentication
        $endpoints = [
            '/api/v1/unit-manager/units/details',
            '/api/v1/unit-manager/units/tasks',
            '/api/v1/unit-manager/units/staff',
            '/api/v1/unit-manager/units/products',
            '/api/v1/unit-manager/units/reviews',
            '/api/v1/unit-manager/units/documents',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401);
        }
    }

    public function test_non_franchisee_cannot_access_endpoints(): void
    {
        // Create a user with lower role that shouldn't have access
        $salesUser = User::factory()->create([
            'role' => 'sales',
            'status' => 'active',
        ]);

        Sanctum::actingAs($salesUser);

        // Test the specific endpoint - the middleware should reject before hitting controller
        $response = $this->getJson('/api/v1/unit-manager/units/details');

        // Based on role hierarchy: sales(1) < franchisee(2), so should be forbidden
        $response->assertStatus(403);

        // Also test another endpoint to make sure it's consistent
        $response2 = $this->getJson('/api/v1/unit-manager/units/tasks');
        $response2->assertStatus(403);
    }

    public function test_staff_data_endpoint_returns_correct_statistics(): void
    {
        // Create staff and assign to unit
        $staff = Staff::factory(5)->create();
        foreach ($staff as $member) {
            $member->assignToUnit($this->unit->id, 'sales_associate');
        }

        Sanctum::actingAs($this->franchisee);

        $response = $this->getJson('/api/v1/unit-manager/dashboard/staff-data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'totalStaffs',
                    'newHires',
                    'monthlyAbsenteeismRate',
                    'topPerformers' => [
                        '*' => [
                            'id',
                            'name',
                            'performance',
                            'department',
                        ],
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'totalStaffs' => 5,
                ],
            ]);
    }
}
