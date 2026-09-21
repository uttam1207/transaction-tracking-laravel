<?php

namespace Tests\Unit\Models;

use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit — Transaction model
 *
 * Test types covered: Unit, Model, Business Logic
 */
class TransactionTest extends TestCase
{
    use RefreshDatabase;

    // ── risk_level accessor ───────────────────────────────────────────────────

    /** @test */
    public function risk_level_is_low_for_score_below_40(): void
    {
        $txn = Transaction::factory()->create(['risk_score' => 30]);
        $this->assertEquals('low', $txn->risk_level);
    }

    /** @test */
    public function risk_level_is_medium_for_score_40_to_59(): void
    {
        $txn = Transaction::factory()->create(['risk_score' => 50]);
        $this->assertEquals('medium', $txn->risk_level);
    }

    /** @test */
    public function risk_level_is_high_for_score_60_to_79(): void
    {
        $txn = Transaction::factory()->create(['risk_score' => 65]);
        $this->assertEquals('high', $txn->risk_level);
    }

    /** @test */
    public function risk_level_is_critical_for_score_80_and_above(): void
    {
        $txn = Transaction::factory()->create(['risk_score' => 85]);
        $this->assertEquals('critical', $txn->risk_level);
    }

    // ── is_posted_to_ledger accessor ──────────────────────────────────────────

    /** @test */
    public function is_posted_to_ledger_is_false_when_no_journal_entry(): void
    {
        $txn = Transaction::factory()->create(['journal_entry_id' => null]);
        $this->assertFalse($txn->is_posted_to_ledger);
    }

    /** @test */
    public function is_posted_to_ledger_is_true_when_journal_entry_set(): void
    {
        // Set the attribute in-memory only (no FK required) to test the accessor
        $txn = Transaction::factory()->make(['journal_entry_id' => 99]);
        $this->assertTrue($txn->is_posted_to_ledger);
    }

    // ── status_badge accessor ─────────────────────────────────────────────────

    /** @test */
    public function status_badge_returns_correct_bootstrap_color_per_status(): void
    {
        $map = [
            'success'    => 'success',
            'pending'    => 'warning',
            'processing' => 'info',
            'failed'     => 'danger',
            'cancelled'  => 'secondary',
            'reversed'   => 'dark',
        ];

        foreach ($map as $status => $expected) {
            $txn = Transaction::factory()->create(['status' => $status]);
            $this->assertEquals($expected, $txn->status_badge, "Status '$status' should map to '$expected'");
        }
    }

    // ── net_amount boot calculation ───────────────────────────────────────────

    /** @test */
    public function net_amount_is_auto_calculated_on_create(): void
    {
        $txn = Transaction::factory()->create(['amount' => 5000, 'fee' => 50]);

        $this->assertEquals(4950, (float) $txn->net_amount);
    }

    /** @test */
    public function net_amount_equals_amount_when_fee_is_zero(): void
    {
        $txn = Transaction::factory()->create(['amount' => 3000, 'fee' => 0]);

        $this->assertEquals(3000, (float) $txn->net_amount);
    }

    // ── transaction_id auto-generation ───────────────────────────────────────

    /** @test */
    public function transaction_id_is_auto_generated_with_txn_prefix(): void
    {
        $txn = Transaction::factory()->create();

        $this->assertStringStartsWith('TXN-', $txn->transaction_id);
    }

    // ── salesOrder relationship ───────────────────────────────────────────────

    /** @test */
    public function sales_order_relationship_returns_linked_invoice(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = SalesOrder::factory()->create(['transaction_id' => $txn->id]);

        $this->assertInstanceOf(SalesOrder::class, $txn->salesOrder);
        $this->assertEquals($order->id, $txn->salesOrder->id);
    }

    /** @test */
    public function sales_order_relationship_is_null_when_not_linked(): void
    {
        $txn = Transaction::factory()->create();

        $this->assertNull($txn->salesOrder);
    }

    // ── scopes ────────────────────────────────────────────────────────────────

    /** @test */
    public function flagged_scope_returns_only_flagged_transactions(): void
    {
        Transaction::factory()->create(['is_flagged' => false]);
        Transaction::factory()->create(['is_flagged' => true]);

        $this->assertEquals(1, Transaction::flagged()->count());
    }

    /** @test */
    public function high_risk_scope_returns_transactions_above_threshold(): void
    {
        Transaction::factory()->create(['risk_score' => 50]);
        Transaction::factory()->create(['risk_score' => 80]);

        $this->assertEquals(1, Transaction::highRisk()->count());
    }
}
