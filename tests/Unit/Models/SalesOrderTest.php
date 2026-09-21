<?php

namespace Tests\Unit\Models;

use App\Models\SalesOrder;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit — SalesOrder model
 *
 * Test types covered: Unit, Model, Business Logic
 */
class SalesOrderTest extends TestCase
{
    use RefreshDatabase;

    // ── outstanding accessor ──────────────────────────────────────────────────

    /** @test */
    public function outstanding_is_zero_when_fully_paid(): void
    {
        $order = SalesOrder::factory()->withTotal(5000)->create();

        $this->assertEquals(0.0, $order->outstanding);
    }

    /** @test */
    public function outstanding_equals_gap_between_total_and_paid(): void
    {
        $order = SalesOrder::factory()
            ->withTotal(10000)
            ->partial(4000)
            ->create();

        $this->assertEquals(6000.0, $order->outstanding);
    }

    /** @test */
    public function outstanding_never_goes_negative(): void
    {
        // amount_paid > total_amount edge case
        $order = SalesOrder::factory()->create([
            'total_amount' => 1000,
            'amount_paid'  => 1500,
        ]);

        $this->assertEquals(0.0, $order->outstanding);
    }

    /** @test */
    public function outstanding_is_full_amount_when_pending(): void
    {
        $order = SalesOrder::factory()->pending()->withTotal(8000)->create();

        $this->assertEquals(8000.0, $order->outstanding);
    }

    // ── generateNumber ────────────────────────────────────────────────────────

    /** @test */
    public function generate_number_matches_expected_format(): void
    {
        $number = SalesOrder::generateNumber();

        // Format: ASD/YY-YY/000001  e.g. ASD/26-27/000001
        $this->assertMatchesRegularExpression('/^ASD\/\d{2}-\d{2}\/\d{6}$/', $number);
    }

    /** @test */
    public function generate_number_is_sequential(): void
    {
        // Two consecutive calls must differ in the serial part
        $first  = SalesOrder::generateNumber();
        SalesOrder::factory()->create(['invoice_number' => $first]);
        $second = SalesOrder::generateNumber();

        $this->assertNotEquals($first, $second);
    }

    // ── payment_status transition ─────────────────────────────────────────────

    /** @test */
    public function factory_creates_paid_order_by_default(): void
    {
        $order = SalesOrder::factory()->create();

        $this->assertEquals('Paid', $order->payment_status);
    }

    /** @test */
    public function factory_pending_state_sets_amount_paid_to_zero(): void
    {
        $order = SalesOrder::factory()->pending()->create();

        $this->assertEquals(0.0, (float) $order->amount_paid);
        $this->assertEquals('Pending', $order->payment_status);
    }

    // ── relationships ─────────────────────────────────────────────────────────

    /** @test */
    public function transaction_relationship_resolves_correctly(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = SalesOrder::factory()->create(['transaction_id' => $txn->id]);

        $this->assertInstanceOf(Transaction::class, $order->transaction);
        $this->assertEquals($txn->id, $order->transaction->id);
    }

    /** @test */
    public function transaction_relationship_is_null_for_manual_invoice(): void
    {
        $order = SalesOrder::factory()->create(['transaction_id' => null]);

        $this->assertNull($order->transaction_id);
        $this->assertNull($order->transaction);
    }
}
