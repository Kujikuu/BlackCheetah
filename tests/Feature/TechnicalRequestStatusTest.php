<?php

namespace Tests\Feature;

use App\Models\TechnicalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TechnicalRequestStatusTest extends TestCase
{
    // use RefreshDatabase;

    public function test_admin_can_update_technical_request_status_with_patch(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create a technical request
        $technicalRequest = TechnicalRequest::factory()->create([
            'status' => 'open',
        ]);

        // Authenticate as admin
        Sanctum::actingAs($admin);

        // Test PATCH method (correct method)
        $response = $this->patchJson("/api/v1/admin/technical-requests/{$technicalRequest->id}/status", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Technical request status updated successfully',
            ]);

        // Verify the status was updated in the database
        $this->assertDatabaseHas('technical_requests', [
            'id' => $technicalRequest->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_put_method_returns_405_method_not_allowed(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create a technical request
        $technicalRequest = TechnicalRequest::factory()->create([
            'status' => 'open',
        ]);

        // Authenticate as admin
        Sanctum::actingAs($admin);

        // Test PUT method (incorrect method - should fail)
        $response = $this->putJson("/api/v1/admin/technical-requests/{$technicalRequest->id}/status", [
            'status' => 'in_progress',
        ]);

        $response->assertStatus(405); // Method Not Allowed
    }
}
