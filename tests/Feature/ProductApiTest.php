<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    // use RefreshDatabase;

    protected User $user;

    protected Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->franchise = Franchise::factory()->create(['franchisor_id' => $this->user->id]);
    }

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create(['franchise_id' => $this->franchise->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/franchises/{$this->franchise->id}/products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'unit_price',
                            'category',
                            'status',
                            'stock',
                            'created_at',
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_create_product(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/franchises/{$this->franchise->id}/products", [
                'name' => 'Test Product',
                'description' => 'A test product',
                'category' => 'electronics',
                'unit_price' => 99.99,
                'stock' => 10,
                'minimum_stock' => 5,
                'sku' => 'TEST-001',
                'status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'category',
                    'unit_price',
                    'stock',
                    'status',
                    'sku',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'A test product',
            'category' => 'electronics',
            'unit_price' => 99.99,
            'stock' => 10,
            'status' => 'active',
            'sku' => 'TEST-001',
            'franchise_id' => $this->franchise->id,
        ]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Original Name',
            'unit_price' => 50.00,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/franchises/{$this->franchise->id}/products/{$product->id}", [
                'name' => 'Updated Product',
                'description' => 'Updated Description',
                'category' => 'electronics',
                'unit_price' => 149.99,
                'stock' => 20,
                'status' => 'active',
                'sku' => 'UPD-001',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'category',
                    'unit_price',
                    'stock',
                    'status',
                    'sku',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'unit_price' => 149.99,
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create(['franchise_id' => $this->franchise->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/franchises/{$this->franchise->id}/products/{$product->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_requires_authentication(): void
    {
        $response = $this->getJson("/api/v1/franchises/{$this->franchise->id}/products");

        $response->assertStatus(401);
    }

    public function test_validates_required_fields_for_creation(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/franchises/{$this->franchise->id}/products", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'unit_price']);
    }

    public function test_can_update_product_with_formdata(): void
    {
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Original Product',
            'stock' => 10,
        ]);

        // Simulate FormData request (multipart/form-data)
        $response = $this->actingAs($this->user)
            ->call('PUT', "/api/v1/franchises/{$this->franchise->id}/products/{$product->id}", [
                'name' => 'Updated Product Name',
                'description' => 'Updated Description',
                'category' => 'electronics',
                'unit_price' => '149.99',
                'stock' => '11', // Update from 10 to 11 as the user mentioned
                'status' => 'active',
                'sku' => 'UPD-001',
            ], [], [], ['HTTP_Content_Type' => 'multipart/form-data']);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully.',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'stock' => 11, // Verify stock actually updated
        ]);
    }

    public function test_validates_numeric_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/franchises/{$this->franchise->id}/products", [
                'name' => 'Test Product',
                'unit_price' => 'not-a-number',
                'stock' => 'not-a-number',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_price', 'stock']);
    }
}
