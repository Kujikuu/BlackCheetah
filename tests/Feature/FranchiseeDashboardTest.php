<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Revenue;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
