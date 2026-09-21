<?php

namespace Tests\Feature\Views;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * View / file existence tests
 *
 * Confirms every Blade template expected by the routes actually exists on disk.
 *
 * Test types covered: View/file existence tests, Smoke tests
 */
class ViewExistenceTest extends TestCase
{
    private string $viewBase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->viewBase = resource_path('views');
    }

    // ── Transactions ──────────────────────────────────────────────────────────

    /** @test */
    public function transactions_index_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/transactions/index.blade.php");
    }

    /** @test */
    public function transactions_create_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/transactions/create.blade.php");
    }

    /** @test */
    public function transactions_edit_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/transactions/edit.blade.php");
    }

    /** @test */
    public function transactions_show_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/transactions/show.blade.php");
    }

    // ── Sales / Invoices ──────────────────────────────────────────────────────

    /** @test */
    public function sales_index_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/sales/index.blade.php");
    }

    /** @test */
    public function sales_create_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/sales/create.blade.php");
    }

    /** @test */
    public function sales_edit_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/sales/edit.blade.php");
    }

    /** @test */
    public function sales_show_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/sales/show.blade.php");
    }

    // ── Finance Reports ───────────────────────────────────────────────────────

    /** @test */
    public function trial_balance_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/finance/reports/trial-balance.blade.php");
    }

    /** @test */
    public function general_ledger_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/finance/reports/general-ledger.blade.php");
    }

    /** @test */
    public function profit_loss_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/finance/reports/profit-loss.blade.php");
    }

    /** @test */
    public function balance_sheet_view_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/admin/finance/reports/balance-sheet.blade.php");
    }

    // ── Layout ────────────────────────────────────────────────────────────────

    /** @test */
    public function app_layout_exists(): void
    {
        $this->assertFileExists("{$this->viewBase}/layouts/app.blade.php");
    }

    // ── Key Service Files ─────────────────────────────────────────────────────

    /** @test */
    public function transaction_invoice_service_file_exists(): void
    {
        $this->assertFileExists(app_path('Services/TransactionInvoiceService.php'));
    }

    /** @test */
    public function sales_order_observer_file_exists(): void
    {
        $this->assertFileExists(app_path('Observers/SalesOrderObserver.php'));
    }

    /** @test */
    public function transaction_observer_file_exists(): void
    {
        $this->assertFileExists(app_path('Observers/TransactionObserver.php'));
    }

    /** @test */
    public function transaction_migration_for_sales_orders_exists(): void
    {
        $migrations = glob(database_path('migrations/*add_transaction_id_to_sales_orders*'));
        $this->assertNotEmpty($migrations, 'Migration add_transaction_id_to_sales_orders not found');
    }
}
