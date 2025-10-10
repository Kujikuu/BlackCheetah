<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Note;
use App\Models\User;
use App\Models\Franchise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NoteApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;
    private Lead $lead;
    private Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'role' => 'franchisor',
            'status' => 'active',
        ]);

        // Create test franchise
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->user->id,
        ]);

        // Create test lead
        $this->lead = Lead::factory()->create([
            'franchise_id' => $this->franchise->id,
            'assigned_to' => $this->user->id,
        ]);

        // Authenticate user
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_can_create_note(): void
    {
        $noteData = [
            'lead_id' => $this->lead->id,
            'title' => 'Test Note',
            'description' => 'This is a test note description.',
        ];

        $response = $this->postJson('/api/v1/notes', $noteData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Note created successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'lead_id',
                    'user_id',
                    'title',
                    'description',
                    'attachments',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('notes', [
            'lead_id' => $this->lead->id,
            'user_id' => $this->user->id,
            'title' => 'Test Note',
            'description' => 'This is a test note description.',
        ]);
    }

    public function test_can_list_notes_for_lead(): void
    {
        // Create multiple notes for the lead
        Note::factory()->count(3)->create([
            'lead_id' => $this->lead->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/notes?lead_id=' . $this->lead->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'lead_id',
                        'user_id',
                        'title',
                        'description',
                        'attachments',
                        'created_at',
                        'updated_at',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ],
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_can_show_specific_note(): void
    {
        $note = Note::factory()->create([
            'lead_id' => $this->lead->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/notes/' . $note->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $note->id,
                    'lead_id' => $this->lead->id,
                    'user_id' => $this->user->id,
                ],
            ]);
    }

    public function test_can_update_note(): void
    {
        $note = Note::factory()->create([
            'lead_id' => $this->lead->id,
            'user_id' => $this->user->id,
        ]);

        $updateData = [
            'title' => 'Updated Note Title',
            'description' => 'Updated note description.',
        ];

        $response = $this->putJson('/api/v1/notes/' . $note->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note updated successfully',
                'data' => [
                    'id' => $note->id,
                    'title' => 'Updated Note Title',
                    'description' => 'Updated note description.',
                ],
            ]);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'title' => 'Updated Note Title',
            'description' => 'Updated note description.',
        ]);
    }

    public function test_can_delete_note(): void
    {
        $note = Note::factory()->create([
            'lead_id' => $this->lead->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->deleteJson('/api/v1/notes/' . $note->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note deleted successfully',
            ]);

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_cannot_update_note_created_by_another_user(): void
    {
        $otherUser = User::factory()->create(['role' => 'sales']);
        $note = Note::factory()->create([
            'lead_id' => $this->lead->id,
            'user_id' => $otherUser->id,
        ]);

        $updateData = [
            'title' => 'Updated Note Title',
            'description' => 'Updated note description.',
        ];

        $response = $this->putJson('/api/v1/notes/' . $note->id, $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to edit this note',
            ]);
    }

    public function test_cannot_delete_note_created_by_another_user(): void
    {
        $otherUser = User::factory()->create(['role' => 'sales']);
        $note = Note::factory()->create([
            'lead_id' => $this->lead->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->deleteJson('/api/v1/notes/' . $note->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to delete this note',
            ]);
    }

    public function test_requires_authentication(): void
    {
        // Remove authentication for this test
        $this->app['auth']->forgetGuards();
        
        // Make request without authentication
        $response = $this->getJson('/api/v1/notes?lead_id=' . $this->lead->id);
        $response->assertStatus(401);
    }

    public function test_validates_required_fields_for_creation(): void
    {
        $response = $this->postJson('/api/v1/notes', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lead_id', 'title', 'description']);
    }

    public function test_validates_lead_exists_for_creation(): void
    {
        $response = $this->postJson('/api/v1/notes', [
            'lead_id' => 99999, // Non-existent lead
            'title' => 'Test Note',
            'description' => 'Test description',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lead_id']);
    }
}
