<?php

namespace Tests\Feature\Sales;

use App\Models\SaleItemType;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CRUD + Regression tests for Sales Invoices
 *
 * Test types covered: Integration, Controller, Regression, Sanity
 */
class SalesInvoiceCrudTest extends TestCase
{
    use RefreshDatabase;

    private SaleItemType $itemType;

    protected function setUp(): void
    {
        parent::setUp();
        // Use 'Animal Sales' (is_milk_type=false) so qty×rate amount calculation works in tests.
        // 'Milk Sales' uses fat×fat_rate which requires extra fields.
        $this->itemType = SaleItemType::firstOrCreate(
            ['name' => 'Animal Sales'],
            ['is_milk_type' => false, 'sort_order' => 2, 'is_active' => true]
        );
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    /** @test */
    public function sales_index_loads_successfully(): void
    {
        $this->asAdmin()->get(route('admin.sales.index'))->assertOk();
    }

    /** @test */
    public function sales_index_shows_invoice_numbers(): void
    {
        SalesOrder::factory()->withItemType('Milk Sales')->create([
            'invoice_number' => 'ASD/26-27/000001',
        ]);

        $this->asAdmin()
             ->get(route('admin.sales.index'))
             ->assertSee('ASD/26-27/000001');
    }

    // ── Create / Show ─────────────────────────────────────────────────────────

    /** @test */
    public function sales_create_page_loads(): void
    {
        $this->asAdmin()->get(route('admin.sales.create'))->assertOk();
    }

    /** @test */
    public function sales_show_page_loads(): void
    {
        $order = SalesOrder::factory()->create();

        $this->asAdmin()->get(route('admin.sales.show', $order))->assertOk();
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    /** @test */
    public function store_creates_invoice_and_saves_to_database(): void
    {
        $payload = [
            'invoice_number' => SalesOrder::generateNumber(),
            'sale_date'      => today()->format('Y-m-d'),
            'payment_status' => 'Paid',
            'items'          => [
                [
                    'sale_item_type_id' => $this->itemType->id,
                    'item_type'         => 'Animal Sales',
                    'quantity'          => 100,
                    'rate'              => 50,
                    'amount'            => 5000,
                    'description'       => 'Test animal sale',
                ],
            ],
        ];

        $this->asAdmin()
             ->post(route('admin.sales.store'), $payload)
             ->assertRedirect();

        $this->assertDatabaseHas('sales_orders', [
            'payment_status' => 'Paid',
            'total_amount'   => 5000,
        ]);
    }

    /** @test */
    public function store_requires_at_least_one_item(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'sale_date'      => today()->format('Y-m-d'),
                 'payment_status' => 'Pending',
                 'items'          => [],
             ])
             ->assertSessionHasErrors();
    }

    // ── Update ────────────────────────────────────────────────────────────────

    /** @test */
    public function update_changes_invoice_fields_in_database(): void
    {
        $order = SalesOrder::factory()->pending()->create();

        $payload = [
            'invoice_number' => $order->invoice_number,
            'sale_date'      => today()->format('Y-m-d'),
            'payment_status' => 'Paid',
            'amount_paid'    => $order->total_amount,
            'items'          => [
                [
                    'sale_item_type_id' => $this->itemType->id,
                    'item_type'         => 'Animal Sales',
                    'quantity'          => 1,
                    'rate'              => (float) $order->total_amount,
                    'amount'            => (float) $order->total_amount,
                    'description'       => 'Updated',
                ],
            ],
        ];

        $this->asAdmin()
             ->put(route('admin.sales.update', $order), $payload)
             ->assertRedirect();

        $this->assertDatabaseHas('sales_orders', [
            'id'             => $order->id,
            'payment_status' => 'Paid',
        ]);
    }

    /** @test */
    public function update_keeps_same_invoice_number(): void
    {
        $order       = SalesOrder::factory()->create();
        $origNumber  = $order->invoice_number;

        $this->asAdmin()->put(route('admin.sales.update', $order), [
            'invoice_number' => $order->invoice_number,
            'sale_date'      => today()->format('Y-m-d'),
            'payment_status' => 'Paid',
            'items'          => [[
                'sale_item_type_id' => $this->itemType->id,
                'item_type'         => 'Animal Sales',
                'quantity'          => 1,
                'rate'              => 100,
                'amount'            => 100,
                'description'       => '',
            ]],
        ]);

        $this->assertDatabaseHas('sales_orders', [
            'id'             => $order->id,
            'invoice_number' => $origNumber,
        ]);
    }

    // ── Soft Delete / Trash / Restore ─────────────────────────────────────────

    /** @test */
    public function delete_soft_deletes_the_invoice(): void
    {
        $order = SalesOrder::factory()->create();

        $this->asAdmin()
             ->delete(route('admin.sales.destroy', $order))
             ->assertRedirect();

        $this->assertSoftDeleted('sales_orders', ['id' => $order->id]);
    }

    /** @test */
    public function trash_page_returns_200(): void
    {
        $this->asAdmin()->get(route('admin.sales.trash'))->assertOk();
    }

    /** @test */
    public function restore_recovers_soft_deleted_invoice(): void
    {
        $order = SalesOrder::factory()->create();
        $order->delete();

        $this->asAdmin()
             ->post(route('admin.sales.restore', $order->id))
             ->assertRedirect();

        $this->assertDatabaseHas('sales_orders', ['id' => $order->id, 'deleted_at' => null]);
    }
}
