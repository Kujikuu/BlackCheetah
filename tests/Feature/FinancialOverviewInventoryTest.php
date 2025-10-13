<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FinancialOverviewInventoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $franchisee;

    protected Unit $unit;

    protected Franchise $franchise;

    protected Product $product;

    protected Inventory $inventory;

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

        // Create a product for the unit
        $this->product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Test Product',
            'unit_price' => 100.00,
            'status' => 'active',
        ]);

        // Create inventory with stock
        $this->inventory = Inventory::create([
            'unit_id' => $this->unit->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
            'reorder_level' => 10,
        ]);
    }

    public function test_cannot_add_sale_if_product_not_found(): void
    {
        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Non-Existent Product',
                'quantitySold' => 5,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Product not found',
            ]);
    }

    public function test_cannot_add_sale_if_product_not_in_unit_inventory(): void
    {
        // Create a product that exists but is not in this unit's inventory
        $otherProduct = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Other Product',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Other Product',
                'quantitySold' => 5,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Product not available in your unit inventory',
            ]);
    }

    public function test_cannot_add_sale_with_quantity_exceeding_stock(): void
    {
        // Try to sell 100 units when only 50 are available
        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Test Product',
                'quantitySold' => 100,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient stock. Only 50 units available',
            ]);
    }

    public function test_adding_sale_reduces_inventory_quantity(): void
    {
        // Get fresh inventory data
        $inventory = Inventory::where('unit_id', $this->unit->id)
            ->where('product_id', $this->product->id)
            ->first();
        $initialStock = $inventory->quantity;

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Test Product',
                'quantitySold' => 10,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // Verify inventory was reduced
        $inventory->refresh();
        $this->assertEquals($initialStock - 10, $inventory->quantity);
    }

    public function test_can_add_sale_with_exact_stock_quantity(): void
    {
        // Create a new product specifically for this test
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Exact Stock Product',
            'unit_price' => 100.00,
            'status' => 'active',
        ]);

        // Create inventory with exactly 10 units
        $inventory = Inventory::create([
            'unit_id' => $this->unit->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'reorder_level' => 5,
        ]);

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Exact Stock Product',
                'quantitySold' => 10,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // Verify inventory is now 0 (fetch fresh to avoid cached model)
        $updatedInventory = Inventory::where('unit_id', $this->unit->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertNotNull($updatedInventory);
        $this->assertEquals(0, $updatedInventory->quantity);
    }

    public function test_response_includes_remaining_stock_after_sale(): void
    {
        // Create a fresh product for this test
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Stock Response Product',
            'unit_price' => 100.00,
            'status' => 'active',
        ]);

        // Create inventory with 50 units
        Inventory::create([
            'unit_id' => $this->unit->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'reorder_level' => 10,
        ]);

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Stock Response Product',
                'quantitySold' => 15,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'remainingStock',
                ],
            ])
            ->assertJson([
                'data' => [
                    'remainingStock' => 35, // 50 - 15
                ],
            ]);
    }

    public function test_multiple_sales_correctly_reduce_inventory(): void
    {
        // Create a fresh product for this test
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Multiple Sales Product',
            'unit_price' => 100.00,
            'status' => 'active',
        ]);

        // Create inventory with 50 units
        $inventory = Inventory::create([
            'unit_id' => $this->unit->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'reorder_level' => 10,
        ]);

        // First sale
        $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Multiple Sales Product',
                'quantitySold' => 10,
                'date' => now()->format('Y-m-d'),
            ])
            ->assertStatus(201);

        // Check inventory after first sale
        $updatedInventory = Inventory::where('unit_id', $this->unit->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertEquals(40, $updatedInventory->quantity);

        // Second sale
        $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Multiple Sales Product',
                'quantitySold' => 15,
                'date' => now()->format('Y-m-d'),
            ])
            ->assertStatus(201);

        // Check inventory after second sale
        $updatedInventory = Inventory::where('unit_id', $this->unit->id)
            ->where('product_id', $product->id)
            ->first();
        $this->assertEquals(25, $updatedInventory->quantity);
    }

    public function test_cannot_sell_after_stock_depleted(): void
    {
        // Create a new product with zero stock
        $depletedProduct = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Depleted Product',
            'unit_price' => 50.00,
            'status' => 'active',
        ]);

        // Create inventory with 0 stock
        $depletedInventory = Inventory::create([
            'unit_id' => $this->unit->id,
            'product_id' => $depletedProduct->id,
            'quantity' => 0,
            'reorder_level' => 10,
        ]);

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', [
                'category' => 'sales',
                'product' => 'Depleted Product',
                'quantitySold' => 1,
                'date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient stock. Only 0 units available',
            ]);
    }
}
