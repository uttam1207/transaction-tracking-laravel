<?php

namespace Tests\Unit\Services;

use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Services\TransactionInvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit + Business Logic — TransactionInvoiceService
 *
 * Test types covered: Unit, Business Logic, Integration (shallow)
 */
class TransactionInvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionInvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransactionInvoiceService();
    }

    // ── shouldCreateInvoice ───────────────────────────────────────────────────

    /** @test */
    public function should_create_invoice_is_true_for_success_credit_sales_category(): void
    {
        $txn = Transaction::factory()->success()->milkSales()->create();

        $this->assertTrue($this->service->shouldCreateInvoice($txn));
    }

    /** @test */
    public function should_create_invoice_is_false_when_status_is_pending(): void
    {
        $txn = Transaction::factory()->pending()->milkSales()->create();

        $this->assertFalse($this->service->shouldCreateInvoice($txn));
    }

    /** @test */
    public function should_create_invoice_is_false_for_debit_type(): void
    {
        $txn = Transaction::factory()->debit()->success()->milkSales()->create();

        $this->assertFalse($this->service->shouldCreateInvoice($txn));
    }

    /** @test */
    public function should_create_invoice_is_false_for_non_sales_category(): void
    {
        $txn = Transaction::factory()->success()->create(['category' => 'salary', 'type' => 'credit']);

        $this->assertFalse($this->service->shouldCreateInvoice($txn));
    }

    /** @test */
    public function should_create_invoice_is_false_when_sales_order_already_linked(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        SalesOrder::factory()->create(['transaction_id' => $txn->id]);

        $this->assertFalse($this->service->shouldCreateInvoice($txn));
    }

    /** @test */
    public function should_create_invoice_covers_all_six_sales_categories(): void
    {
        $categories = TransactionInvoiceService::SALES_CATEGORIES;
        $this->assertCount(6, $categories);

        foreach ($categories as $cat) {
            $txn = Transaction::factory()->success()->create([
                'type'     => 'credit',
                'category' => $cat,
            ]);
            $this->assertTrue(
                $this->service->shouldCreateInvoice($txn),
                "shouldCreateInvoice should be true for category: $cat"
            );
        }
    }

    /** @test */
    public function should_create_invoice_is_false_when_status_is_failed(): void
    {
        $txn = Transaction::factory()->milkSales()->create(['status' => 'failed', 'type' => 'credit']);

        $this->assertFalse($this->service->shouldCreateInvoice($txn));
    }

    // ── createFromTransaction ─────────────────────────────────────────────────

    /** @test */
    public function create_from_transaction_sets_correct_total_amount(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->withAmount(7500)->create();
        $order = $this->service->createFromTransaction($txn);

        $this->assertEquals(7500, (float) $order->total_amount);
    }

    /** @test */
    public function create_from_transaction_sets_transaction_id(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = $this->service->createFromTransaction($txn);

        $this->assertEquals($txn->id, $order->transaction_id);
    }

    /** @test */
    public function create_from_transaction_sets_payment_status_paid(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = $this->service->createFromTransaction($txn);

        $this->assertEquals('Paid', $order->payment_status);
    }

    /** @test */
    public function create_from_transaction_creates_exactly_one_line_item(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = $this->service->createFromTransaction($txn);

        $this->assertEquals(1, $order->items()->count());
    }

    /** @test */
    public function create_from_transaction_line_item_amount_matches_net_amount(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->withAmount(9999)->create();
        $order = $this->service->createFromTransaction($txn);

        $item = $order->items()->first();
        $this->assertEquals(9999, (float) $item->amount);
    }

    /** @test */
    public function create_from_transaction_sets_correct_invoice_number_format(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->create();
        $order = $this->service->createFromTransaction($txn);

        $this->assertMatchesRegularExpression('/^ASD\/\d{2}-\d{2}\/\d{6}$/', $order->invoice_number);
    }

    /** @test */
    public function create_from_transaction_sets_amount_paid_equal_to_total(): void
    {
        $txn   = Transaction::factory()->success()->milkSales()->withAmount(3300)->create();
        $order = $this->service->createFromTransaction($txn);

        $this->assertEquals((float) $order->total_amount, (float) $order->amount_paid);
        $this->assertEquals(0.0, $order->outstanding);
    }

    /** @test */
    public function create_from_transaction_stores_record_in_database(): void
    {
        $txn = Transaction::factory()->success()->milkSales()->create();
        $this->service->createFromTransaction($txn);

        $this->assertDatabaseHas('sales_orders', [
            'transaction_id' => $txn->id,
            'payment_status' => 'Paid',
        ]);
    }
}
