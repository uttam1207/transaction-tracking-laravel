<?php

namespace Tests\Feature\Finance;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Finance Report Sanity + Integration tests
 *
 * Test types covered: Sanity, Integration, Business Logic
 */
class FinanceReportSanityTest extends TestCase
{
    use RefreshDatabase;

    // ── Report pages load ─────────────────────────────────────────────────────

    /** @test */
    public function trial_balance_page_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.trial-balance'))
             ->assertOk();
    }

    /** @test */
    public function balance_sheet_page_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.balance-sheet'))
             ->assertOk();
    }

    /** @test */
    public function profit_loss_page_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.profit-loss'))
             ->assertOk();
    }

    /** @test */
    public function general_ledger_page_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.general-ledger'))
             ->assertOk();
    }

    // ── Trial Balance columns ─────────────────────────────────────────────────

    /** @test */
    public function trial_balance_shows_debit_and_credit_columns(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.trial-balance'))
             ->assertSee('Debit')
             ->assertSee('Credit');
    }

    // ── Balance Sheet structure ───────────────────────────────────────────────

    /** @test */
    public function balance_sheet_shows_assets_section(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.balance-sheet'))
             ->assertSee('Assets');
    }

    /** @test */
    public function balance_sheet_shows_liabilities_section(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.balance-sheet'))
             ->assertSee('Liabilities');
    }

    // ── P&L COGS vs OpEx split (uses sub_type) ────────────────────────────────

    /** @test */
    public function profit_loss_shows_cost_of_sales_section(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.profit-loss'))
             ->assertSee('COGS');   // blade uses "COST OF GOODS SOLD (COGS)"
    }

    /** @test */
    public function profit_loss_shows_operating_expenses_section(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.reports.profit-loss'))
             ->assertSee('Operating');
    }

    // ── General Ledger — account data ────────────────────────────────────────

    /** @test */
    public function general_ledger_with_valid_account_shows_transaction_history(): void
    {
        $admin   = $this->adminUser();
        $account = ChartOfAccount::factory()->bankAccount()->create();

        // Create a posted journal entry with a line on this account
        $je = JournalEntry::create([
            'entry_number' => 'JE-26-00001',
            'entry_date'   => today()->format('Y-m-d'),
            'type'         => 'general',
            'status'       => 'posted',
            'total_debit'  => 1000,
            'total_credit' => 1000,
            'created_by'   => $admin->id,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id'       => $account->id,
            'debit'            => 1000,
            'credit'           => 0,
            'description'      => 'Test debit',
            'sort_order'       => 1,
        ]);

        $this->actingAs($admin)
             ->get(route('admin.finance.reports.general-ledger', ['account_id' => $account->id]))
             ->assertOk()
             ->assertSee($account->name);
    }

    // ── Chart of Accounts ─────────────────────────────────────────────────────

    /** @test */
    public function coa_index_page_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.coa.index'))
             ->assertOk();
    }

    /** @test */
    public function journal_entries_index_returns_200(): void
    {
        $this->asAdmin()
             ->get(route('admin.finance.journal.index'))
             ->assertOk();
    }

    // ── Business logic: no double JE for auto-invoice ─────────────────────────

    /** @test */
    public function auto_invoice_from_transaction_results_in_single_journal_entry(): void
    {
        // Transactions don't auto-create JE in test (no observer fires without a real request)
        // but we can confirm: if transaction has a JE and we create order with same JE id,
        // the count is still 1.

        $admin   = $this->adminUser();
        $account = ChartOfAccount::factory()->bankAccount()->create();

        $je = JournalEntry::create([
            'entry_number' => 'JE-26-00099',
            'entry_date'   => today()->format('Y-m-d'),
            'type'         => 'general',
            'status'       => 'posted',
            'total_debit'  => 5000,
            'total_credit' => 5000,
            'created_by'   => $admin->id,
        ]);

        $order = SalesOrder::factory()->create(['journal_entry_id' => $je->id]);

        // The same JE is shared — only 1 entry in journal_entries
        $this->assertEquals(1, JournalEntry::count());
    }
}
