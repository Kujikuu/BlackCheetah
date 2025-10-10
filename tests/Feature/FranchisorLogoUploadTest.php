<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FranchisorLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_franchisor_can_upload_logo(): void
    {
        // Create a franchisor user
        $user = User::factory()->create([
            'role' => 'franchisor',
        ]);

        // Create a franchise for the user
        $franchise = Franchise::factory()->create([
            'franchisor_id' => $user->id,
        ]);

        // Create a fake image file
        $file = UploadedFile::fake()->image('logo.jpg', 800, 600);

        // Make the API request
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/franchisor/franchise/upload-logo', [
                'logo' => $file,
            ]);

        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logo uploaded successfully',
            ])
            ->assertJsonStructure([
                'data' => ['logo_url'],
            ]);

        // Assert the file was stored
        Storage::disk('public')->assertExists('franchise-logos/'.$file->hashName());

        // Assert the franchise was updated with the logo URL
        $franchise->refresh();
        $this->assertNotNull($franchise->logo);
        $this->assertStringContainsString('franchise-logos', $franchise->logo);
    }

    public function test_logo_upload_requires_authentication(): void
    {
        $file = UploadedFile::fake()->image('logo.jpg');

        $response = $this->postJson('/api/v1/franchisor/franchise/upload-logo', [
            'logo' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_logo_upload_requires_franchisor_role(): void
    {
        $user = User::factory()->create([
            'role' => 'franchisee',
        ]);

        // Create a franchise for the franchisee user to test role validation
        Franchise::factory()->create([
            'franchisor_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->image('logo.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/franchisor/franchise/upload-logo', [
                'logo' => $file,
            ]);

        $response->assertStatus(403);
    }

    public function test_logo_upload_requires_existing_franchise(): void
    {
        $user = User::factory()->create([
            'role' => 'franchisor',
        ]);

        $file = UploadedFile::fake()->image('logo.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/franchisor/franchise/upload-logo', [
                'logo' => $file,
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No franchise found for this user',
            ]);
    }

    public function test_logo_upload_validates_file_type(): void
    {
        $user = User::factory()->create([
            'role' => 'franchisor',
        ]);

        Franchise::factory()->create([
            'franchisor_id' => $user->id,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/franchisor/franchise/upload-logo', [
                'logo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['logo']);
    }

    public function test_logo_upload_validates_file_size(): void
    {
        $user = User::factory()->create([
            'role' => 'franchisor',
        ]);

        Franchise::factory()->create([
            'franchisor_id' => $user->id,
        ]);

        // Create a file larger than 2MB
        $file = UploadedFile::fake()->image('logo.jpg')->size(3000);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/franchisor/franchise/upload-logo', [
                'logo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['logo']);
    }

    public function test_logo_upload_requires_logo_file(): void
    {
        $user = User::factory()->create([
            'role' => 'franchisor',
        ]);

        Franchise::factory()->create([
            'franchisor_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/franchisor/franchise/upload-logo', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['logo']);
    }
}
