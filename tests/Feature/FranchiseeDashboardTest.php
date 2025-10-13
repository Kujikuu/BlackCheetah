<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FranchiseeDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $franchisee;

    protected Unit $unit;

    protected Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a franchisee user
        $this->franchisee = User::factory()->create([
            'role' => 'franchisee',
        ]);

        // Create a franchise
        $this->franchise = Franchise::factory()->create();

        // Create a unit managed by the franchisee
        $this->unit = Unit::factory()->create([
            'franchisee_id' => $this->franchisee->id,
            'franchise_id' => $this->franchise->id,
        ]);
    }

    public function test_sales_statistics_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/unit-manager/dashboard/sales-statistics');
        $response->assertStatus(401);
    }

    public function test_sales_statistics_requires_franchisee_role(): void
    {
        $regularUser = User::factory()->create(['role' => 'customer']);
        $response = $this->actingAs($regularUser)
            ->getJson('/api/v1/unit-manager/dashboard/sales-statistics');
        $response->assertStatus(403);
    }

    public function test_sales_statistics_returns_data_for_franchisee(): void
    {
        // Create test revenue data with line items
        Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'sales',
            'amount' => 5000,
            'net_amount' => 4000,
            'period_year' => now()->year,
            'period_month' => now()->month,
            'status' => 'verified',
            'revenue_date' => now(),
            'line_items' => [
                ['item_name' => 'Product A', 'quantity' => 10, 'unit_price' => 100],
                ['item_name' => 'Product B', 'quantity' => 5, 'unit_price' => 200],
                ['item_name' => 'Product C', 'quantity' => 3, 'unit_price' => 150],
                ['item_name' => 'Product D', 'quantity' => 2, 'unit_price' => 300],
                ['item_name' => 'Product E', 'quantity' => 1, 'unit_price' => 500],
            ],
        ]);

        Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'sales',
            'amount' => 4000,
            'net_amount' => 3200,
            'period_year' => now()->subMonth()->year,
            'period_month' => now()->subMonth()->month,
            'status' => 'verified',
            'revenue_date' => now()->subMonth(),
        ]);

        $response = $this->actingAs($this->franchisee)
            ->getJson('/api/v1/unit-manager/dashboard/sales-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'totalSales',
                    'totalProfit',
                    'salesChange',
                    'profitChange',
                ],
            ]);

        $this->assertEquals(5000, $response->json('data.totalSales'));
        $this->assertEquals(4000, $response->json('data.totalProfit'));
    }

    public function test_sales_statistics_returns_error_when_no_unit_found(): void
    {
        // Create a franchisee without a unit
        $franchiseeWithoutUnit = User::factory()->create(['role' => 'franchisee']);

        $response = $this->actingAs($franchiseeWithoutUnit)
            ->getJson('/api/v1/unit-manager/dashboard/sales-statistics');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No unit found for current user',
            ]);
    }

    public function test_product_sales_returns_data_for_franchisee(): void
    {
        $response = $this->actingAs($this->franchisee)
            ->getJson('/api/v1/unit-manager/dashboard/product-sales');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'mostSelling',
                    'lowSelling',
                ],
            ]);
    }

    public function test_monthly_performance_returns_data_for_franchisee(): void
    {
        $response = $this->actingAs($this->franchisee)
            ->getJson('/api/v1/unit-manager/dashboard/monthly-performance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'topPerforming',
                    'lowPerforming',
                    'averagePerformance',
                    'categories',
                ],
                'message',
            ]);

        // Verify arrays have 12 months
        $this->assertCount(12, $response->json('data.topPerforming'));
        $this->assertCount(12, $response->json('data.lowPerforming'));
        $this->assertCount(12, $response->json('data.averagePerformance'));
        $this->assertCount(12, $response->json('data.categories'));
    }

    public function test_can_create_document_with_file_upload(): void
    {
        Storage::fake('local');

        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->franchisee)
            ->postJson("/api/v1/unit-manager/units/documents/{$this->unit->id}", [
                'title' => 'Test Document',
                'description' => 'This is a test document',
                'type' => 'Sales Report',
                'file' => $file,
                'status' => 'pending',
                'comment' => 'Test comment',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'type',
                    'fileName',
                    'fileSize',
                    'filePath',
                    'uploadDate',
                    'status',
                    'comment',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'title' => 'Test Document',
                    'description' => 'This is a test document',
                    'type' => 'Sales Report',
                    'status' => 'pending',
                ],
            ]);

        // Verify file was stored
        $documentData = $response->json('data');
        Storage::disk('local')->assertExists($documentData['filePath']);

        // Verify unit was updated with document
        $this->unit->refresh();
        $this->assertNotNull($this->unit->documents);
        $this->assertCount(1, $this->unit->documents);
        $this->assertEquals('Test Document', $this->unit->documents[0]['title']);
    }

    public function test_document_upload_requires_file(): void
    {
        $response = $this->actingAs($this->franchisee)
            ->postJson("/api/v1/unit-manager/units/documents/{$this->unit->id}", [
                'title' => 'Test Document',
                'description' => 'This is a test document',
                'type' => 'Sales Report',
                'status' => 'pending',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_can_download_document_with_stored_file(): void
    {
        Storage::fake('local');

        // Create a document with a file
        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $filePath = $file->storeAs('documents/units/' . $this->unit->id, 'test_document.pdf', 'local');

        // Add document to unit
        $this->unit->update([
            'documents' => [[
                'title' => 'Test Document',
                'description' => 'Test description',
                'fileName' => 'document.pdf',
                'fileSize' => '100 KB',
                'filePath' => $filePath,
                'uploadDate' => now()->format('Y-m-d'),
                'type' => 'Sales Report',
                'status' => 'approved',
                'comment' => '',
            ]],
        ]);

        $response = $this->actingAs($this->franchisee)
            ->get("/api/v1/unit-manager/units/documents/{$this->unit->id}/1/download");

        $response->assertStatus(200)
            ->assertHeader('content-disposition');
    }

    public function test_download_fails_for_document_without_file(): void
    {
        // Add document without file (old documents)
        $this->unit->update([
            'documents' => [[
                'title' => 'Old Document',
                'description' => 'Test description',
                'fileName' => 'document.pdf',
                'fileSize' => '100 KB',
                'uploadDate' => now()->format('Y-m-d'),
                'type' => 'Sales Report',
                'status' => 'approved',
                'comment' => '',
            ]],
        ]);

        $response = $this->actingAs($this->franchisee)
            ->getJson("/api/v1/unit-manager/units/documents/{$this->unit->id}/1/download");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'File not found in storage. The document may have been uploaded before file storage was implemented.',
            ]);
    }

    public function test_cannot_access_another_franchisees_documents(): void
    {
        Storage::fake('local');

        // Create another franchisee and unit
        $otherFranchisee = User::factory()->create(['role' => 'franchisee']);
        $otherUnit = Unit::factory()->create([
            'franchisee_id' => $otherFranchisee->id,
            'franchise_id' => $this->franchise->id,
        ]);

        // Add document to other unit
        $file = \Illuminate\Http\UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $filePath = $file->storeAs('documents/units/' . $otherUnit->id, 'test_document.pdf', 'local');

        $otherUnit->update([
            'documents' => [[
                'title' => 'Private Document',
                'description' => 'Test description',
                'fileName' => 'document.pdf',
                'fileSize' => '100 KB',
                'filePath' => $filePath,
                'uploadDate' => now()->format('Y-m-d'),
                'type' => 'Sales Report',
                'status' => 'approved',
                'comment' => '',
            ]],
        ]);

        // Try to access as different franchisee
        $response = $this->actingAs($this->franchisee)
            ->getJson("/api/v1/unit-manager/units/documents/{$otherUnit->id}/1/download");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Unit not found or access denied',
            ]);
    }
}
