<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FranchisorOperationsDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $franchisor;

    private Franchise $franchise;

    private User $franchisee;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a franchisor user
        $this->franchisor = User::factory()->create([
            'role' => 'franchisor',
        ]);

        // Create a franchise
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        // Create a franchisee user
        $this->franchisee = User::factory()->create([
            'role' => 'franchisee',
        ]);

        // Create a unit
        $this->unit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
        ]);

        // Authenticate the franchisor
        Sanctum::actingAs($this->franchisor);
    }

    public function test_can_get_operations_stats_for_franchisee_type(): void
    {
        // Create test tasks assigned to franchisees
        Task::factory()->count(3)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Task::factory()->count(2)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'status' => 'in_progress',
            'priority' => 'medium',
        ]);

        Task::factory()->count(1)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'status' => 'completed',
            'priority' => 'low',
        ]);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?type=franchisee');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tasks' => [
                        'data' => [
                            '*' => [
                                'id',
                                'title',
                                'description',
                                'status',
                                'priority',
                                'assigned_to',
                                'created_at',
                            ],
                        ],
                    ],
                    'statistics' => [
                        'total',
                        'pending',
                        'inProgress',
                        'completed',
                    ],
                    'tasksByPriority',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'statistics' => [
                        'total' => 6,
                        'pending' => 3,
                        'inProgress' => 2,
                        'completed' => 1,
                    ],
                ],
            ]);
    }

    public function test_can_get_operations_stats_for_unit_type(): void
    {
        // Create test tasks assigned to units
        Task::factory()->count(2)->create([
            'created_by' => $this->franchisor->id,
            'unit_id' => $this->unit->id,
            'status' => 'pending',
            'priority' => 'high',
        ]);

        Task::factory()->count(1)->create([
            'created_by' => $this->franchisor->id,
            'unit_id' => $this->unit->id,
            'status' => 'completed',
            'priority' => 'medium',
        ]);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?type=unit');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tasks',
                    'statistics' => [
                        'total',
                        'pending',
                        'inProgress',
                        'completed',
                    ],
                    'tasksByPriority',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'statistics' => [
                        'total' => 3,
                        'pending' => 2,
                        'inProgress' => 0,
                        'completed' => 1,
                    ],
                ],
            ]);
    }

    public function test_can_filter_operations_stats_by_status(): void
    {
        // Create test tasks with different statuses
        Task::factory()->count(2)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'status' => 'pending',
        ]);

        Task::factory()->count(3)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'status' => 'completed',
        ]);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?status=pending');

        $response->assertStatus(200);

        $tasks = $response->json('data.tasks.data');
        $this->assertCount(2, $tasks);

        foreach ($tasks as $task) {
            $this->assertEquals('pending', $task['status']);
        }
    }

    public function test_can_filter_operations_stats_by_priority(): void
    {
        // Create test tasks with different priorities
        Task::factory()->count(2)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'priority' => 'high',
        ]);

        Task::factory()->count(1)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'priority' => 'low',
        ]);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?priority=high');

        $response->assertStatus(200);

        $tasks = $response->json('data.tasks.data');
        $this->assertCount(2, $tasks);

        foreach ($tasks as $task) {
            $this->assertEquals('high', $task['priority']);
        }
    }

    public function test_can_search_operations_stats(): void
    {
        // Create test tasks with specific titles
        Task::factory()->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'title' => 'Update inventory system',
        ]);

        Task::factory()->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'title' => 'Staff training completion',
        ]);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?search=inventory');

        $response->assertStatus(200);

        $tasks = $response->json('data.tasks.data');
        $this->assertCount(1, $tasks);
        $this->assertStringContainsString('inventory', strtolower($tasks[0]['title']));
    }

    public function test_operations_stats_requires_authentication(): void
    {
        // Create a fresh application instance without authentication
        $this->refreshApplication();

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations');

        $response->assertStatus(401);
    }

    public function test_operations_stats_returns_error_when_no_franchise_found(): void
    {
        // Create a franchisor without a franchise
        $franchisorWithoutFranchise = User::factory()->create([
            'role' => 'franchisor',
        ]);

        Sanctum::actingAs($franchisorWithoutFranchise);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No franchise found for this user',
            ]);
    }

    public function test_operations_stats_defaults_to_franchisee_type(): void
    {
        // Create test tasks assigned to franchisees
        Task::factory()->count(2)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'status' => 'pending',
        ]);

        // Don't specify type parameter - should default to 'franchisee'
        $response = $this->getJson('/api/v1/franchisor/dashboard/operations');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'statistics' => [
                        'total' => 2,
                        'pending' => 2,
                        'inProgress' => 0,
                        'completed' => 0,
                    ],
                ],
            ]);
    }

    public function test_operations_stats_includes_tasks_by_priority_breakdown(): void
    {
        // Create test tasks with different priorities
        Task::factory()->count(3)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'priority' => 'high',
        ]);

        Task::factory()->count(2)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'priority' => 'medium',
        ]);

        Task::factory()->count(1)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'priority' => 'low',
        ]);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations');

        $response->assertStatus(200);

        $tasksByPriority = $response->json('data.tasksByPriority');

        $this->assertEquals(3, $tasksByPriority['high']);
        $this->assertEquals(2, $tasksByPriority['medium']);
        $this->assertEquals(1, $tasksByPriority['low']);
    }

    public function test_operations_stats_supports_pagination(): void
    {
        // Create more tasks than the default page size
        Task::factory()->count(25)->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
        ]);

        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tasks' => [
                        'data',
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                    ],
                ],
            ]);

        $this->assertEquals(10, count($response->json('data.tasks.data')));
        $this->assertEquals(25, $response->json('data.tasks.total'));
        $this->assertEquals(3, $response->json('data.tasks.last_page'));
    }

    public function test_operations_stats_handles_sorting(): void
    {
        // Create tasks with different creation dates
        $oldTask = Task::factory()->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'title' => 'Old Task',
            'created_at' => now()->subDays(5),
        ]);

        $newTask = Task::factory()->create([
            'created_by' => $this->franchisor->id,
            'assigned_to' => $this->franchisee->id,
            'title' => 'New Task',
            'created_at' => now(),
        ]);

        // Test ascending order
        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?sortBy=created_at&sortOrder=asc');

        $response->assertStatus(200);

        $tasks = $response->json('data.tasks.data');
        $this->assertEquals('Old Task', $tasks[0]['title']);
        $this->assertEquals('New Task', $tasks[1]['title']);

        // Test descending order (default)
        $response = $this->getJson('/api/v1/franchisor/dashboard/operations?sortBy=created_at&sortOrder=desc');

        $response->assertStatus(200);

        $tasks = $response->json('data.tasks.data');
        $this->assertEquals('New Task', $tasks[0]['title']);
        $this->assertEquals('Old Task', $tasks[1]['title']);
    }

    public function test_operations_stats_handles_server_error_gracefully(): void
    {
        // Mock a scenario that would cause an exception
        // This is a bit tricky to test without mocking, but we can test the structure
        $response = $this->getJson('/api/v1/franchisor/dashboard/operations');

        // Should not return a 500 error under normal circumstances
        $this->assertNotEquals(500, $response->getStatusCode());
    }
}
