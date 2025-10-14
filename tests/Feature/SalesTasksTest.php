<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesTasksTest extends TestCase
{
    use RefreshDatabase;

    private User $salesUser;

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

        // Create sales user
        $this->salesUser = User::factory()->create([
            'role' => 'sales',
            'email' => 'sales@test.com',
            'franchise_id' => $this->franchise->id,
        ]);
    }

    public function test_sales_user_can_get_their_tasks(): void
    {
        // Create lead for sales tasks
        $lead = Lead::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
        ]);

        // Create sales tasks
        $task1 = Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'created_by' => $this->salesUser->id,
            'lead_id' => $lead->id,
            'type' => 'lead_management',
            'title' => 'Follow up with lead',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $task2 = Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'created_by' => $this->salesUser->id,
            'type' => 'sales',
            'title' => 'Prepare presentation',
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->getJson('/api/v1/sales/tasks');

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
                        'leadId',
                        'leadName',
                    ],
                ],
                'pagination' => [
                    'total',
                    'perPage',
                    'currentPage',
                    'lastPage',
                ],
                'message',
            ])
            ->assertJsonFragment(['title' => 'Follow up with lead'])
            ->assertJsonFragment(['title' => 'Prepare presentation']);
    }

    public function test_sales_user_can_get_statistics(): void
    {
        // Create tasks with different statuses
        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'lead_management',
            'status' => 'completed',
        ]);

        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'sales',
            'status' => 'in_progress',
        ]);

        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'market_research',
            'status' => 'pending',
            'due_date' => now()->subDay(),
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->getJson('/api/v1/sales/tasks/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'totalTasks',
                    'completedTasks',
                    'inProgressTasks',
                    'dueTasks',
                ],
                'message',
            ])
            ->assertJson([
                'data' => [
                    'totalTasks' => 3,
                    'completedTasks' => 1,
                    'inProgressTasks' => 1,
                    'dueTasks' => 1,
                ],
            ]);
    }

    public function test_sales_user_can_update_task_status(): void
    {
        $task = Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'lead_management',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->patchJson("/api/v1/sales/tasks/{$task->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'in_progress']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_sales_user_cannot_update_other_users_task(): void
    {
        $otherUser = User::factory()->create([
            'role' => 'sales',
            'franchise_id' => $this->franchise->id,
        ]);

        $task = Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $otherUser->id,
            'type' => 'lead_management',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->patchJson("/api/v1/sales/tasks/{$task->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(403);
    }

    public function test_sales_tasks_can_be_filtered_by_status(): void
    {
        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'lead_management',
            'status' => 'completed',
            'title' => 'Completed Task',
        ]);

        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'sales',
            'status' => 'pending',
            'title' => 'Pending Task',
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->getJson('/api/v1/sales/tasks?status=completed');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Completed Task'])
            ->assertJsonMissing(['title' => 'Pending Task']);
    }

    public function test_sales_tasks_can_be_filtered_by_priority(): void
    {
        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'lead_management',
            'priority' => 'high',
            'title' => 'High Priority Task',
        ]);

        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'sales',
            'priority' => 'low',
            'title' => 'Low Priority Task',
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->getJson('/api/v1/sales/tasks?priority=high');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'High Priority Task'])
            ->assertJsonMissing(['title' => 'Low Priority Task']);
    }

    public function test_sales_tasks_can_be_filtered_by_category(): void
    {
        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'lead_management',
            'title' => 'Lead Task',
        ]);

        Task::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->salesUser->id,
            'type' => 'sales',
            'title' => 'Sales Task',
        ]);

        $response = $this->actingAs($this->salesUser, 'sanctum')
            ->getJson('/api/v1/sales/tasks?category=Lead Management');

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Lead Task'])
            ->assertJsonMissing(['title' => 'Sales Task']);
    }
}
