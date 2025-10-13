<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyTasksTest extends TestCase
{
    use RefreshDatabase;

    private User $franchisee;

    private Unit $unit;

    private Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        // Create franchisor
        $franchisor = User::factory()->create([
            'role' => 'franchisor',
            'email' => 'franchisor@test.com',
        ]);

        // Create franchise
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $franchisor->id,
        ]);

        // Create franchisee
        $this->franchisee = User::factory()->create([
            'role' => 'franchisee',
            'email' => 'franchisee@test.com',
        ]);

        // Create unit
        $this->unit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
            'franchisee_id' => $this->franchisee->id,
        ]);
    }

    public function test_franchisee_can_get_their_tasks(): void
    {
        // Create tasks for the unit
        $task1 = Task::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'created_by' => $this->franchisee->id,
            'title' => 'Unit Task 1',
            'status' => 'pending',
        ]);

        $task2 = Task::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->franchisee->id,
            'created_by' => $this->franchisee->id,
            'title' => 'Unit Task 2',
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->getJson('/api/v1/unit-manager/my-tasks');

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
                        'unitName',
                        'startDate',
                        'dueDate',
                        'priority',
                        'status',
                    ],
                ],
                'message',
            ])
            ->assertJsonFragment(['title' => 'Unit Task 1'])
            ->assertJsonFragment(['title' => 'Unit Task 2']);
    }

    public function test_franchisee_can_update_task_status(): void
    {
        $task = Task::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'created_by' => $this->franchisee->id,
            'title' => 'Test Task',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->patchJson("/api/v1/unit-manager/my-tasks/{$task->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'in_progress']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_franchisee_cannot_update_other_users_tasks(): void
    {
        $otherFranchisee = User::factory()->create([
            'role' => 'franchisee',
        ]);

        $otherUnit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
            'franchisee_id' => $otherFranchisee->id,
        ]);

        $task = Task::factory()->create([
            'unit_id' => $otherUnit->id,
            'franchise_id' => $this->franchise->id,
            'created_by' => $otherFranchisee->id,
            'title' => 'Other Task',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->patchJson("/api/v1/unit-manager/my-tasks/{$task->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(404);
    }

    public function test_status_validation_works(): void
    {
        $task = Task::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'created_by' => $this->franchisee->id,
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->patchJson("/api/v1/unit-manager/my-tasks/{$task->id}/status", [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_franchisee_without_unit_gets_404(): void
    {
        $franchiseeWithoutUnit = User::factory()->create([
            'role' => 'franchisee',
        ]);

        $response = $this->actingAs($franchiseeWithoutUnit, 'sanctum')
            ->getJson('/api/v1/unit-manager/my-tasks');

        $response->assertStatus(404)
            ->assertJsonFragment(['message' => 'No unit found for current user']);
    }
}
