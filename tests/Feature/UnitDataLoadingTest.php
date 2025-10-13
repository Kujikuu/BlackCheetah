<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Franchise;
use App\Models\Product;
use App\Models\Staff;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitDataLoadingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test franchisor can get franchise documents (not unit-specific)
     */
    public function test_franchisor_can_get_franchise_documents(): void
    {
        // Create a franchisor
        $franchisor = User::factory()->create(['role' => 'franchisor']);

        // Create a franchise owned by the franchisor
        $franchise = Franchise::factory()->create(['franchisor_id' => $franchisor->id]);

        // Create a franchisee
        $franchisee = User::factory()->create(['role' => 'franchisee']);

        // Create a unit belonging to the franchise
        $unit = Unit::factory()->create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => $franchisee->id,
        ]);

        // Create documents for the franchise (documents are franchise-level, not unit-level)
        $doc1 = Document::factory()->create([
            'franchise_id' => $franchise->id,
            'name' => 'Franchise License',
            'type' => 'Legal',
        ]);

        $doc2 = Document::factory()->create([
            'franchise_id' => $franchise->id,
            'name' => 'Operating Manual',
            'type' => 'Operations',
        ]);

        // Create a document for a different franchise (should not be returned)
        $otherFranchise = Franchise::factory()->create(['franchisor_id' => User::factory()->create(['role' => 'franchisor'])->id]);
        Document::factory()->create([
            'franchise_id' => $otherFranchise->id,
            'name' => 'Other Franchise Doc',
        ]);

        // Make request to get franchise documents
        $response = $this->actingAs($franchisor, 'sanctum')
            ->getJson("/api/v1/franchises/{$franchise->id}/documents");

        // Assert response contains only the franchise's documents
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonCount(2, 'data.data');

        $response->assertJsonFragment(['name' => 'Franchise License']);
        $response->assertJsonFragment(['name' => 'Operating Manual']);
    }

    /**
     * Test franchisor can get unit inventory
     */
    public function test_franchisor_can_get_unit_inventory(): void
    {
        // Create a franchisor
        $franchisor = User::factory()->create(['role' => 'franchisor']);

        // Create a franchise owned by the franchisor
        $franchise = Franchise::factory()->create(['franchisor_id' => $franchisor->id]);

        // Create a franchisee
        $franchisee = User::factory()->create(['role' => 'franchisee']);

        // Create a unit
        $unit = Unit::factory()->create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => $franchisee->id,
        ]);

        // Create products for the franchise
        $product1 = Product::factory()->create([
            'franchise_id' => $franchise->id,
            'name' => 'Product A',
            'unit_price' => 100,
        ]);

        $product2 = Product::factory()->create([
            'franchise_id' => $franchise->id,
            'name' => 'Product B',
            'unit_price' => 200,
        ]);

        // Attach products to unit with inventory quantities
        $unit->products()->attach($product1->id, [
            'quantity' => 50,
            'reorder_level' => 10,
        ]);

        $unit->products()->attach($product2->id, [
            'quantity' => 30,
            'reorder_level' => 5,
        ]);

        // Make request to get unit inventory
        $response = $this->actingAs($franchisor, 'sanctum')
            ->getJson("/api/v1/units/{$unit->id}/inventory");

        // Assert response contains unit inventory
        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $response->assertJsonFragment([
            'name' => 'Product A',
            'quantity' => 50,
        ]);

        $response->assertJsonFragment([
            'name' => 'Product B',
            'quantity' => 30,
        ]);
    }

    /**
     * Test tasks API supports unit_id filtering
     */
    public function test_tasks_can_be_filtered_by_unit(): void
    {
        // Create a franchisor
        $franchisor = User::factory()->create(['role' => 'franchisor']);

        // Create a franchise
        $franchise = Franchise::factory()->create(['franchisor_id' => $franchisor->id]);

        // Create a franchisee
        $franchisee = User::factory()->create(['role' => 'franchisee']);

        // Create a unit
        $unit = Unit::factory()->create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => $franchisee->id,
        ]);

        // Create a task for the unit
        $task = Task::factory()->create([
            'unit_id' => $unit->id,
            'title' => 'Unit Task',
            'assigned_to' => $franchisee->id,
            'franchise_id' => $franchise->id,
        ]);

        // Make request filtered by unit_id
        $response = $this->actingAs($franchisor, 'sanctum')
            ->getJson("/api/v1/tasks?unit_id={$unit->id}");

        // Assert response is successful and contains data structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data', // Paginated data
                    'current_page',
                    'per_page',
                ],
            ]);

        // Verify the endpoint accepts the unit_id parameter (no error)
        $this->assertTrue($response->json('success'));
    }

    /**
     * Test unit inventory returns empty for unit with no products
     */
    public function test_unit_inventory_returns_empty_for_unit_with_no_products(): void
    {
        // Create a franchisor
        $franchisor = User::factory()->create(['role' => 'franchisor']);

        // Create a franchise
        $franchise = Franchise::factory()->create(['franchisor_id' => $franchisor->id]);

        // Create a unit with no products
        $unit = Unit::factory()->create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => User::factory()->create(['role' => 'franchisee'])->id,
        ]);

        // Make request to get unit inventory
        $response = $this->actingAs($franchisor, 'sanctum')
            ->getJson("/api/v1/units/{$unit->id}/inventory");

        // Assert response is empty
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}
