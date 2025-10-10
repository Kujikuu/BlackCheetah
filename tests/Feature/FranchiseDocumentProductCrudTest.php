<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Franchise;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FranchiseDocumentProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $franchisor;

    private Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a franchisor user
        $this->franchisor = User::factory()->create([
            'role' => 'franchisor',
        ]);

        // Create a franchise with the franchisor
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        // Associate the franchise with the user
        $this->franchisor->franchise_id = $this->franchise->id;
        $this->franchisor->save();

        // Authenticate the user
        Sanctum::actingAs($this->franchisor);

        // Fake storage for file uploads (using 'private' disk as per DocumentController)
        Storage::fake('private');
    }

    /** @test */
    public function test_can_create_franchise_document()
    {
        $file = \Illuminate\Http\UploadedFile::fake()->create('test-document.pdf', 1024, 'application/pdf');

        $response = $this->postJson("/api/v1/franchises/{$this->franchise->id}/documents", [
            'name' => 'Test Document',
            'description' => 'Test Description',
            'type' => 'manual',
            'file' => $file,
            'is_confidential' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Document uploaded successfully.',
            ]);

        $this->assertDatabaseHas('documents', [
            'franchise_id' => $this->franchise->id,
            'name' => 'Test Document',
            'type' => 'manual',
            'is_confidential' => true,
        ]);

        Storage::disk('private')->assertExists($response->json('data.file_path'));
    }

    /** @test */
    public function test_can_list_franchise_documents()
    {
        // Create some documents for the franchise
        Document::factory()->count(3)->create([
            'franchise_id' => $this->franchise->id,
        ]);

        // Create documents for another franchise (should not be returned)
        $otherFranchise = Franchise::factory()->create();
        Document::factory()->count(2)->create([
            'franchise_id' => $otherFranchise->id,
        ]);

        $response = $this->getJson("/api/v1/franchises/{$this->franchise->id}/documents");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data.data');
    }

    /** @test */
    public function test_can_update_franchise_document()
    {
        $document = Document::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Original Name',
            'description' => 'Original Description',
        ]);

        $response = $this->putJson("/api/v1/franchises/{$this->franchise->id}/documents/{$document->id}", [
            'name' => 'Updated Document',
            'description' => 'Updated description',
            'type' => 'policy',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Document updated successfully.',
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'name' => 'Updated Document',
            'description' => 'Updated description',
            'type' => 'policy',
        ]);
    }

    /** @test */
    public function test_can_delete_franchise_document()
    {
        $document = Document::factory()->create([
            'franchise_id' => $this->franchise->id,
        ]);

        $response = $this->deleteJson("/api/v1/franchises/{$this->franchise->id}/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Document deleted successfully.',
            ]);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);
    }

    /** @test */
    public function test_can_create_franchise_product()
    {
        $response = $this->postJson("/api/v1/franchises/{$this->franchise->id}/products", [
            'name' => 'Test Product',
            'description' => 'A test product',
            'category' => 'electronics',
            'unit_price' => 99.99,
            'stock' => 50,
            'sku' => 'TEST-PROD-001',
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully.',
            ]);

        $this->assertDatabaseHas('products', [
            'franchise_id' => $this->franchise->id,
            'name' => 'Test Product',
            'description' => 'A test product',
            'category' => 'electronics',
            'unit_price' => 99.99,
            'stock' => 50,
            'sku' => 'TEST-PROD-001',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function test_can_list_franchise_products()
    {
        // Create some products for the franchise
        Product::factory()->count(3)->create([
            'franchise_id' => $this->franchise->id,
        ]);

        // Create products for another franchise (should not be returned)
        $otherFranchise = Franchise::factory()->create();
        Product::factory()->count(2)->create([
            'franchise_id' => $otherFranchise->id,
        ]);

        $response = $this->getJson("/api/v1/franchises/{$this->franchise->id}/products");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(3, 'data.data');
    }

    /** @test */
    public function test_can_update_franchise_product()
    {
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Original Product',
            'unit_price' => 50.00,
            'sku' => 'ORIG-PROD-001',
        ]);

        $response = $this->putJson("/api/v1/franchises/{$this->franchise->id}/products/{$product->id}", [
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'category' => 'updated-category',
            'unit_price' => 75.00,
            'stock' => 100,
            'sku' => 'UPD-PROD-001',
            'status' => 'inactive',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully.',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'description' => 'Updated Description',
            'category' => 'updated-category',
            'unit_price' => 75.00,
            'stock' => 100,
            'sku' => 'UPD-PROD-001',
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function test_can_delete_franchise_product()
    {
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
        ]);

        $response = $this->deleteJson("/api/v1/franchises/{$this->franchise->id}/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function test_cannot_access_other_franchise_documents()
    {
        $otherFranchise = Franchise::factory()->create();
        $document = Document::factory()->create([
            'franchise_id' => $otherFranchise->id,
        ]);

        $response = $this->getJson("/api/v1/franchises/{$otherFranchise->id}/documents/{$document->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Access denied.',
            ]);
    }

    /** @test */
    public function test_cannot_access_other_franchise_products()
    {
        $otherFranchise = Franchise::factory()->create();
        $product = Product::factory()->create([
            'franchise_id' => $otherFranchise->id,
        ]);

        $response = $this->getJson("/api/v1/franchises/{$otherFranchise->id}/products/{$product->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    /** @test */
    public function test_document_validation_rules()
    {
        $response = $this->postJson("/api/v1/franchises/{$this->franchise->id}/documents", [
            'name' => '', // Required field
            'type' => 'invalid-type', // Invalid type
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'file']);
    }

    /** @test */
    public function test_product_validation_rules()
    {
        $response = $this->postJson("/api/v1/franchises/{$this->franchise->id}/products", [
            'name' => '', // Required field
            'unit_price' => -10, // Invalid price
            'stock' => -5, // Invalid stock
            // Missing required fields: sku, category, status
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category', 'unit_price', 'stock', 'sku', 'status']);
    }

    /** @test */
    public function test_can_update_franchise_information()
    {
        $updateData = [
            'personalInfo' => [
                'contactNumber' => '+1-555-999-8888',
                'address' => '456 Updated St',
                'city' => 'Updated City',
                'state' => 'Updated State',
                'country' => 'Updated Country',
            ],
            'franchiseDetails' => [
                'franchiseDetails' => [
                    'franchiseName' => 'Updated Franchise Name',
                    'website' => 'https://updated.com',
                ],
                'legalDetails' => [
                    'legalEntityName' => 'Updated Legal Entity',
                    'businessStructure' => 'llc',
                    'taxId' => 'TAX123456',
                    'industry' => 'technology',
                    'fundingAmount' => '500000',
                    'fundingSource' => 'bank_loan',
                ],
                'contactDetails' => [
                    'contactNumber' => '+1-555-999-8888',
                    'email' => 'updated@example.com',
                    'address' => '456 Updated St',
                    'city' => 'Updated City',
                    'state' => 'Updated State',
                    'country' => 'Updated Country',
                ],
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Franchise updated successfully',
            ]);

        // Verify franchise data was updated
        $this->assertDatabaseHas('franchises', [
            'id' => $this->franchise->id,
            'brand_name' => 'Updated Franchise Name',
            'website' => 'https://updated.com',
            'business_name' => 'Updated Legal Entity',
            'business_type' => 'llc',
            'tax_id' => 'TAX123456',
            'industry' => 'technology',
            'contact_phone' => '+1-555-999-8888',
            'contact_email' => 'updated@example.com',
            'headquarters_address' => '456 Updated St',
            'headquarters_city' => 'Updated City',
            'headquarters_country' => 'Updated Country',
        ]);

        // Verify user data was updated
        $this->assertDatabaseHas('users', [
            'id' => $this->franchisor->id,
            'phone' => '+1-555-999-8888',
            'address' => '456 Updated St',
            'city' => 'Updated City',
            'state' => 'Updated State',
            'country' => 'Updated Country',
        ]);
    }

    /** @test */
    public function test_can_update_partial_franchise_information()
    {
        $updateData = [
            'personalInfo' => [
                'contactNumber' => '+1-555-777-9999',
            ],
            'franchiseDetails' => [
                'franchiseDetails' => [
                    'franchiseName' => 'Partially Updated Name',
                ],
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Franchise updated successfully',
            ]);

        // Verify only specified fields were updated
        $this->assertDatabaseHas('franchises', [
            'id' => $this->franchise->id,
            'brand_name' => 'Partially Updated Name',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->franchisor->id,
            'phone' => '+1-555-777-9999',
        ]);
    }

    /** @test */
    public function test_franchise_update_validation_rules()
    {
        $invalidData = [
            'personalInfo' => [
                'contactNumber' => str_repeat('a', 25), // Too long
            ],
            'franchiseDetails' => [
                'franchiseDetails' => [
                    'website' => 'invalid-url', // Invalid URL
                ],
                'legalDetails' => [
                    'businessStructure' => 'invalid-structure', // Invalid business structure
                ],
                'contactDetails' => [
                    'email' => 'invalid-email', // Invalid email
                ],
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'personalInfo.contactNumber',
                'franchiseDetails.franchiseDetails.website',
                'franchiseDetails.legalDetails.businessStructure',
                'franchiseDetails.contactDetails.email',
            ]);
    }

    /** @test */
    public function test_cannot_update_franchise_without_authentication()
    {
        // Remove authentication
        $this->app['auth']->forgetGuards();

        $updateData = [
            'personalInfo' => [
                'contactNumber' => '+1-555-999-8888',
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $updateData);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_cannot_update_franchise_without_franchisor_role()
    {
        // Create a user with different role
        $regularUser = User::factory()->create(['role' => 'franchisee']);
        Sanctum::actingAs($regularUser);

        $updateData = [
            'personalInfo' => [
                'contactNumber' => '+1-555-999-8888',
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_cannot_update_franchise_if_no_franchise_exists()
    {
        // Create a franchisor without a franchise
        $franchiserWithoutFranchise = User::factory()->create(['role' => 'franchisor']);
        Sanctum::actingAs($franchiserWithoutFranchise);

        $updateData = [
            'personalInfo' => [
                'contactNumber' => '+1-555-999-8888',
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No franchise found for this user',
            ]);
    }

    /** @test */
    public function test_can_get_franchise_data()
    {
        $response = $this->getJson('/api/v1/franchisor/franchise/data');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'franchise' => [
                        'id',
                        'franchiseDetails' => [
                            'franchiseName',
                            'website',
                            'logo',
                        ],
                        'legalDetails' => [
                            'legalEntityName',
                            'businessStructure',
                            'taxId',
                            'industry',
                            'fundingAmount',
                            'fundingSource',
                        ],
                        'contactDetails' => [
                            'contactNumber',
                            'email',
                            'address',
                            'country',
                            'state',
                            'city',
                        ],
                    ],
                    'documents',
                    'products',
                    'stats' => [
                        'status',
                        'totalDocuments',
                        'totalProducts',
                        'activeProducts',
                    ],
                ],
            ]);
    }

    /** @test */
    public function test_franchise_funding_information_updates_documents_field()
    {
        $updateData = [
            'franchiseDetails' => [
                'legalDetails' => [
                    'fundingAmount' => '750000',
                    'fundingSource' => 'investors',
                ],
            ],
        ];

        $response = $this->putJson('/api/v1/franchisor/franchise/update', $updateData);

        $response->assertStatus(200);

        // Verify funding information is stored in documents field
        $this->franchise->refresh();
        $documents = $this->franchise->documents;

        $this->assertEquals('750000', $documents['funding_amount']);
        $this->assertEquals('investors', $documents['funding_source']);
    }
}
