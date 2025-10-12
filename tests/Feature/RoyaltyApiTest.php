<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Royalty;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoyaltyApiTest extends TestCase
{
    use RefreshDatabase;

    private User $franchisor;

    private Franchise $franchise;

    private Unit $unit;

    private User $franchisee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a franchisor user
        $this->franchisor = User::factory()->create([
            'role' => 'franchisor',
        ]);

        // Create a franchisee user
        $this->franchisee = User::factory()->create([
            'role' => 'franchisee',
        ]);

        // Create a franchise for the franchisor
        $this->franchise = Franchise::factory()->create([
            'franchisor_id' => $this->franchisor->id,
        ]);

        // Create a unit for the franchise
        $this->unit = Unit::factory()->create([
            'franchise_id' => $this->franchise->id,
            'franchisee_id' => $this->franchisee->id,
        ]);

        // Authenticate the franchisor
        Sanctum::actingAs($this->franchisor);
    }

    /** @test */
    public function it_can_get_royalties_list()
    {
        // Create test royalties
        Royalty::factory()->count(5)->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
        ]);

        // Make the request
        $response = $this->getJson('/api/v1/franchisor/royalties');

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'royalty_number',
                            'billing_period',
                            'franchisee_name',
                            'store_location',
                            'due_date',
                            'gross_sales',
                            'royalty_percentage',
                            'amount',
                            'status',
                        ],
                    ],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
                'message',
            ]);
    }

    /** @test */
    public function it_can_filter_royalties_by_status()
    {
        // Create royalties with different statuses
        Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'status' => 'paid',
        ]);

        Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'status' => 'pending',
        ]);

        // Filter by paid status
        $response = $this->getJson('/api/v1/franchisor/royalties?status=paid');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data.data')));
        $this->assertEquals('paid', $response->json('data.data.0.status'));
    }

    /** @test */
    public function it_can_get_royalty_statistics()
    {
        // Create test royalties with specific amounts
        Royalty::factory()->paid()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'total_amount' => 10000,
        ]);

        Royalty::factory()->pending()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'total_amount' => 5000,
        ]);

        // Make the request
        $response = $this->getJson('/api/v1/franchisor/royalties/statistics');

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'royalty_collected_till_date',
                    'upcoming_royalties',
                    'total_royalties',
                    'pending_amount',
                    'paid_amount',
                    'overdue_amount',
                ],
                'message',
            ]);

        // Assert amounts are correct
        $paidAmount = $response->json('data.royalty_collected_till_date');
        $pendingAmount = $response->json('data.upcoming_royalties');

        $this->assertEquals(10000, $paidAmount, 'Paid amount mismatch. Got: '.$paidAmount);
        $this->assertEquals(5000, $pendingAmount, 'Pending amount mismatch. Got: '.$pendingAmount);
    }

    /** @test */
    public function it_can_mark_royalty_as_paid()
    {
        Storage::fake('public');

        $royalty = Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'status' => 'pending',
            'total_amount' => 10000,
        ]);

        $file = UploadedFile::fake()->create('receipt.pdf', 100);

        // Make the request
        $response = $this->patchJson("/api/v1/franchisor/royalties/{$royalty->id}/mark-paid", [
            'amount_paid' => 10000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'REF123456',
            'notes' => 'Payment received',
            'attachment' => $file,
        ]);

        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Assert the royalty was updated
        $royalty->refresh();
        $this->assertEquals('paid', $royalty->status);
        $this->assertEquals('bank_transfer', $royalty->payment_method);
        $this->assertEquals('REF123456', $royalty->payment_reference);
        $this->assertNotNull($royalty->paid_date);

        // Assert file was uploaded
        $this->assertNotEmpty($royalty->attachments);
    }

    /** @test */
    public function it_validates_mark_as_paid_request()
    {
        $royalty = Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'status' => 'pending',
        ]);

        // Make request without required fields
        $response = $this->patchJson("/api/v1/franchisor/royalties/{$royalty->id}/mark-paid", []);

        // Assert validation errors
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount_paid', 'payment_date', 'payment_method']);
    }

    /** @test */
    public function it_can_export_royalties_as_csv()
    {
        // Create test royalties
        Royalty::factory()->count(3)->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
        ]);

        // Make the request
        $response = $this->getJson('/api/v1/franchisor/royalties/export?format=csv&data_type=all&period=monthly');

        // Assert the response
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function it_can_add_adjustment_to_royalty()
    {
        $royalty = Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'total_amount' => 10000,
        ]);

        // Make the request
        $response = $this->postJson("/api/v1/franchisor/royalties/{$royalty->id}/adjustments", [
            'adjustment_amount' => -500,
            'adjustment_reason' => 'Discount applied',
        ]);

        // Assert the response
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Assert the royalty was updated
        $royalty->refresh();
        $this->assertEquals(-500, $royalty->adjustments);
        $this->assertEquals('Discount applied', $royalty->adjustment_notes);
    }

    /** @test */
    public function it_can_calculate_late_fee()
    {
        $royalty = Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'status' => 'pending',
            'due_date' => now()->subDays(10),
            'total_amount' => 10000,
        ]);

        // Make the request
        $response = $this->postJson("/api/v1/franchisor/royalties/{$royalty->id}/late-fee");

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'royalty',
                    'late_fee',
                    'days_overdue',
                ],
            ]);
    }

    /** @test */
    public function it_can_search_royalties()
    {
        $royalty = Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
            'royalty_number' => 'ROY202410010001',
        ]);

        // Make the request
        $response = $this->getJson('/api/v1/franchisor/royalties?search=ROY202410010001');

        // Assert the response
        $response->assertStatus(200);
        $this->assertGreaterThan(0, count($response->json('data.data')));
        $this->assertEquals('ROY202410010001', $response->json('data.data.0.royalty_number'));
    }

    /** @test */
    public function it_can_get_single_royalty()
    {
        $royalty = Royalty::factory()->create([
            'franchise_id' => $this->franchise->id,
            'unit_id' => $this->unit->id,
            'franchisee_id' => $this->franchisee->id,
        ]);

        // Make the request
        $response = $this->getJson("/api/v1/royalties/{$royalty->id}");

        // Assert the response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'royalty_number',
                    'franchise_id',
                    'status',
                ],
            ]);
    }
}
