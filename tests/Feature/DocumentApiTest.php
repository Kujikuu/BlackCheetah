<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Franchise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->franchise = Franchise::factory()->create(['franchisor_id' => $this->user->id]);

        Storage::fake('public');
    }

    public function test_can_list_documents(): void
    {
        Document::factory()->count(3)->create(['franchise_id' => $this->franchise->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/franchises/{$this->franchise->id}/documents");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'type',
                            'file_name',
                            'file_size',
                            'mime_type',
                            'status',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_can_create_document(): void
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/franchises/{$this->franchise->id}/documents", [
                'name' => 'Test Document',
                'description' => 'A test document',
                'type' => 'contract',
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'type',
                    'file_name',
                    'file_size',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('documents', [
            'name' => 'Test Document',
            'franchise_id' => $this->franchise->id,
        ]);
    }

    public function test_can_download_document(): void
    {
        $document = Document::factory()->create([
            'franchise_id' => $this->franchise->id,
            'file_path' => 'documents/test.pdf',
            'file_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
        ]);

        Storage::disk('local')->put('documents/test.pdf', 'fake content');

        $response = $this->actingAs($this->user)
            ->get("/api/v1/franchises/{$this->franchise->id}/documents/{$document->id}/download");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename=test.pdf');
    }

    public function test_can_delete_document(): void
    {
        $document = Document::factory()->create([
            'franchise_id' => $this->franchise->id,
            'file_path' => 'documents/test.pdf',
        ]);

        Storage::disk('local')->put('documents/test.pdf', 'fake content');

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/franchises/{$this->franchise->id}/documents/{$document->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson("/api/v1/franchises/{$this->franchise->id}/documents");

        $response->assertStatus(401);
    }

    public function test_validates_required_fields_for_creation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/franchises/{$this->franchise->id}/documents", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'file']);
    }
}
