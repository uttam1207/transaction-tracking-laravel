<?php

namespace Tests\Feature\Sales;

use App\Models\SaleItemType;
use App\Models\SalesOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validation tests for Sales Invoice store/update
 *
 * Test types covered: Validation tests
 */
class SalesValidationTest extends TestCase
{
    use RefreshDatabase;

    private SaleItemType $itemType;

    protected function setUp(): void
    {
        parent::setUp();
        // Use 'Animal Sales' (is_milk_type=false) so qty×rate calculation works
        $this->itemType = SaleItemType::firstOrCreate(
            ['name' => 'Animal Sales'],
            ['is_milk_type' => false, 'sort_order' => 2, 'is_active' => true]
        );
    }

    // ── Required field validation ─────────────────────────────────────────────

    /** @test */
    public function store_fails_when_sale_date_is_missing(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'payment_status' => 'Paid',
                 'items'          => [['sale_item_type_id' => $this->itemType->id,
                                       'item_type' => 'Animal Sales', 'quantity' => 1,
                                       'rate' => 100, 'amount' => 100, 'description' => '']],
             ])
             ->assertSessionHasErrors('sale_date');
    }

    /** @test */
    public function store_fails_when_items_array_is_empty(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'sale_date'      => today()->format('Y-m-d'),
                 'payment_status' => 'Pending',
                 'items'          => [],
             ])
             ->assertSessionHasErrors('items');
    }

    /** @test */
    public function store_fails_when_item_rate_is_zero_or_negative(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'sale_date'      => today()->format('Y-m-d'),
                 'payment_status' => 'Pending',
                 'items'          => [['sale_item_type_id' => $this->itemType->id,
                                       'item_type' => 'Animal Sales', 'quantity' => 1,
                                       'rate' => 0, 'amount' => 0, 'description' => '']],
             ])
             ->assertSessionHasErrors();
    }

    /** @test */
    public function store_fails_when_item_quantity_is_zero_or_negative(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'sale_date'      => today()->format('Y-m-d'),
                 'payment_status' => 'Pending',
                 'items'          => [['sale_item_type_id' => $this->itemType->id,
                                       'item_type' => 'Animal Sales', 'quantity' => -1,
                                       'rate' => 50, 'amount' => -50, 'description' => '']],
             ])
             ->assertSessionHasErrors();
    }

    /** @test */
    public function store_fails_when_sale_date_is_invalid(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'sale_date'      => 'not-a-date',
                 'payment_status' => 'Paid',
                 'items'          => [['sale_item_type_id' => $this->itemType->id,
                                       'item_type' => 'Animal Sales', 'quantity' => 1,
                                       'rate' => 100, 'amount' => 100, 'description' => '']],
             ])
             ->assertSessionHasErrors('sale_date');
    }

    /** @test */
    public function store_fails_when_payment_status_is_invalid(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'sale_date'      => today()->format('Y-m-d'),
                 'payment_status' => 'INVALID_STATUS',
                 'items'          => [['sale_item_type_id' => $this->itemType->id,
                                       'item_type' => 'Animal Sales', 'quantity' => 1,
                                       'rate' => 100, 'amount' => 100, 'description' => '']],
             ])
             ->assertSessionHasErrors('payment_status');
    }

    // ── Valid store passes ────────────────────────────────────────────────────

    /** @test */
    public function store_succeeds_with_valid_payload(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'invoice_number' => \App\Models\SalesOrder::generateNumber(),
                 'sale_date'      => today()->format('Y-m-d'),
                 'payment_status' => 'Paid',
                 'items'          => [['sale_item_type_id' => $this->itemType->id,
                                       'item_type' => 'Animal Sales', 'quantity' => 10,
                                       'rate' => 50, 'amount' => 500, 'description' => 'Valid']],
             ])
             ->assertRedirect();

        $this->assertDatabaseCount('sales_orders', 1);
    }

    // ── Amount paid validation ────────────────────────────────────────────────

    /** @test */
    public function amount_paid_cannot_exceed_total_amount_on_partial(): void
    {
        $this->asAdmin()
             ->post(route('admin.sales.store'), [
                 'sale_date'      => today()->format('Y-m-d'),
                 'payment_status' => 'Partial',
                 'amount_paid'    => 9999999,   // way over any reasonable total
                 'items'          => [['sale_item_type_id' => $this->itemType->id,
                                       'item_type' => 'Animal Sales', 'quantity' => 1,
                                       'rate' => 100, 'amount' => 100, 'description' => '']],
             ])
             ->assertSessionHasErrors();
    }
}
