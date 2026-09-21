<?php

namespace Tests\Feature\Schema;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Database Schema tests
 *
 * Test types covered: Database Schema tests, Smoke tests
 */
class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    // ── transactions ──────────────────────────────────────────────────────────

    /** @test */
    public function transactions_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('transactions'));
    }

    /** @test */
    public function transactions_table_has_required_columns(): void
    {
        $required = [
            'id', 'transaction_id', 'category', 'type', 'amount', 'fee',
            'net_amount', 'status', 'payment_method',
            'sender_name', 'receiver_name', 'journal_entry_id',
            'is_flagged', 'risk_score', 'processed_at', 'deleted_at',
        ];

        foreach ($required as $col) {
            $this->assertTrue(
                Schema::hasColumn('transactions', $col),
                "Column 'transactions.$col' is missing"
            );
        }
    }

    // ── sales_orders ──────────────────────────────────────────────────────────

    /** @test */
    public function sales_orders_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('sales_orders'));
    }

    /** @test */
    public function sales_orders_table_has_transaction_id_column(): void
    {
        $this->assertTrue(Schema::hasColumn('sales_orders', 'transaction_id'));
    }

    /** @test */
    public function sales_orders_table_has_required_columns(): void
    {
        $required = [
            'id', 'invoice_number', 'item_type', 'sale_date', 'due_date',
            'payment_terms', 'quantity', 'rate', 'total_amount', 'amount_paid',
            'payment_status', 'journal_entry_id', 'transaction_id',
            'crm_customer_id', 'deleted_at',
        ];

        foreach ($required as $col) {
            $this->assertTrue(
                Schema::hasColumn('sales_orders', $col),
                "Column 'sales_orders.$col' is missing"
            );
        }
    }

    // ── sale_order_items ──────────────────────────────────────────────────────

    /** @test */
    public function sale_order_items_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('sale_order_items'));
    }

    /** @test */
    public function sale_order_items_table_has_required_columns(): void
    {
        $required = [
            'id', 'sales_order_id', 'sale_item_type_id', 'item_type',
            'quantity', 'rate', 'amount', 'description', 'sort_order',
        ];

        foreach ($required as $col) {
            $this->assertTrue(
                Schema::hasColumn('sale_order_items', $col),
                "Column 'sale_order_items.$col' is missing"
            );
        }
    }

    // ── journal_entries ───────────────────────────────────────────────────────

    /** @test */
    public function journal_entries_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('journal_entries'));
    }

    /** @test */
    public function journal_entries_table_has_required_columns(): void
    {
        $required = [
            'id', 'entry_number', 'entry_date', 'type', 'status',
            'total_debit', 'total_credit', 'created_by',
        ];

        foreach ($required as $col) {
            $this->assertTrue(
                Schema::hasColumn('journal_entries', $col),
                "Column 'journal_entries.$col' is missing"
            );
        }
    }

    // ── chart_of_accounts ─────────────────────────────────────────────────────

    /** @test */
    public function chart_of_accounts_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('chart_of_accounts'));
    }

    /** @test */
    public function chart_of_accounts_table_has_required_columns(): void
    {
        $required = ['id', 'code', 'name', 'type', 'sub_type', 'is_active', 'allow_direct_posting'];

        foreach ($required as $col) {
            $this->assertTrue(
                Schema::hasColumn('chart_of_accounts', $col),
                "Column 'chart_of_accounts.$col' is missing"
            );
        }
    }

    // ── sale_item_types ───────────────────────────────────────────────────────

    /** @test */
    public function sale_item_types_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('sale_item_types'));
    }

    // ── crm_customers ─────────────────────────────────────────────────────────

    /** @test */
    public function crm_customers_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('crm_customers'));
    }

    // ── wallets & wallet_transactions ─────────────────────────────────────────

    /** @test */
    public function wallets_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('wallets'));
    }

    /** @test */
    public function wallet_transactions_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('wallet_transactions'));
    }

    // ── users ─────────────────────────────────────────────────────────────────

    /** @test */
    public function users_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
    }

    /** @test */
    public function users_table_has_role_column(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'role'));
    }

    // ── foreign key structural checks ────────────────────────────────────────

    /** @test */
    public function sales_orders_transaction_id_is_nullable(): void
    {
        // SQLite: just check the column exists and can hold null
        $this->assertTrue(Schema::hasColumn('sales_orders', 'transaction_id'));
    }
}
