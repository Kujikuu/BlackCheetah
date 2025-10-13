<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Product;
use App\Models\Revenue;
use App\Models\Review;
use App\Models\Royalty;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $franchisee;

    private Unit $unit;

    private Franchise $franchise;

    protected function setUp(): void
    {
        parent::setUp();

        // Create franchisor
        $franchisor = User::factory()->create([
            'role' => 'franchisor',
            'email' => 'franchisor@test.com',
        ]);

        // Create franchise
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $franchisor->id,
        ]);

        // Create franchisee
        $this->franchisee = User::factory()->create([
            'role' => 'franchisee',
            'email' => 'franchisee@test.com',
        ]);

        // Create unit
        $this->unit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
            'franchisee_id' => $this->franchisee->id,
        ]);
    }

    public function test_franchisee_can_get_performance_management_data(): void
    {
        // Create products
        $product1 = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Product A',
        ]);

        $product2 = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Product B',
        ]);

        // Create revenues with line items
        Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'line_items' => json_encode([
                [
                    'product_id' => $product1->id,
                    'product_name' => 'Product A',
                    'quantity' => 10,
                    'price' => 100,
                ],
                [
                    'product_id' => $product2->id,
                    'product_name' => 'Product B',
                    'quantity' => 5,
                    'price' => 200,
                ],
            ]),
            'amount' => 2000,
            'revenue_date' => now()->subMonth(),
        ]);

        // Create royalties
        Royalty::factory()->count(3)->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
        ]);

        // Create tasks
        Task::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'status' => 'completed',
        ]);

        Task::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'status' => 'in_progress',
        ]);

        Task::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'status' => 'pending',
            'due_date' => now()->addDay(),
        ]);

        // Create reviews
        Review::factory()->create([
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'rating' => 5,
            'sentiment' => 'positive',
            'status' => 'published',
        ]);

        Review::factory()->create([
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'rating' => 4,
            'sentiment' => 'positive',
            'status' => 'published',
        ]);

        Review::factory()->create([
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'rating' => 2,
            'sentiment' => 'negative',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->getJson('/api/v1/unit-manager/dashboard/performance-management');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'productPerformance' => [
                        'topPerformingProductData',
                        'lowPerformingProductData',
                    ],
                    'royalty' => [
                        'amount',
                        'phaseData',
                    ],
                    'tasksOverview' => [
                        'completed',
                        'inProgress',
                        'due',
                        'total',
                    ],
                    'customerSatisfaction' => [
                        'score',
                        'users',
                        'positive',
                        'neutral',
                        'negative',
                    ],
                ],
                'message',
            ]);

        $data = $response->json('data');

        // Verify product performance
        $this->assertIsArray($data['productPerformance']['topPerformingProductData']);
        $this->assertIsArray($data['productPerformance']['lowPerformingProductData']);

        // Verify royalty data
        $this->assertIsNumeric($data['royalty']['amount']);
        $this->assertIsArray($data['royalty']['phaseData']);
        $this->assertCount(4, $data['royalty']['phaseData']); // Should have 4 data points

        // Verify tasks overview
        $this->assertEquals(1, $data['tasksOverview']['completed']);
        $this->assertEquals(1, $data['tasksOverview']['inProgress']);
        $this->assertEquals(1, $data['tasksOverview']['due']);
        $this->assertEquals(3, $data['tasksOverview']['total']);

        // Verify customer satisfaction
        $this->assertGreaterThan(0, $data['customerSatisfaction']['score']);
        $this->assertEquals(3, $data['customerSatisfaction']['users']);
        $this->assertIsNumeric($data['customerSatisfaction']['positive']);
        $this->assertIsNumeric($data['customerSatisfaction']['neutral']);
        $this->assertIsNumeric($data['customerSatisfaction']['negative']);
    }

    public function test_performance_management_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/unit-manager/dashboard/performance-management');

        $response->assertStatus(401);
    }

    public function test_performance_management_requires_franchisee_role(): void
    {
        $staff = User::factory()->create(['role' => 'unit_staff']);

        $response = $this->actingAs($staff, 'sanctum')
            ->getJson('/api/v1/unit-manager/dashboard/performance-management');

        $response->assertStatus(403);
    }

    public function test_performance_management_handles_empty_data_gracefully(): void
    {
        // Create a new unit with no data
        $emptyUnit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
            'franchisee_id' => $this->franchisee->id,
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->getJson('/api/v1/unit-manager/dashboard/performance-management');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Verify it returns empty but valid structure
        $this->assertIsArray($data['productPerformance']['topPerforming']);
        $this->assertIsArray($data['productPerformance']['lowPerforming']);
        $this->assertCount(4, $data['royaltyData']['amounts']); // Should still have 4 zeros
        $this->assertEquals(0, $data['tasksOverview']['total']);
        $this->assertEquals(0, $data['customerSatisfaction']['score']);
    }

    public function test_product_performance_aggregates_correctly(): void
    {
        $product = Product::factory()->create([
            'franchise_id' => $this->franchise->id,
            'name' => 'Test Product',
        ]);

        // Create multiple revenues with the same product
        Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'line_items' => json_encode([
                [
                    'product_id' => $product->id,
                    'product_name' => 'Test Product',
                    'quantity' => 10,
                    'price' => 100,
                ],
            ]),
            'amount' => 1000,
            'revenue_date' => now()->subMonth(),
        ]);

        Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'line_items' => json_encode([
                [
                    'product_id' => $product->id,
                    'product_name' => 'Test Product',
                    'quantity' => 5,
                    'price' => 100,
                ],
            ]),
            'amount' => 500,
            'revenue_date' => now()->subMonth(),
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->getJson('/api/v1/unit-manager/dashboard/performance-management');

        $response->assertStatus(200);

        $data = $response->json('data');
        $topPerforming = collect($data['productPerformance']['topPerforming']);

        // Verify the product appears and quantities are aggregated
        $testProduct = $topPerforming->firstWhere('name', 'Test Product');
        $this->assertNotNull($testProduct);
        $this->assertEquals(15, $testProduct['value']); // 10 + 5
    }

    public function test_customer_satisfaction_calculates_score_correctly(): void
    {
        // Create reviews with known ratings
        Review::factory()->create([
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'rating' => 5,
            'sentiment' => 'positive',
            'status' => 'published',
        ]);

        Review::factory()->create([
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'rating' => 3,
            'sentiment' => 'neutral',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->franchisee, 'sanctum')
            ->getJson('/api/v1/unit-manager/dashboard/performance-management');

        $response->assertStatus(200);

        $data = $response->json('data');
        $satisfaction = $data['customerSatisfaction'];

        // Expected score: (5 + 3) / 2 = 4.0
        $this->assertEquals(4.0, $satisfaction['score']);
        $this->assertEquals(2, $satisfaction['totalReviews']);

        // Verify sentiment breakdown
        $this->assertEquals(50, $satisfaction['sentimentBreakdown']['positive']); // 1/2 = 50%
        $this->assertEquals(50, $satisfaction['sentimentBreakdown']['neutral']); // 1/2 = 50%
        $this->assertEquals(0, $satisfaction['sentimentBreakdown']['negative']); // 0/2 = 0%
    }
}
