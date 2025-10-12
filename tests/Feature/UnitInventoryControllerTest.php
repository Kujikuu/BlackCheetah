<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnitInventoryControllerTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function index_returns_inventory_list(): void
    {
        $franchise = Franchise::factory()->create();
        $unit = Unit::factory()->create(['franchise_id' => $franchise->id]);
        $product = Product::factory()->create(['franchise_id' => $franchise->id]);

        $unit->products()->attach($product->id, [
            'quantity' => 5,
            'reorder_level' => 2,
        ]);

        $this->actingAs(\App\Models\User::factory()->create());
        $response = $this->getJson("/api/v1/units/{$unit->id}/inventory");

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $product->id,
                'quantity' => 5,
                'reorder_level' => 2,
            ]);
    }

    /** @test */
    public function store_adds_product_to_inventory(): void
    {
        $franchise = Franchise::factory()->create();
        $unit = Unit::factory()->create(['franchise_id' => $franchise->id]);
        $product = Product::factory()->create(['franchise_id' => $franchise->id]);

        $payload = [
            'quantity' => 10,
            'reorder_level' => 3,
        ];

        $this->actingAs(\App\Models\User::factory()->create());
        $response = $this->postJson("/api/v1/units/{$unit->id}/inventory/{$product->id}", $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('unit_product_inventories', [
            'unit_id' => $unit->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'reorder_level' => 3,
        ]);
    }

    /** @test */
    public function update_changes_inventory_quantity(): void
    {
        $franchise = Franchise::factory()->create();
        $unit = Unit::factory()->create(['franchise_id' => $franchise->id]);
        $product = Product::factory()->create(['franchise_id' => $franchise->id]);
        $unit->products()->attach($product->id, ['quantity' => 5]);

        $payload = ['quantity' => 8];

        $this->actingAs(\App\Models\User::factory()->create());
        $response = $this->putJson("/api/v1/units/{$unit->id}/inventory/{$product->id}", $payload);

        $response->assertOk();

        $this->assertDatabaseHas('unit_product_inventories', [
            'unit_id' => $unit->id,
            'product_id' => $product->id,
            'quantity' => 8,
        ]);
    }

    /** @test */
    public function destroy_removes_inventory_record(): void
    {
        $franchise = Franchise::factory()->create();
        $unit = Unit::factory()->create(['franchise_id' => $franchise->id]);
        $product = Product::factory()->create(['franchise_id' => $franchise->id]);
        $unit->products()->attach($product->id, ['quantity' => 5]);

        $this->actingAs(\App\Models\User::factory()->create());
        $response = $this->deleteJson("/api/v1/units/{$unit->id}/inventory/{$product->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('unit_product_inventories', [
            'unit_id' => $unit->id,
            'product_id' => $product->id,
        ]);
    }
}
