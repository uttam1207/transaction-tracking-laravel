<?php

namespace Tests\Feature\Routes;

use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke + Route tests
 *
 * Verifies every key URL returns an HTTP 200 (or appropriate redirect) for an
 * authenticated super_admin user. No deep assertion — just ensure the app
 * boots, the route resolves, and the controller doesn't crash.
 *
 * Test types covered: Smoke tests, Route tests
 */
class RouteTest extends TestCase
{
    use RefreshDatabase;

    // ── Transactions ──────────────────────────────────────────────────────────

    /** @test */
    public function transactions_index_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.transactions.index'))
             ->assertOk();
    }

    /** @test */
    public function transactions_create_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.transactions.create'))
             ->assertOk();
    }

    /** @test */
    public function transactions_show_returns_200(): void
    {
        $txn = Transaction::factory()->create();

        $this->asAdmin()
             ->get(route('admin.transactions.show', $txn))
             ->assertOk();
    }

    /** @test */
    public function transactions_edit_returns_200(): void
    {
        $txn = Transaction::factory()->create();

        $this->asAdmin()
             ->get(route('admin.transactions.edit', $txn))
             ->assertOk();
    }

    /** @test */
    public function transactions_trash_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.transactions.trash'))
             ->assertOk();
    }

    // ── Sales / Invoices ──────────────────────────────────────────────────────

    /** @test */
    public function sales_index_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.sales.index'))
             ->assertOk();
    }

    /** @test */
    public function sales_create_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.sales.create'))
             ->assertOk();
    }

    /** @test */
    public function sales_show_returns_200(): void
    {
        $order = SalesOrder::factory()->create();

        $this->asAdmin()
             ->get(route('admin.sales.show', $order))
             ->assertOk();
    }

    /** @test */
    public function sales_edit_returns_200(): void
    {
        $order = SalesOrder::factory()->create();

        $this->asAdmin()
             ->get(route('admin.sales.edit', $order))
             ->assertOk();
    }

    /** @test */
    public function sales_trash_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.sales.trash'))
             ->assertOk();
    }

    // ── Finance Reports ───────────────────────────────────────────────────────

    /** @test */
    public function trial_balance_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.trial-balance'))
             ->assertOk();
    }

    /** @test */
    public function general_ledger_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.general-ledger'))
             ->assertOk();
    }

    /** @test */
    public function profit_loss_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.profit-loss'))
             ->assertOk();
    }

    /** @test */
    public function balance_sheet_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.balance-sheet'))
             ->assertOk();
    }

    /** @test */
    public function journal_index_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.journal.index'))
             ->assertOk();
    }

    /** @test */
    public function coa_index_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.coa.index'))
             ->assertOk();
    }

    // ── Unauthenticated redirects ─────────────────────────────────────────────

    /** @test */
    public function unauthenticated_user_is_redirected_from_transactions(): void
    {
        $this->get(route('admin.transactions.index'))
             ->assertRedirect();
    }

    /** @test */
    public function unauthenticated_user_is_redirected_from_sales(): void
    {
        $this->get(route('admin.sales.index'))
             ->assertRedirect();
    }

    /** @test */
    public function dashboard_returns_200_for_admin(): void
    {
        $this->asAdmin()
             ->get(route('admin.dashboard'))
             ->assertOk();
    }
}
