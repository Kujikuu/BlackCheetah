<?php

namespace Tests\Unit;

use App\Models\Franchise;
use App\Models\TechnicalRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalRequestTest extends TestCase
{
    // use RefreshDatabase;

    public function test_generates_unique_ticket_number_on_creation(): void
    {
        $user = User::factory()->create();
        $franchise = Franchise::factory()->create();

        $request = TechnicalRequest::factory()->create([
            'requester_id' => $user->id,
            'franchise_id' => $franchise->id,
        ]);

        $this->assertNotNull($request->ticket_number);
        $this->assertStringStartsWith('TR', $request->ticket_number);
    }

    public function test_can_check_if_request_is_open(): void
    {
        $request = TechnicalRequest::factory()->create(['status' => 'open']);
        $this->assertTrue($request->isOpen());

        $request2 = TechnicalRequest::factory()->create(['status' => 'closed']);
        $this->assertFalse($request2->isOpen());
    }

    public function test_can_check_if_request_is_resolved(): void
    {
        $request = TechnicalRequest::factory()->create(['status' => 'resolved']);
        $this->assertTrue($request->isResolved());

        $request2 = TechnicalRequest::factory()->create(['status' => 'open']);
        $this->assertFalse($request2->isResolved());
    }

    public function test_can_respond_to_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'status' => 'open',
            'first_response_at' => null,
        ]);

        $request->respond();

        $this->assertNotNull($request->first_response_at);
        $this->assertEquals('in_progress', $request->status);
        $this->assertNotNull($request->response_time_hours);
    }

    public function test_can_resolve_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'status' => 'in_progress',
        ]);

        $request->resolve('Issue has been fixed');

        $this->assertEquals('resolved', $request->status);
        $this->assertNotNull($request->resolved_at);
        $this->assertEquals('Issue has been fixed', $request->resolution_notes);
        $this->assertNotNull($request->resolution_time_hours);
    }

    public function test_can_close_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'status' => 'resolved',
        ]);

        $request->close(5, 'Excellent support');

        $this->assertEquals('closed', $request->status);
        $this->assertNotNull($request->closed_at);
        $this->assertEquals(5, $request->satisfaction_rating);
        $this->assertEquals('Excellent support', $request->satisfaction_feedback);
    }

    public function test_can_escalate_request(): void
    {
        $request = TechnicalRequest::factory()->create([
            'is_escalated' => false,
            'priority' => 'medium',
        ]);

        $request->escalate();

        $this->assertTrue($request->is_escalated);
        $this->assertNotNull($request->escalated_at);
        $this->assertEquals('high', $request->priority);
    }

    public function test_can_add_attachment(): void
    {
        $request = TechnicalRequest::factory()->create([
            'attachments' => [],
        ]);

        $request->addAttachment('path/to/file.pdf');

        $this->assertCount(1, $request->attachments);
        $this->assertEquals('path/to/file.pdf', $request->attachments[0]);
    }

    public function test_can_assign_to_user(): void
    {
        $user = User::factory()->create();
        $request = TechnicalRequest::factory()->create([
            'assigned_to' => null,
            'first_response_at' => null,
        ]);

        $request->assignTo($user->id);

        $this->assertEquals($user->id, $request->assigned_to);
        $this->assertNotNull($request->first_response_at);
    }

    public function test_calculates_age_in_hours(): void
    {
        $request = TechnicalRequest::factory()->create([
            'created_at' => now()->subHours(5),
        ]);

        $this->assertGreaterThanOrEqual(4, $request->age_in_hours);
        $this->assertLessThanOrEqual(6, $request->age_in_hours);
    }

    public function test_has_requester_relationship(): void
    {
        $user = User::factory()->create();
        $request = TechnicalRequest::factory()->create(['requester_id' => $user->id]);

        $this->assertInstanceOf(User::class, $request->requester);
        $this->assertEquals($user->id, $request->requester->id);
    }

    public function test_has_assigned_user_relationship(): void
    {
        $user = User::factory()->create();
        $request = TechnicalRequest::factory()->create(['assigned_to' => $user->id]);

        $this->assertInstanceOf(User::class, $request->assignedUser);
        $this->assertEquals($user->id, $request->assignedUser->id);
    }

    public function test_has_franchise_relationship(): void
    {
        $franchise = Franchise::factory()->create();
        $request = TechnicalRequest::factory()->create(['franchise_id' => $franchise->id]);

        $this->assertInstanceOf(Franchise::class, $request->franchise);
        $this->assertEquals($franchise->id, $request->franchise->id);
    }

    public function test_has_unit_relationship(): void
    {
        $unit = Unit::factory()->create();
        $request = TechnicalRequest::factory()->create(['unit_id' => $unit->id]);

        $this->assertInstanceOf(Unit::class, $request->unit);
        $this->assertEquals($unit->id, $request->unit->id);
    }

    public function test_can_scope_by_status(): void
    {
        TechnicalRequest::factory()->create(['status' => 'open']);
        TechnicalRequest::factory()->create(['status' => 'resolved']);

        $openRequests = TechnicalRequest::byStatus('open')->get();

        $this->assertCount(1, $openRequests);
        $this->assertEquals('open', $openRequests->first()->status);
    }

    public function test_can_scope_open_requests(): void
    {
        TechnicalRequest::factory()->create(['status' => 'open']);
        TechnicalRequest::factory()->create(['status' => 'in_progress']);
        TechnicalRequest::factory()->create(['status' => 'closed']);

        $openRequests = TechnicalRequest::open()->get();

        $this->assertCount(2, $openRequests);
    }

    public function test_can_scope_resolved_requests(): void
    {
        TechnicalRequest::factory()->create(['status' => 'resolved']);
        TechnicalRequest::factory()->create(['status' => 'closed']);
        TechnicalRequest::factory()->create(['status' => 'open']);

        $resolvedRequests = TechnicalRequest::resolved()->get();

        $this->assertCount(2, $resolvedRequests);
    }

    public function test_can_scope_escalated_requests(): void
    {
        TechnicalRequest::factory()->create(['is_escalated' => true]);
        TechnicalRequest::factory()->create(['is_escalated' => false]);

        $escalatedRequests = TechnicalRequest::escalated()->get();

        $this->assertCount(1, $escalatedRequests);
    }

    public function test_can_scope_high_priority_requests(): void
    {
        TechnicalRequest::factory()->create(['priority' => 'urgent']);
        TechnicalRequest::factory()->create(['priority' => 'high']);
        TechnicalRequest::factory()->create(['priority' => 'low']);

        $highPriorityRequests = TechnicalRequest::highPriority()->get();

        $this->assertCount(2, $highPriorityRequests);
    }
}
