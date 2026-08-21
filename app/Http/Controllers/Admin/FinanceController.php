<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\FinancialPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\LedgerBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function __construct(protected LedgerBalanceService $ledger) {}

    // ── Chart of Accounts ────────────────────────────────────────────────────

    public function coaIndex(Request $request)
    {
        $query = ChartOfAccount::with('parent');

        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('active')) {
            $query->where('is_active', $request->active);
        }

        $accounts = $query->orderBy('code')->paginate(30)->withQueryString();
        $parents  = ChartOfAccount::whereNull('parent_id')->orderBy('code')->get();
        $types    = ['asset', 'liability', 'equity', 'revenue', 'expense'];

        return view('admin.finance.coa.index', compact('accounts', 'parents', 'types'));
    }

    public function coaStore(Request $request)
    {
        $request->validate([
            'code'      => 'required|string|max:20|unique:chart_of_accounts,code',
            'name'      => 'required|string|max:200',
            'type'      => 'required|in:asset,liability,equity,revenue,expense',
            'sub_type'  => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        $account = ChartOfAccount::create($request->only(
            'code', 'name', 'type', 'sub_type', 'parent_id', 'description', 'is_active', 'allow_direct_posting'
        ));

        return response()->json(['success' => true, 'message' => 'Account created.', 'account' => $account]);
    }

    public function coaUpdate(Request $request, ChartOfAccount $account)
    {
        $request->validate([
            'code' => "required|string|max:20|unique:chart_of_accounts,code,{$account->id}",
            'name' => 'required|string|max:200',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
        ]);

        $account->update($request->only(
            'code', 'name', 'type', 'sub_type', 'parent_id',
            'description', 'is_active', 'allow_direct_posting'
        ));

        return response()->json(['success' => true, 'message' => 'Account updated.']);
    }

    public function coaDestroy(ChartOfAccount $account)
    {
        if ($account->journalLines()->exists()) {
            return response()->json(['success' => false, 'message' => 'Account has journal lines and cannot be deleted.'], 422);
        }
        if ($account->children()->exists()) {
            return response()->json(['success' => false, 'message' => 'Account has sub-accounts. Remove them first.'], 422);
        }

        $account->delete();
        return response()->json(['success' => true, 'message' => 'Account deleted.']);
    }

    // ── Financial Periods ────────────────────────────────────────────────────

    public function periodsIndex()
    {
        $periods = FinancialPeriod::with('createdBy')->orderByDesc('start_date')->paginate(20);
        return view('admin.finance.periods.index', compact('periods'));
    }

    public function periodsStore(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:year,quarter,month',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        $period = FinancialPeriod::create([
            'name'       => $request->name,
            'type'       => $request->type,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => 'open',
            'created_by' => auth()->user()?->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Period created.', 'period' => $period]);
    }

    public function periodsUpdate(Request $request, FinancialPeriod $period)
    {
        $request->validate([
            'status' => 'required|in:open,closed,locked',
        ]);

        if ($period->status === 'locked') {
            return response()->json(['success' => false, 'message' => 'Locked periods cannot be modified.'], 422);
        }

        $period->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Period updated.']);
    }

    /**
     * Close a period: recalculate all ledger balances then set status to closed.
     */
    public function periodsClose(FinancialPeriod $period)
    {
        if ($period->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'Only open periods can be closed.'], 422);
        }

        $this->ledger->recalculateForPeriod($period);
        $period->update(['status' => 'closed']);

        return response()->json(['success' => true, 'message' => 'Period closed and ledger balances updated.']);
    }

    // ── Journal Entries ──────────────────────────────────────────────────────

    public function journalIndex(Request $request)
    {
        $query = JournalEntry::with('createdBy', 'period');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->period_id) {
            $query->where('period_id', $request->period_id);
        }
        if ($request->date_from) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('entry_number', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%");
            });
        }

        $entries = $query->latest('entry_date')->paginate(20)->withQueryString();
        $periods = FinancialPeriod::orderByDesc('start_date')->get();
        $types   = ['general', 'sales', 'purchase', 'payment', 'receipt', 'payroll', 'depreciation', 'adjustment'];

        $stats = [
            'draft'  => JournalEntry::where('status', 'draft')->count(),
            'posted' => JournalEntry::where('status', 'posted')->count(),
        ];

        return view('admin.finance.journal.index', compact('entries', 'periods', 'types', 'stats'));
    }

    public function journalCreate()
    {
        $accounts = ChartOfAccount::postable()->orderBy('code')->get();
        $periods  = FinancialPeriod::open()->orderByDesc('start_date')->get();
        $types    = ['general', 'sales', 'purchase', 'payment', 'receipt', 'payroll', 'depreciation', 'adjustment'];
        return view('admin.finance.journal.create', compact('accounts', 'periods', 'types'));
    }

    public function journalStore(Request $request)
    {
        $request->validate([
            'entry_date'           => 'required|date',
            'period_id'            => 'nullable|exists:financial_periods,id',
            'type'                 => 'required|in:general,sales,purchase,payment,receipt,payroll,depreciation,adjustment',
            'description'          => 'nullable|string',
            'reference'            => 'nullable|string|max:100',
            'lines'                => 'required|array|min:2',
            'lines.*.account_id'   => 'required|exists:chart_of_accounts,id',
            'lines.*.debit'        => 'required|numeric|min:0',
            'lines.*.credit'       => 'required|numeric|min:0',
        ]);

        $totalDebit  = collect($request->lines)->sum('debit');
        $totalCredit = collect($request->lines)->sum('credit');

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            return back()->withErrors(['lines' => 'Debits must equal credits. Current difference: ' . abs($totalDebit - $totalCredit)])->withInput();
        }

        DB::transaction(function () use ($request, $totalDebit, $totalCredit) {
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $request->period_id,
                'entry_date'   => $request->entry_date,
                'reference'    => $request->reference,
                'type'         => $request->type,
                'description'  => $request->description,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'status'       => 'draft',
                'created_by'   => auth()->user()?->id,
            ]);

            foreach ($request->lines as $line) {
                if ($line['debit'] > 0 || $line['credit'] > 0) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id'       => $line['account_id'],
                        'debit'            => $line['debit'],
                        'credit'           => $line['credit'],
                        'description'      => $line['description'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('admin.finance.journal.index')->with('success', 'Journal entry created.');
    }

    public function journalShow(JournalEntry $entry)
    {
        $entry->load('lines.account', 'period', 'createdBy', 'postedBy');
        return view('admin.finance.journal.show', compact('entry'));
    }

    public function journalPost(JournalEntry $entry)
    {
        if ($entry->status !== 'draft') {
            return response()->json(['success' => false, 'message' => 'Only draft entries can be posted.'], 422);
        }

        $entry->update([
            'status'    => 'posted',
            'posted_by' => auth()->user()?->id,
            'posted_at' => now(),
        ]);

        // Update ledger balances after posting
        $this->ledger->updateAfterPost($entry->load('lines'));

        return response()->json(['success' => true, 'message' => 'Entry posted successfully.']);
    }

    public function journalReverse(JournalEntry $entry)
    {
        if ($entry->status !== 'posted') {
            return response()->json(['success' => false, 'message' => 'Only posted entries can be reversed.'], 422);
        }

        DB::transaction(function () use ($entry) {
            $reversal = JournalEntry::create([
                'entry_number' => JournalEntry::generateNumber(),
                'period_id'    => $entry->period_id,
                'entry_date'   => now()->toDateString(),
                'reference'    => 'REV-' . $entry->entry_number,
                'type'         => $entry->type,
                'description'  => 'Reversal of ' . $entry->entry_number,
                'total_debit'  => $entry->total_credit,
                'total_credit' => $entry->total_debit,
                'status'       => 'posted',
                'created_by'   => auth()->user()?->id,
                'posted_by'    => auth()->user()?->id,
                'posted_at'    => now(),
                'reversal_of'  => $entry->id,
            ]);

            foreach ($entry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id'       => $line->account_id,
                    'debit'            => $line->credit,
                    'credit'           => $line->debit,
                    'description'      => 'Reversal: ' . $line->description,
                ]);
            }

            $entry->update(['status' => 'reversed']);

            // Update ledger balances for reversal
            $this->ledger->updateAfterPost($reversal->load('lines'));
        });

        return response()->json(['success' => true, 'message' => 'Entry reversed.']);
    }

    // ── Reports ──────────────────────────────────────────────────────────────

    public function trialBalance(Request $request)
    {
        $periods  = FinancialPeriod::orderByDesc('start_date')->get();
        $periodId = $request->period_id;

        $rows = $this->ledger->trialBalance($periodId ?: null);

        $totalDebit  = $rows->sum('total_debit');
        $totalCredit = $rows->sum('total_credit');
        $balanced    = round($totalDebit, 2) === round($totalCredit, 2);

        $selectedPeriod = $periodId ? $periods->firstWhere('id', $periodId) : null;

        return view('admin.finance.reports.trial-balance', compact(
            'rows', 'periods', 'selectedPeriod', 'totalDebit', 'totalCredit', 'balanced'
        ));
    }

    public function generalLedger(Request $request)
    {
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        $accountId = $request->account_id;
        $dateFrom  = $request->date_from;
        $dateTo    = $request->date_to;

        $data = null;
        $selectedAccount = null;

        if ($accountId) {
            $selectedAccount = ChartOfAccount::find($accountId);
            $data = $this->ledger->generalLedger((int) $accountId, $dateFrom, $dateTo);
        }

        return view('admin.finance.reports.general-ledger', compact(
            'accounts', 'accountId', 'dateFrom', 'dateTo', 'data', 'selectedAccount'
        ));
    }

    public function profitLoss(Request $request)
    {
        $periods       = FinancialPeriod::orderByDesc('start_date')->get();
        $periodId      = $request->period_id;
        $dateFrom      = $request->date_from;
        $dateTo        = $request->date_to;
        $selectedPeriod = $periodId ? $periods->firstWhere('id', $periodId) : null;

        $data = $this->ledger->profitAndLoss($periodId ?: null, $dateFrom ?: null, $dateTo ?: null);

        return view('admin.finance.reports.profit-loss', compact(
            'periods', 'selectedPeriod', 'periodId', 'dateFrom', 'dateTo', 'data'
        ));
    }

    public function balanceSheet(Request $request)
    {
        $periods        = FinancialPeriod::orderByDesc('start_date')->get();
        $periodId       = $request->period_id;
        $selectedPeriod = $periodId ? $periods->firstWhere('id', $periodId) : null;

        $data = $this->ledger->balanceSheet($periodId ?: null);

        return view('admin.finance.reports.balance-sheet', compact(
            'periods', 'selectedPeriod', 'periodId', 'data'
        ));
    }
}
