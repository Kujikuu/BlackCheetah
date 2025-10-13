<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\Transaction;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FinancialOverviewTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $franchisee;

    protected Unit $unit;

    protected Franchise $franchise;

    protected Product $product;

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
        ]);

        // Attach product to unit inventory if needed (check if Inventory model exists)
        try {
            \App\Models\Inventory::create([
                'unit_id' => $this->unit->id,
                'product_id' => $this->product->id,
                'quantity' => 100,
            ]);
        } catch (\Exception $e) {
            // Inventory model might not be required for this feature
        }
    }

    public function test_financial_overview_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/unit-manager/dashboard/financial-overview');
        $response->assertStatus(401);
    }

    public function test_financial_overview_requires_franchisee_role(): void
    {
        $regularUser = User::factory()->create(['role' => 'customer']);
        $response = $this->actingAs($regularUser)
            ->getJson('/api/v1/unit-manager/dashboard/financial-overview');
        $response->assertStatus(403);
    }

    public function test_financial_overview_returns_sales_expenses_and_profit(): void
    {
        // Create revenue with line items and type='sales'
        Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'sales',  // Must have type='sales'
            'amount' => 500,
            'revenue_date' => now(),
            'line_items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 5,
                    'price' => 100,
                ],
            ],
        ]);

        // Create expense transaction
        Transaction::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'expense',
            'category' => 'supplies',
            'amount' => 200,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($this->franchisee)
            ->getJson('/api/v1/unit-manager/dashboard/financial-overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'sales',
                    'expenses',
                    'profit',
                    'totals',
                ],
            ]);

        $data = $response->json('data');

        // Verify basic structure and data are returned
        $this->assertIsArray($data['sales']);
        $this->assertIsArray($data['expenses']);
        $this->assertIsArray($data['profit']);
        $this->assertIsArray($data['totals']);

        // Verify totals keys exist
        $this->assertArrayHasKey('sales', $data['totals']);
        $this->assertArrayHasKey('expenses', $data['totals']);
        $this->assertArrayHasKey('profit', $data['totals']);
    }
    public function test_can_add_new_sale(): void
    {
        $payload = [
            'category' => 'sales',
            'date' => now()->toDateString(),
            'product' => $this->product->name,
            'quantitySold' => 3,
        ];

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Sales data added successfully',
            ]);

        // Verify revenue was created
        $this->assertDatabaseHas('revenues', [
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'amount' => 300, // 3 * 100
        ]);
    }

    public function test_can_add_new_expense(): void
    {
        $payload = [
            'category' => 'expense',
            'date' => now()->toDateString(),
            'expenseCategory' => 'utilities',
            'amount' => 150.50,
            'description' => 'Electric bill',
        ];

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Expense data added successfully',
            ]);

        // Verify transaction was created
        $this->assertDatabaseHas('transactions', [
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'expense',
            'category' => 'utilities',
            'amount' => 150.50,
            'description' => 'Electric bill',
        ]);
    }

    public function test_can_delete_sales_records(): void
    {
        // Create a revenue record
        $revenue = Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'amount' => 500,
            'revenue_date' => now(),
            'line_items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 5,
                    'price' => 100,
                ],
            ],
        ]);

        // Sales records use composite IDs like "revenue_id-product_id"
        $compositeId = $revenue->id . '-' . $this->product->id;

        $payload = [
            'category' => 'sales',
            'ids' => [$compositeId],
        ];

        $response = $this->actingAs($this->franchisee)
            ->deleteJson('/api/v1/unit-manager/dashboard/financial-data', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('message', function ($message) {
                return str_contains($message, 'record(s) deleted successfully');
            });

        // Verify revenue was deleted
        $this->assertDatabaseMissing('revenues', [
            'id' => $revenue->id,
        ]);
    }

    public function test_can_delete_expense_records(): void
    {
        // Create expense transactions
        $expense1 = Transaction::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'expense',
            'category' => 'supplies',
            'amount' => 100,
        ]);

        $expense2 = Transaction::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'expense',
            'category' => 'utilities',
            'amount' => 200,
        ]);

        $payload = [
            'category' => 'expense',
            'ids' => [(string) $expense1->id, (string) $expense2->id],
        ];

        $response = $this->actingAs($this->franchisee)
            ->deleteJson('/api/v1/unit-manager/dashboard/financial-data', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('message', function ($message) {
                return str_contains($message, 'record(s) deleted successfully');
            });

        // Verify expenses were deleted
        $this->assertDatabaseMissing('transactions', [
            'id' => $expense1->id,
        ]);
        $this->assertDatabaseMissing('transactions', [
            'id' => $expense2->id,
        ]);
    }

    public function test_add_sale_validates_required_fields(): void
    {
        $payload = [
            'category' => 'sales',
            // Missing required fields
        ];

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'product', 'quantitySold']);
    }

    public function test_add_expense_validates_required_fields(): void
    {
        $payload = [
            'category' => 'expense',
            // Missing required fields
        ];

        $response = $this->actingAs($this->franchisee)
            ->postJson('/api/v1/unit-manager/dashboard/financial-data', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'expenseCategory', 'amount']);
    }

    public function test_financial_overview_only_shows_own_unit_data(): void
    {
        // Create another franchisee with their own unit
        $otherFranchisee = User::factory()->create(['role' => 'franchisee']);
        $otherUnit = Unit::factory()->create([
            'franchisee_id' => $otherFranchisee->id,
            'franchise_id' => $this->franchise->id,
        ]);

        // Create revenue for the other unit
        Revenue::factory()->create([
            'unit_id' => $otherUnit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'sales',  // Must have type='sales'
            'amount' => 1000,
            'revenue_date' => now(),
            'line_items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 10,
                    'price' => 100,
                ],
            ],
        ]);

        // Create revenue for current franchisee's unit
        Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'type' => 'sales',  // Must have type='sales'
            'amount' => 500,
            'revenue_date' => now(),
            'line_items' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 5,
                    'price' => 100,
                ],
            ],
        ]);

        $response = $this->actingAs($this->franchisee)
            ->getJson('/api/v1/unit-manager/dashboard/financial-overview');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Should only see own unit's sales
        $this->assertCount(1, $data['sales']);
        $this->assertEquals(500, $data['totals']['sales']);
    }
}
