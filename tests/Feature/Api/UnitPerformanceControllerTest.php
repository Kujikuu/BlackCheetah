<?php

namespace Tests\Feature\Api;

use App\Models\Franchise;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UnitPerformanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $franchisor;

    private Franchise $franchise;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a franchisor user
        $this->franchisor = User::factory()->create([
            'role' => 'franchisor',
        ]);

        // Create a franchise for this franchisor
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        // Create a unit for this franchise
        $this->unit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
        ]);

        // Create some sample data
        $this->createSampleData();
    }

    private function createSampleData(): void
    {
        $now = now();

        // Create revenues using factory
        \App\Models\Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'amount' => 5000,
            'period_year' => $now->year,
            'period_month' => $now->month,
            'revenue_date' => $now->toDateString(),
        ]);

        \App\Models\Revenue::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'amount' => 3000,
            'period_year' => $now->copy()->subMonth()->year,
            'period_month' => $now->copy()->subMonth()->month,
            'revenue_date' => $now->copy()->subMonth()->toDateString(),
        ]);

        // Create expenses (transactions) using factory
        \App\Models\Transaction::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'amount' => 2000,
            'type' => 'expense',
            'transaction_date' => $now->toDateString(),
            'description' => 'Operating costs',
        ]);

        \App\Models\Transaction::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'amount' => 1500,
            'type' => 'expense',
            'transaction_date' => $now->copy()->subMonth()->toDateString(),
            'description' => 'Supplies',
        ]);

        // Create royalties using factory
        \App\Models\Royalty::factory()->create([
            'unit_id' => $this->unit->id,
            'franchise_id' => $this->franchise->id,
            'total_amount' => 500,
            'period_year' => $now->year,
            'period_month' => $now->month,
            'status' => 'paid',
            'due_date' => $now->addDays(30)->toDateString(),
        ]);

        // Create reviews using factory
        \App\Models\Review::factory()->create([
            'unit_id' => $this->unit->id,
            'rating' => 4.5,
            'comment' => 'Great service!',
        ]);

        \App\Models\Review::factory()->create([
            'unit_id' => $this->unit->id,
            'rating' => 4.0,
            'comment' => 'Good experience',
        ]);
    }

    public function test_chart_data_returns_calculated_metrics(): void
    {
        $response = $this->actingAs($this->franchisor)
            ->getJson('/api/v1/franchisor/performance/chart-data?period_type=monthly');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'periods',
                    'datasets' => [
                        'all' => ['revenue', 'expenses', 'royalties', 'profit'],
                    ],
                ],
                'message',
            ]);

        $data = $response->json('data');

        // Verify we have period labels
        $this->assertNotEmpty($data['periods']);

        // Verify datasets contain numeric values
        $this->assertIsArray($data['datasets']['all']['revenue']);
        $this->assertIsArray($data['datasets']['all']['expenses']);
    }

    public function test_top_performers_calculates_from_real_data(): void
    {
        $response = $this->actingAs($this->franchisor)
            ->getJson('/api/v1/franchisor/performance/top-performers?period_type=monthly&limit=3');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'location',
                        'revenue',
                        'growth',
                    ],
                ],
                'message',
            ]);

        $data = $response->json('data');

        // Verify we have at least one unit
        $this->assertNotEmpty($data);

        // Verify revenue format
        if (count($data) > 0) {
            $this->assertStringContainsString('SAR', $data[0]['revenue']);
        }
    }

    public function test_customer_satisfaction_calculates_from_reviews(): void
    {
        $response = $this->actingAs($this->franchisor)
            ->getJson('/api/v1/franchisor/performance/customer-satisfaction?period_type=monthly');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'score',
                    'max_score',
                    'total_reviews',
                    'trend',
                ],
                'message',
            ]);

        $data = $response->json('data.score');

        // Verify score is within valid range
        $this->assertGreaterThanOrEqual(0, $data);
        $this->assertLessThanOrEqual(5, $data);
    }

    public function test_ratings_returns_top_and_lowest_rated_units(): void
    {
        $response = $this->actingAs($this->franchisor)
            ->getJson('/api/v1/franchisor/performance/ratings?period_type=monthly');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'top_rated',
                    'lowest_rated',
                ],
                'message',
            ]);
    }

    public function test_units_returns_all_franchise_units(): void
    {
        $response = $this->actingAs($this->franchisor)
            ->getJson('/api/v1/franchisor/performance/units');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'location',
                        'code',
                    ],
                ],
                'message',
            ]);

        $data = $response->json('data');

        // Verify "All Units" option is first
        $this->assertEquals('all', $data[0]['id']);
        $this->assertEquals('All Units', $data[0]['name']);
    }

    public function test_export_returns_performance_data(): void
    {
        $response = $this->actingAs($this->franchisor)
            ->getJson('/api/v1/franchisor/performance/export?period_type=monthly&export_type=performance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
            ]);
    }

    public function test_unauthorized_user_cannot_access_performance_data(): void
    {
        $response = $this->getJson('/api/v1/franchisor/performance/chart-data');

        $response->assertStatus(401);
    }
}
