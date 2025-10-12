<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\TechnicalRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TechnicalRequestApiTest extends TestCase
{
    // use RefreshDatabase;

    private User $user;

    private Franchise $franchise;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        $this->user = User::factory()->create([
            'role' => 'franchisor',
        ]);

        // Create a franchise
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->user->id,
        ]);

        // Create a unit
        $this->unit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
        ]);

        // Authenticate the user
        Sanctum::actingAs($this->user);
    }

    public function test_can_get_technical_requests_list(): void
    {
        // Create test technical requests
        TechnicalRequest::factory()->count(5)->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/technical-requests');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'ticket_number',
                            'title',
                            'description',
                            'category',
                            'priority',
                            'status',
                            'requester_id',
                            'created_at',
                        ],
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'message',
            ]);
    }

    public function test_can_filter_technical_requests_by_status(): void
    {
        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'status' => 'open',
        ]);

        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'status' => 'resolved',
        ]);

        $response = $this->getJson('/api/v1/technical-requests?status=open');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
        $this->assertEquals('open', $response->json('data.data.0.status'));
    }

    public function test_can_create_technical_request(): void
    {
        $data = [
            'title' => 'Test Bug Report',
            'description' => 'This is a test bug description',
            'category' => 'software',
            'priority' => 'high',
            'status' => 'open',
            'requester_id' => $this->user->id,
            'franchise_id' => $this->franchise->id,
            'affected_system' => 'Dashboard',
        ];

        $response = $this->postJson('/api/v1/technical-requests', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('technical_requests', [
            'title' => 'Test Bug Report',
            'category' => 'software',
            'priority' => 'high',
        ]);
    }

    public function test_validates_technical_request_creation(): void
    {
        $response = $this->postJson('/api/v1/technical-requests', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'category', 'priority', 'status', 'requester_id']);
    }

    public function test_can_get_single_technical_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->getJson("/api/v1/technical-requests/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'ticket_number',
                    'title',
                    'description',
                ],
            ]);
    }

    public function test_can_update_technical_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'title' => 'Original Title',
        ]);

        $response = $this->patchJson("/api/v1/technical-requests/{$request->id}", [
            'title' => 'Updated Title',
            'priority' => 'urgent',
        ]);

        $response->assertStatus(200);

        $request->refresh();
        $this->assertEquals('Updated Title', $request->title);
        $this->assertEquals('urgent', $request->priority);
    }

    public function test_can_delete_technical_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/v1/technical-requests/{$request->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('technical_requests', ['id' => $request->id]);
    }

    public function test_can_assign_technical_request(): void
    {
        $assignee = User::factory()->create();
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'assigned_to' => null,
        ]);

        $response = $this->patchJson("/api/v1/technical-requests/{$request->id}/assign", [
            'assigned_to' => $assignee->id,
        ]);

        $response->assertStatus(200);

        $request->refresh();
        $this->assertEquals($assignee->id, $request->assigned_to);
        $this->assertNotNull($request->first_response_at);
    }

    public function test_can_respond_to_technical_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'first_response_at' => null,
        ]);

        $response = $this->postJson("/api/v1/technical-requests/{$request->id}/respond", [
            'response_notes' => 'We are looking into this issue',
        ]);

        $response->assertStatus(200);

        $request->refresh();
        $this->assertNotNull($request->first_response_at);
        $this->assertEquals('in_progress', $request->status);
    }

    public function test_can_resolve_technical_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'status' => 'in_progress',
        ]);

        $response = $this->patchJson("/api/v1/technical-requests/{$request->id}/resolve", [
            'resolution_notes' => 'Issue has been fixed',
        ]);

        $response->assertStatus(200);

        $request->refresh();
        $this->assertEquals('resolved', $request->status);
        $this->assertNotNull($request->resolved_at);
        $this->assertEquals('Issue has been fixed', $request->resolution_notes);
    }

    public function test_can_close_technical_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'status' => 'resolved',
        ]);

        $response = $this->patchJson("/api/v1/technical-requests/{$request->id}/close", [
            'satisfaction_rating' => 5,
            'satisfaction_feedback' => 'Great support!',
        ]);

        $response->assertStatus(200);

        $request->refresh();
        $this->assertEquals('closed', $request->status);
        $this->assertNotNull($request->closed_at);
        $this->assertEquals(5, $request->satisfaction_rating);
    }

    public function test_can_escalate_technical_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'is_escalated' => false,
            'priority' => 'medium',
        ]);

        $response = $this->patchJson("/api/v1/technical-requests/{$request->id}/escalate");

        $response->assertStatus(200);

        $request->refresh();
        $this->assertTrue($request->is_escalated);
        $this->assertNotNull($request->escalated_at);
        $this->assertEquals('high', $request->priority);
    }

    public function test_can_get_technical_request_statistics(): void
    {
        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'status' => 'open',
        ]);

        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'status' => 'resolved',
        ]);

        $response = $this->getJson('/api/v1/technical-requests/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_requests',
                    'open_requests',
                    'resolved_requests',
                    'overdue_requests',
                ],
            ]);
    }

    public function test_can_search_technical_requests(): void
    {
        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'title' => 'Login Bug',
            'ticket_number' => 'TR202410010001',
        ]);

        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'title' => 'Dashboard Issue',
        ]);

        $response = $this->getJson('/api/v1/technical-requests?search=Login');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
        $this->assertStringContainsString('Login', $response->json('data.data.0.title'));
    }

    public function test_can_filter_by_priority(): void
    {
        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'priority' => 'urgent',
        ]);

        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'priority' => 'low',
        ]);

        $response = $this->getJson('/api/v1/technical-requests?priority=urgent');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
        $this->assertEquals('urgent', $response->json('data.data.0.priority'));
    }

    public function test_can_filter_by_category(): void
    {
        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'category' => 'software',
        ]);

        TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
            'category' => 'hardware',
        ]);

        $response = $this->getJson('/api/v1/technical-requests?category=software');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
        $this->assertEquals('software', $response->json('data.data.0.category'));
    }

    public function test_can_bulk_delete_technical_requests(): void
    {
        $request1 = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
        ]);

        $request2 = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
        ]);

        $request3 = TechnicalRequest::factory()->create([
            'franchise_id' => $this->franchise->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->postJson('/api/v1/technical-requests/bulk-delete', [
            'ids' => [$request1->id, $request2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 2,
            ]);

        $this->assertDatabaseMissing('technical_requests', ['id' => $request1->id]);
        $this->assertDatabaseMissing('technical_requests', ['id' => $request2->id]);
        $this->assertDatabaseHas('technical_requests', ['id' => $request3->id]);
    }

    public function test_bulk_delete_validates_ids(): void
    {
        $response = $this->postJson('/api/v1/technical-requests/bulk-delete', [
            'ids' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ids']);
    }
}
