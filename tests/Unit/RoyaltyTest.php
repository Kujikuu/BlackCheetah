<?php

namespace Tests\Unit;

use App\Models\Franchise;
use App\Models\Royalty;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoyaltyTest extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function it_generates_unique_royalty_number_on_creation()
    {
        $franchise = Franchise::factory()->create();
        $unit = Unit::factory()->create(['franchise_id' => $franchise->id]);
        $franchisee = User::factory()->create();

        $royalty = Royalty::factory()->create([
            'franchise_id' => $franchise->id,
            'unit_id' => $unit->id,
            'franchisee_id' => $franchisee->id,
        ]);

        $this->assertNotNull($royalty->royalty_number);
        $this->assertStringStartsWith('ROY', $royalty->royalty_number);
    }

    /** @test */
    public function it_can_check_if_royalty_is_paid()
    {
        $royalty = Royalty::factory()->create(['status' => 'paid']);

        $this->assertTrue($royalty->isPaid());
        $this->assertFalse($royalty->isPending());
    }

    /** @test */
    public function it_can_check_if_royalty_is_pending()
    {
        $royalty = Royalty::factory()->create(['status' => 'pending']);

        $this->assertTrue($royalty->isPending());
        $this->assertFalse($royalty->isPaid());
    }

    /** @test */
    public function it_can_check_if_royalty_is_overdue()
    {
        $royalty = Royalty::factory()->create([
            'status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);

        $this->assertTrue($royalty->isOverdue());
        $this->assertGreaterThan(0, $royalty->days_overdue);
    }

    /** @test */
    public function it_returns_zero_days_overdue_for_non_overdue_royalty()
    {
        $royalty = Royalty::factory()->create([
            'status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        $this->assertFalse($royalty->isOverdue());
        $this->assertEquals(0, $royalty->days_overdue);
    }

    /** @test */
    public function it_can_mark_royalty_as_paid()
    {
        $royalty = Royalty::factory()->create([
            'status' => 'pending',
            'total_amount' => 10000,
        ]);

        $royalty->markAsPaid('bank_transfer', 'REF123456');

        $this->assertEquals('paid', $royalty->status);
        $this->assertEquals('bank_transfer', $royalty->payment_method);
        $this->assertEquals('REF123456', $royalty->payment_reference);
        $this->assertNotNull($royalty->paid_date);
    }

    /** @test */
    public function it_can_add_adjustment_to_royalty()
    {
        $royalty = Royalty::factory()->create([
            'total_amount' => 10000,
            'adjustments' => 0,
        ]);

        $royalty->addAdjustment(-500, 'Discount applied');

        $this->assertEquals(-500, $royalty->adjustments);
        $this->assertEquals('Discount applied', $royalty->adjustment_notes);
        $this->assertEquals(9500, $royalty->total_amount);
    }

    /** @test */
    public function it_can_add_attachment_to_royalty()
    {
        $royalty = Royalty::factory()->create([
            'attachments' => [],
        ]);

        $royalty->addAttachment('path/to/file.pdf');

        $this->assertCount(1, $royalty->attachments);
        $this->assertEquals('path/to/file.pdf', $royalty->attachments[0]);
    }

    /** @test */
    public function it_can_calculate_royalty_amounts()
    {
        $royalty = Royalty::factory()->create([
            'gross_revenue' => 100000,
            'royalty_percentage' => 8,
            'marketing_fee_percentage' => 2,
            'technology_fee_amount' => 50,
        ]);

        $royalty->calculateRoyaltyAmounts();

        $this->assertEquals(8000, $royalty->royalty_amount);
        $this->assertEquals(2000, $royalty->marketing_fee_amount);
        $this->assertEquals(10050, $royalty->total_amount);
    }

    /** @test */
    public function it_returns_formatted_period_description_for_monthly_type()
    {
        $royalty = Royalty::factory()->create([
            'type' => 'royalty',
            'period_year' => 2024,
            'period_month' => 10,
        ]);

        $this->assertStringContainsString('October', $royalty->period_description);
        $this->assertStringContainsString('2024', $royalty->period_description);
    }

    /** @test */
    public function it_can_scope_royalties_by_status()
    {
        Royalty::factory()->create(['status' => 'paid']);
        Royalty::factory()->create(['status' => 'pending']);
        Royalty::factory()->create(['status' => 'pending']);

        $paidRoyalties = Royalty::paid()->get();
        $pendingRoyalties = Royalty::pending()->get();

        $this->assertCount(1, $paidRoyalties);
        $this->assertCount(2, $pendingRoyalties);
    }

    /** @test */
    public function it_can_scope_royalties_by_franchise()
    {
        $franchise1 = Franchise::factory()->create();
        $franchise2 = Franchise::factory()->create();

        Royalty::factory()->create(['franchise_id' => $franchise1->id]);
        Royalty::factory()->create(['franchise_id' => $franchise1->id]);
        Royalty::factory()->create(['franchise_id' => $franchise2->id]);

        $franchise1Royalties = Royalty::byFranchise($franchise1->id)->get();

        $this->assertCount(2, $franchise1Royalties);
    }

    /** @test */
    public function it_can_scope_overdue_royalties()
    {
        Royalty::factory()->create([
            'status' => 'pending',
            'due_date' => now()->subDays(5),
        ]);

        Royalty::factory()->create([
            'status' => 'pending',
            'due_date' => now()->addDays(5),
        ]);

        Royalty::factory()->create([
            'status' => 'paid',
            'due_date' => now()->subDays(5),
        ]);

        $overdueRoyalties = Royalty::overdue()->get();

        $this->assertCount(1, $overdueRoyalties);
    }

    /** @test */
    public function it_has_franchise_relationship()
    {
        $franchise = Franchise::factory()->create();
        $royalty = Royalty::factory()->create(['franchise_id' => $franchise->id]);

        $this->assertInstanceOf(Franchise::class, $royalty->franchise);
        $this->assertEquals($franchise->id, $royalty->franchise->id);
    }

    /** @test */
    public function it_has_unit_relationship()
    {
        $unit = Unit::factory()->create();
        $royalty = Royalty::factory()->create(['unit_id' => $unit->id]);

        $this->assertInstanceOf(Unit::class, $royalty->unit);
        $this->assertEquals($unit->id, $royalty->unit->id);
    }

    /** @test */
    public function it_has_franchisee_relationship()
    {
        $franchisee = User::factory()->create();
        $royalty = Royalty::factory()->create(['franchisee_id' => $franchisee->id]);

        $this->assertInstanceOf(User::class, $royalty->franchisee);
        $this->assertEquals($franchisee->id, $royalty->franchisee->id);
    }
}
