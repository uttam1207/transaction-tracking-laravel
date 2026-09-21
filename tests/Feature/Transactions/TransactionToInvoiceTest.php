<?php

namespace Tests\Feature\Transactions;

use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Services\TransactionInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration + Business Logic — Transaction → Auto-Invoice flow
 *
 * Test types covered: Integration, Business Logic, Regression
 */
class TransactionToInvoiceTest extends TestCase
{
    use RefreshDatabase;

    // ── Auto-invoice creation ─────────────────────────────────────────────────

    /** @test */
    public function milk_sales_credit_success_transaction_creates_sales_order(): void
    {
        $txn = Transaction::factory()->success()->milkSales()->withAmount(5000)->create();

        (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->assertDatabaseHas('sales_orders', [
            'transaction_id' => $txn->id,
        ]);
    }

    /** @test */
    public function auto_invoice_amount_equals_transaction_net_amount(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->withAmount(8750)->create();
        $order = (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->assertEquals(8750, (float) $order->total_amount);
    }

    /** @test */
    public function auto_invoice_payment_status_is_paid(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->assertEquals('Paid', $order->payment_status);
    }

    /** @test */
    public function auto_invoice_links_back_via_transaction_id(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->assertEquals($txn->id, $order->transaction_id);
        $this->assertEquals($order->id, $txn->salesOrder->id);
    }

    /** @test */
    public function debit_transaction_does_not_generate_invoice(): void
    {
        $txn = Transaction::factory()->debit()->success()->milkSales()->create();

        $should = (new TransactionInvoiceService())->shouldCreateInvoice($txn);

        $this->assertFalse($should);
        $this->assertDatabaseCount('sales_orders', 0);
    }

    /** @test */
    public function non_sales_category_does_not_generate_invoice(): void
    {
        $txn = Transaction::factory()->success()->create(['type' => 'credit', 'category' => 'salary']);

        $should = (new TransactionInvoiceService())->shouldCreateInvoice($txn);

        $this->assertFalse($should);
    }

    /** @test */
    public function pending_transaction_does_not_generate_invoice(): void
    {
        $txn = Transaction::factory()->pending()->milkSales()->create(['type' => 'credit']);

        $should = (new TransactionInvoiceService())->shouldCreateInvoice($txn);

        $this->assertFalse($should);
    }

    // ── Status change triggers invoice ────────────────────────────────────────

    /** @test */
    public function update_status_from_pending_to_success_triggers_invoice_for_milk_sales(): void
    {
        $txn = Transaction::factory()->pending()->milkSales()->create(['type' => 'credit']);

        $this->asAdmin()
             ->postJson(route('admin.transactions.status', $txn), ['status' => 'success', 'notes' => ''])
             ->assertJson(['success' => true]);

        $this->assertDatabaseHas('sales_orders', ['transaction_id' => $txn->id]);
    }

    // ── Idempotency ───────────────────────────────────────────────────────────

    /** @test */
    public function double_status_update_to_success_creates_only_one_invoice(): void
    {
        $txn = Transaction::factory()->pending()->milkSales()->create(['type' => 'credit']);

        // Update to success twice
        $this->asAdmin()->postJson(route('admin.transactions.status', $txn), ['status' => 'success', 'notes' => '']);
        $this->asAdmin()->postJson(route('admin.transactions.status', $txn), ['status' => 'success', 'notes' => '']);

        $this->assertEquals(1, SalesOrder::where('transaction_id', $txn->id)->count());
    }

    // ── View integration ──────────────────────────────────────────────────────

    /** @test */
    public function transaction_show_page_displays_linked_invoice_section(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->asAdmin()
             ->get(route('admin.transactions.show', $txn))
             ->assertSee('Auto-Generated Invoice')
             ->assertSee($order->invoice_number);
    }

    /** @test */
    public function sales_index_shows_via_txn_badge_for_auto_generated_invoice(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->asAdmin()
             ->get(route('admin.sales.index'))
             ->assertSee('Via TXN');
    }

    /** @test */
    public function sales_show_page_displays_linked_transaction_section(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->asAdmin()
             ->get(route('admin.sales.show', $order))
             ->assertSee('Linked Transaction')
             ->assertSee($txn->transaction_id);
    }

    // ── Animal Sales category ─────────────────────────────────────────────────

    /** @test */
    public function animal_sales_credit_success_also_creates_invoice(): void
    {
        $txn = Transaction::factory()->success()->create([
            'type'     => 'credit',
            'category' => 'Animal Sales',
        ]);

        $order = (new TransactionInvoiceService())->createFromTransaction($txn);

        $this->assertEquals('Animal Sales', $order->item_type);
    }
}
