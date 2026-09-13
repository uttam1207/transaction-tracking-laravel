<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExpenseExport;
use App\Exports\JournalEntryExport;
use App\Exports\PurchaseOrderExport;
use App\Exports\SalesOrderExport;
use App\Exports\TransactionExport;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\MilkEntry;
use App\Models\Animal;
use App\Models\BreedingRecord;
use App\Models\HealthRecord;
use App\Services\LedgerBalanceService;
use App\Services\FeedCalculationService;
use App\Models\ChartOfAccount;
use App\Models\FinancialPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function __construct(private LedgerBalanceService $ledger) {}

    // ── Sales Orders ──────────────────────────────────────────────────────────

    public function sales(Request $request, string $format)
    {
        $params = $request->only(['date_from', 'date_to', 'status', 'item_type']);

        if ($format === 'excel') {
            return Excel::download(
                new SalesOrderExport(
                    $params['date_from'] ?? null,
                    $params['date_to']   ?? null,
                    $params['status']    ?? null,
                    $params['item_type'] ?? null,
                ),
                'sales-invoices-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $q = SalesOrder::with('customer', 'saleItemType')->latest('sale_date');
        if ($params['date_from'] ?? null) $q->whereDate('sale_date', '>=', $params['date_from']);
        if ($params['date_to']   ?? null) $q->whereDate('sale_date', '<=', $params['date_to']);
        if ($params['status']    ?? null) $q->where('payment_status', $params['status']);
        if ($params['item_type'] ?? null) $q->where('item_type', $params['item_type']);
        $records = $q->get();

        $pdf = Pdf::loadView('exports.sales', compact('records', 'params'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('sales-invoices-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── Purchase Orders ───────────────────────────────────────────────────────

    public function procurement(Request $request, string $format)
    {
        $params = $request->only(['date_from', 'date_to', 'status']);

        if ($format === 'excel') {
            return Excel::download(
                new PurchaseOrderExport(
                    $params['date_from'] ?? null,
                    $params['date_to']   ?? null,
                    $params['status']    ?? null,
                ),
                'purchase-orders-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $q = PurchaseOrder::with('vendor')->latest('order_date');
        if ($params['date_from'] ?? null) $q->whereDate('order_date', '>=', $params['date_from']);
        if ($params['date_to']   ?? null) $q->whereDate('order_date', '<=', $params['date_to']);
        if ($params['status']    ?? null) $q->where('status', $params['status']);
        $records = $q->get();

        $pdf = Pdf::loadView('exports.procurement', compact('records', 'params'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('purchase-orders-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── Transactions ──────────────────────────────────────────────────────────

    public function transactions(Request $request, string $format)
    {
        $params = $request->only(['date_from', 'date_to', 'type', 'status', 'category']);

        if ($format === 'excel') {
            return Excel::download(
                new TransactionExport(
                    $params['date_from'] ?? null,
                    $params['date_to']   ?? null,
                    $params['type']      ?? null,
                    $params['status']    ?? null,
                    $params['category']  ?? null,
                ),
                'transactions-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $q = Transaction::latest('created_at');
        if ($params['date_from'] ?? null) $q->whereDate('created_at', '>=', $params['date_from']);
        if ($params['date_to']   ?? null) $q->whereDate('created_at', '<=', $params['date_to']);
        if ($params['type']      ?? null) $q->where('type', $params['type']);
        if ($params['status']    ?? null) $q->where('status', $params['status']);
        if ($params['category']  ?? null) $q->where('category', $params['category']);
        $records = $q->get();

        $pdf = Pdf::loadView('exports.transactions', compact('records', 'params'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('transactions-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── Expenses ──────────────────────────────────────────────────────────────

    public function expenses(Request $request, string $format)
    {
        $params = $request->only(['date_from', 'date_to', 'status', 'category_id']);

        if ($format === 'excel') {
            return Excel::download(
                new ExpenseExport(
                    $params['date_from']   ?? null,
                    $params['date_to']     ?? null,
                    $params['status']      ?? null,
                    isset($params['category_id']) ? (int) $params['category_id'] : null,
                ),
                'expenses-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $q = Expense::with('category', 'approvedBy')->latest('expense_date');
        if ($params['date_from']   ?? null) $q->whereDate('expense_date', '>=', $params['date_from']);
        if ($params['date_to']     ?? null) $q->whereDate('expense_date', '<=', $params['date_to']);
        if ($params['status']      ?? null) $q->where('payment_status', $params['status']);
        if ($params['category_id'] ?? null) $q->where('expense_category_id', $params['category_id']);
        $records = $q->get();

        $pdf = Pdf::loadView('exports.expenses', compact('records', 'params'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('expenses-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── Journal Entries ───────────────────────────────────────────────────────

    public function journalEntries(Request $request, string $format)
    {
        $params = $request->only(['date_from', 'date_to', 'type', 'status', 'period_id']);

        if ($format === 'excel') {
            return Excel::download(
                new JournalEntryExport(
                    $params['date_from'] ?? null,
                    $params['date_to']   ?? null,
                    $params['type']      ?? null,
                    $params['status']    ?? null,
                    isset($params['period_id']) ? (int) $params['period_id'] : null,
                ),
                'journal-entries-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $q = JournalEntry::with('period', 'createdBy')->latest('entry_date');
        if ($params['date_from'] ?? null) $q->whereDate('entry_date', '>=', $params['date_from']);
        if ($params['date_to']   ?? null) $q->whereDate('entry_date', '<=', $params['date_to']);
        if ($params['type']      ?? null) $q->where('type', $params['type']);
        if ($params['status']    ?? null) $q->where('status', $params['status']);
        if ($params['period_id'] ?? null) $q->where('period_id', $params['period_id']);
        $records = $q->get();

        $pdf = Pdf::loadView('exports.journal-entries', compact('records', 'params'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('journal-entries-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── Finance Reports ───────────────────────────────────────────────────────

    public function trialBalance(Request $request, string $format)
    {
        $periodId = $request->period_id;
        $rows     = $this->ledger->trialBalance($periodId ? (int) $periodId : null);
        $periods  = FinancialPeriod::orderByDesc('start_date')->get();
        $selectedPeriod = $periodId ? $periods->firstWhere('id', $periodId) : null;
        $totalDebit  = $rows->sum('total_debit');
        $totalCredit = $rows->sum('total_credit');
        $balanced    = abs($totalDebit - $totalCredit) < 0.01;

        if ($format === 'excel') {
            $data = $rows->map(fn ($r) => [
                $r->code, $r->name, ucfirst($r->type),
                number_format($r->total_debit, 2),
                number_format($r->total_credit, 2),
            ]);
            $export = new \App\Exports\GenericExport(
                $data,
                ['Code', 'Account Name', 'Type', 'Debit (₹)', 'Credit (₹)'],
                'Trial Balance', '7C3AED'
            );
            return Excel::download($export, 'trial-balance-' . now()->format('Y-m-d') . '.xlsx');
        }

        $pdf = Pdf::loadView('exports.trial-balance', compact('rows', 'totalDebit', 'totalCredit', 'balanced', 'selectedPeriod'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('trial-balance-' . now()->format('Y-m-d') . '.pdf');
    }

    public function profitLoss(Request $request, string $format)
    {
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;
        $periodId = $request->period_id;
        $data     = $this->ledger->profitAndLoss($periodId ? (int) $periodId : null, $dateFrom, $dateTo);
        $periods  = FinancialPeriod::orderByDesc('start_date')->get();
        $selectedPeriod = $periodId ? $periods->firstWhere('id', $periodId) : null;

        if ($format === 'excel') {
            $rows = collect();
            foreach ($data['revenue'] as $r)  $rows->push([$r->code, $r->name, 'Revenue', number_format($r->balance, 2), '']);
            foreach ($data['expenses'] as $e) $rows->push([$e->code, $e->name, 'Expense', '', number_format($e->balance, 2)]);
            $export = new \App\Exports\GenericExport(
                $rows,
                ['Code', 'Account', 'Type', 'Revenue (₹)', 'Expense (₹)'],
                'P&L', '059669'
            );
            return Excel::download($export, 'profit-loss-' . now()->format('Y-m-d') . '.xlsx');
        }

        $pdf = Pdf::loadView('exports.profit-loss', compact('data', 'dateFrom', 'dateTo', 'selectedPeriod'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('profit-loss-' . now()->format('Y-m-d') . '.pdf');
    }

    public function balanceSheet(Request $request, string $format)
    {
        $data    = $this->ledger->balanceSheet();
        $periods = FinancialPeriod::orderByDesc('start_date')->get();

        if ($format === 'excel') {
            $rows = collect();
            foreach ($data['assets']      as $a) $rows->push(['Asset',     $a->code, $a->name, number_format($a->balance, 2)]);
            foreach ($data['liabilities'] as $l) $rows->push(['Liability', $l->code, $l->name, number_format($l->balance, 2)]);
            foreach ($data['equity']      as $e) $rows->push(['Equity',    $e->code, $e->name, number_format($e->balance, 2)]);
            $export = new \App\Exports\GenericExport(
                $rows,
                ['Category', 'Code', 'Account', 'Balance (₹)'],
                'Balance Sheet', '1D4ED8'
            );
            return Excel::download($export, 'balance-sheet-' . now()->format('Y-m-d') . '.xlsx');
        }

        $pdf = Pdf::loadView('exports.balance-sheet', compact('data'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('balance-sheet-' . now()->format('Y-m-d') . '.pdf');
    }

    public function generalLedger(Request $request, string $format)
    {
        $accountId = $request->account_id ?? ChartOfAccount::where('code', '1010')->value('id');
        $dateFrom  = $request->date_from;
        $dateTo    = $request->date_to;
        $account   = ChartOfAccount::find($accountId);
        $data      = $accountId ? $this->ledger->generalLedger((int) $accountId, $dateFrom, $dateTo) : null;

        if (! $data) return back()->with('error', 'Please select an account.');

        if ($format === 'excel') {
            $rows = collect($data['rows'])->map(fn ($r) => [
                \Carbon\Carbon::parse($r['date'])->format('d/m/Y'),
                $r['entry_number'],
                $r['description'],
                $r['debit'] > 0  ? number_format($r['debit'],  2) : '',
                $r['credit'] > 0 ? number_format($r['credit'], 2) : '',
                number_format(abs($r['balance']), 2) . ' ' . ($r['balance'] >= 0 ? 'Dr' : 'Cr'),
            ]);
            $export = new \App\Exports\GenericExport(
                $rows,
                ['Date', 'Entry #', 'Description', 'Debit (₹)', 'Credit (₹)', 'Balance'],
                'General Ledger - ' . $account->name, '0D9488'
            );
            return Excel::download($export, 'general-ledger-' . now()->format('Y-m-d') . '.xlsx');
        }

        $pdf = Pdf::loadView('exports.general-ledger', compact('data', 'account', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('general-ledger-' . $account->code . '-' . now()->format('Y-m-d') . '.pdf');
    }

    // ── Dairy Operations Reports ──────────────────────────────────────────────

    public function milkReport(Request $request, string $format)
    {
        $dateFrom    = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo      = $request->get('date_to', now()->toDateString());
        $shiftFilter = $request->get('shift');

        $q = MilkEntry::with('animal')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo);
        if ($shiftFilter) $q->where('shift', $shiftFilter);
        $records = $q->orderBy('date', 'desc')->get();

        if ($format === 'excel') {
            $rows = $records->map(fn ($e) => [
                $e->date->format('d/m/Y'),
                $e->shift ?? '—',
                $e->animal?->tag_number ?? '—',
                number_format((float)$e->quantity_liters, 2),
                $e->fat_percentage ? number_format($e->fat_percentage, 2).'%' : '—',
                $e->snf_percentage ? number_format($e->snf_percentage, 2).'%' : '—',
                number_format((float)($e->rejected_liters ?? 0), 2),
                $e->quality_grade ?? '—',
            ]);
            return Excel::download(
                new \App\Exports\GenericExport($rows, ['Date','Shift','Animal','Yield (L)','Fat %','SNF %','Rejected (L)','Quality'], 'Milk Production', '0369A1'),
                'milk-production-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.milk-report', compact('records', 'dateFrom', 'dateTo', 'shiftFilter'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('milk-production-' . now()->format('Y-m-d') . '.pdf');
    }

    public function animalReport(Request $request, string $format)
    {
        $animals = Animal::orderBy('animal_type')->orderBy('tag_number')->get();

        if ($format === 'excel') {
            $rows = $animals->map(fn ($a) => [
                $a->tag_number ?? '—',
                $a->animal_type ?? '—',
                $a->breed ?? '—',
                $a->status ?? '—',
                $a->pregnancy_status ?? '—',
                $a->health_status ?? '—',
                $a->lactation_number ?? 0,
                $a->date_of_birth ? \Carbon\Carbon::parse($a->date_of_birth)->format('d/m/Y') : '—',
            ]);
            return Excel::download(
                new \App\Exports\GenericExport($rows, ['Tag #','Type','Breed','Status','Pregnancy','Health','Lactation #','DOB'], 'Animal Report', '065F46'),
                'animal-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.animal-report', compact('animals'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('animal-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function feedReport(Request $request, string $format, FeedCalculationService $feedService)
    {
        $data            = $feedService->getFeedCalculationSummary();
        $stockComparison = $data['stock_comparison'];

        if ($format === 'excel') {
            $rows = collect($stockComparison)->map(fn ($s) => [
                $s['feed_type'] ?? '—',
                number_format($s['daily_need'] ?? 0, 2),
                number_format(($s['daily_need'] ?? 0) * 7, 2),
                number_format(($s['daily_need'] ?? 0) * 30, 2),
                number_format($s['current_stock'] ?? 0, 2),
                $s['days_remaining'] ?? '—',
                $s['status'] ?? '—',
            ]);
            return Excel::download(
                new \App\Exports\GenericExport($rows, ['Feed Type','Daily Need (kg)','Weekly (kg)','Monthly (kg)','Stock (kg)','Days Remaining','Status'], 'Feed Report', '78350F'),
                'feed-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $totalDailyFeed = array_sum(array_column($stockComparison, 'daily_need'));
        $pdf = Pdf::loadView('exports.feed-report', compact('data', 'stockComparison', 'totalDailyFeed'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('feed-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function breedingReport(Request $request, string $format)
    {
        $dateFrom     = $request->get('date_from', now()->startOfYear()->toDateString());
        $dateTo       = $request->get('date_to', now()->toDateString());
        $statusFilter = $request->get('status');

        $q = BreedingRecord::with('animal')
            ->whereDate('ai_date', '>=', $dateFrom)
            ->whereDate('ai_date', '<=', $dateTo);
        if ($statusFilter) $q->where('status', $statusFilter);
        $records = $q->latest('ai_date')->get();

        if ($format === 'excel') {
            $rows = $records->map(fn ($r) => [
                $r->animal?->tag_number ?? '—',
                $r->ai_date ? \Carbon\Carbon::parse($r->ai_date)->format('d/m/Y') : '—',
                $r->bull_semen ?? '—',
                $r->status ?? '—',
                $r->is_pregnant ? 'Yes' : 'No',
                $r->expected_calving_date ? \Carbon\Carbon::parse($r->expected_calving_date)->format('d/m/Y') : '—',
                $r->actual_calving_date  ? \Carbon\Carbon::parse($r->actual_calving_date)->format('d/m/Y')  : '—',
            ]);
            return Excel::download(
                new \App\Exports\GenericExport($rows, ['Animal Tag','AI Date','Bull/Semen','Status','Pregnant','Expected Calving','Actual Calving'], 'Breeding Report', '7C3AED'),
                'breeding-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.breeding-report', compact('records', 'dateFrom', 'dateTo'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('breeding-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function healthReport(Request $request, string $format)
    {
        $dateFrom   = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo     = $request->get('date_to', now()->toDateString());
        $typeFilter = $request->get('record_type');

        $q = HealthRecord::with('animal')
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo);
        if ($typeFilter) $q->where('record_type', $typeFilter);
        $records = $q->latest('date')->get();

        if ($format === 'excel') {
            $rows = $records->map(fn ($r) => [
                $r->animal?->tag_number ?? '—',
                $r->date ? \Carbon\Carbon::parse($r->date)->format('d/m/Y') : '—',
                $r->record_type ?? '—',
                $r->diagnosis ?? $r->notes ?? '—',
                $r->treatment ?? '—',
                $r->veterinarian ?? '—',
                number_format((float)($r->cost ?? 0), 2),
                $r->next_due_date ? \Carbon\Carbon::parse($r->next_due_date)->format('d/m/Y') : '—',
            ]);
            return Excel::download(
                new \App\Exports\GenericExport($rows, ['Animal Tag','Date','Type','Diagnosis','Treatment','Vet','Cost (₹)','Next Due'], 'Health Report', 'B91C1C'),
                'health-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('exports.health-report', compact('records', 'dateFrom', 'dateTo', 'typeFilter'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('health-report-' . now()->format('Y-m-d') . '.pdf');
    }
}