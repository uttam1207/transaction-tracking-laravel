<?php

namespace Tests\Feature\Sales;

use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Payment Status + Business Logic tests for Sales Orders
 *
 * Test types covered: Business Logic, Regression, Validation
 */
class SalesPaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    // ── Outstanding calculation ───────────────────────────────────────────────

    /** @test */
    public function paid_invoice_has_zero_outstanding(): void
    {
        $order = SalesOrder::factory()->withTotal(10000)->create([
            'payment_status' => 'Paid',
            'amount_paid'    => 10000,
        ]);

        $this->assertEquals(0.0, $order->outstanding);
    }

    /** @test */
    public function partial_payment_outstanding_equals_remaining_balance(): void
    {
        $order = SalesOrder::factory()->withTotal(10000)->partial(3000)->create();

        $this->assertEquals(7000.0, $order->outstanding);
    }

    /** @test */
    public function partial_payment_capped_at_total_amount(): void
    {
        // Overpayment edge case — outstanding must not be negative
        $order = SalesOrder::factory()->create([
            'total_amount'   => 5000,
            'amount_paid'    => 7000,
            'payment_status' => 'Paid',
        ]);

        $this->assertEquals(0.0, $order->outstanding);
    }

    /** @test */
    public function pending_invoice_shows_full_amount_as_outstanding(): void
    {
        $order = SalesOrder::factory()->pending()->withTotal(6000)->create();

        $this->assertEquals(6000.0, $order->outstanding);
    }

    /** @test */
    public function unbilled_invoice_has_zero_amount_paid(): void
    {
        $order = SalesOrder::factory()->unbilled()->create();

        $this->assertEquals(0.0, (float) $order->amount_paid);
        $this->assertEquals('Unbilled', $order->payment_status);
    }

    // ── Show page contains outstanding ────────────────────────────────────────

    /** @test */
    public function show_page_displays_outstanding_amount_for_partial_invoice(): void
    {
        $order = SalesOrder::factory()->withTotal(5000)->partial(2000)->create();

        $this->asAdmin()
             ->get(route('admin.sales.show', $order))
             ->assertOk()
             ->assertSee('3,000');   // ₹3,000 outstanding
    }

    /** @test */
    public function show_page_shows_fully_settled_for_paid_invoice(): void
    {
        $order = SalesOrder::factory()->withTotal(5000)->create();

        $this->asAdmin()
             ->get(route('admin.sales.show', $order))
             ->assertOk()
             ->assertSee('Fully Settled');
    }

    // ── Overdue filter ────────────────────────────────────────────────────────

    /** @test */
    public function overdue_invoices_appear_in_index_with_overdue_badge(): void
    {
        SalesOrder::factory()->pending()->withTotal(9000)->create([
            'due_date' => now()->subDays(10)->format('Y-m-d'),
        ]);

        $this->asAdmin()
             ->get(route('admin.sales.index'))
             ->assertSee('OVERDUE');
    }

    // ── Payment status transitions ────────────────────────────────────────────

    /** @test */
    public function payment_status_enum_allows_all_valid_values(): void
    {
        foreach (['Paid', 'Pending', 'Partial', 'Unbilled'] as $status) {
            $order = SalesOrder::factory()->create(['payment_status' => $status]);
            $this->assertEquals($status, $order->fresh()->payment_status);
        }
    }
}
