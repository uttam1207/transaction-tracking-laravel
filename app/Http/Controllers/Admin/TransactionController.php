<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TransactionsExport;
use App\Http\Controllers\Controller;
use App\Mail\TransactionStatusMail;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FraudDetectionService;
use App\Services\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class TransactionController extends Controller
{
    public function __construct(
        private FraudDetectionService $fraudService,
        private NotificationService $notificationService
    ) {
    }

    public function index(Request $request)
    {
        $query = Transaction::with('user', 'fraudAlerts');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('transaction_id', 'like', '%' . $request->search . '%')
                  ->orWhere('sender_name', 'like', '%' . $request->search . '%')
                  ->orWhere('receiver_name', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->is_flagged !== null && $request->is_flagged !== '') {
            $query->where('is_flagged', $request->boolean('is_flagged'));
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->amount_min) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->amount_max) {
            $query->where('amount', '<=', $request->amount_max);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total' => Transaction::count(),
            'success' => Transaction::where('status', 'success')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'flagged' => Transaction::where('is_flagged', true)->count(),
        ];

        return view('admin.transactions.index', compact('transactions', 'summary'));
    }

    public function create()
    {
        $users = User::active()->orderBy('name')->get();
        return view('admin.transactions.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category'          => 'required|string',
            'type'              => 'required|in:debit,credit',
            'amount'            => 'required|numeric|min:0.01',
            'currency'          => 'required|string|size:3',
            'payment_method'    => 'nullable|string',
            'sender_name'       => 'nullable|string|max:255',
            'sender_account'    => 'nullable|string|max:100',
            'sender_mobile'     => 'nullable|string|max:20',
            'sender_company'    => 'nullable|string|max:255',
            'receiver_name'     => 'nullable|string|max:255',
            'receiver_account'  => 'nullable|string|max:100',
            'receiver_mobile'   => 'nullable|string|max:20',
            'receiver_company'  => 'nullable|string|max:255',
            'receiver_address'         => 'nullable|string|max:500',
            'receipt'                  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'account_owner_name'       => 'nullable|string|max:255',
            'account_owner_mobile'     => 'nullable|string|max:20',
            'account_owner_company'    => 'nullable|string|max:255',
            'account_owner_address'    => 'nullable|string|max:500',
        ]);

        // Handle receipt upload
        $attachments = [];
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $attachments[] = [
                'type'         => 'receipt',
                'path'         => $path,
                'original'     => $request->file('receipt')->getClientOriginalName(),
                'uploaded_at'  => now()->toDateTimeString(),
            ];
        }

        // Build metadata: cash voucher + external account owner
        $metadata = [];
        if ($request->payment_method === 'cash' && $request->filled('cash_voucher_name')) {
            $metadata['cash_voucher'] = [
                'company_name' => $request->cash_voucher_company,
                'name'         => $request->cash_voucher_name,
                'mobile'       => $request->cash_voucher_mobile,
                'address'      => $request->cash_voucher_address,
            ];
        }
        if ($request->filled('account_owner_name')) {
            $metadata['external_owner'] = [
                'name'    => $request->account_owner_name,
                'mobile'  => $request->account_owner_mobile,
                'company' => $request->account_owner_company,
                'address' => $request->account_owner_address,
            ];
        }
        $metadata = empty($metadata) ? null : $metadata;

        $transaction = Transaction::create(array_merge(
            $request->only([
                'category', 'type', 'amount', 'currency', 'fee', 'payment_method',
                'sender_name', 'sender_account', 'sender_bank', 'sender_mobile', 'sender_company',
                'receiver_name', 'receiver_account', 'receiver_bank',
                'receiver_mobile', 'receiver_company', 'receiver_address',
                'reference', 'description', 'notes', 'country', 'device_id',
            ]),
            [
                'user_id'     => $request->user_id ?: auth()->id(),
                'status'      => $request->status ?? 'pending',
                'ip_address'  => $request->ip(),
                'attachments' => $attachments ?: null,
                'metadata'    => $metadata,
            ]
        ));

        // Run fraud detection
        $analysis = $this->fraudService->analyze($transaction);
        $transaction->update([
            'risk_score' => $analysis['risk_score'],
            'is_flagged' => $analysis['is_flagged'],
            'fraud_reason' => !empty($analysis['triggered_rules'])
                ? implode(', ', array_column($analysis['triggered_rules'], 'rule'))
                : null,
        ]);

        if ($analysis['is_flagged']) {
            $this->fraudService->createAlert($transaction, $analysis);
        }

        // If transaction created directly as success, update wallet immediately
        if ($transaction->status === 'success') {
            $wallet = Wallet::company();
            if ($wallet->status === 'active') {
                $desc = 'Transaction ' . $transaction->transaction_id;
                try {
                    if ($transaction->type === 'credit') {
                        $wallet->credit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id);
                    } else {
                        $wallet->debit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id);
                    }
                } catch (\RuntimeException $e) {
                    // Revert to pending if wallet debit fails
                    $transaction->update(['status' => 'pending']);
                    return redirect()->route('admin.transactions.show', $transaction)
                        ->with('warning', 'Transaction created but status set to pending — ' . $e->getMessage());
                }
            }
        }

        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'action' => 'created',
            'to_status' => $transaction->status,
            'performed_by' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('admin.transactions.show', $transaction)
                         ->with('success', 'Transaction created. ID: ' . $transaction->transaction_id);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('user', 'logs.performer', 'fraudAlerts');
        return view('admin.transactions.show', compact('transaction'));
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,success,failed,cancelled,reversed',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $transaction->status;

        // Status transition validation
        $allowedTransitions = [
            'pending'    => ['pending', 'processing', 'success', 'failed', 'cancelled'],
            'processing' => ['pending', 'processing', 'success', 'failed', 'cancelled'],
            'success'    => ['reversed'],
            'failed'     => [],
            'cancelled'  => [],
            'reversed'   => [],
        ];
        $allowed = $allowedTransitions[$oldStatus] ?? [];
        if ($request->status !== $oldStatus && !in_array($request->status, $allowed)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot change status from \"{$oldStatus}\" to \"{$request->status}\". " .
                    ($allowed ? 'Allowed: ' . implode(', ', $allowed) : 'No changes allowed from this status.'),
            ], 422);
        }
        $transaction->update([
            'status' => $request->status,
            'processed_at' => in_array($request->status, ['success', 'failed']) ? now() : null,
        ]);

        // Update wallet balance when transaction succeeds (only once)
        if ($request->status === 'success' && $oldStatus !== 'success') {
            $wallet = Wallet::company();
            if ($wallet->status === 'active') {
                $desc = 'Transaction ' . $transaction->transaction_id;
                try {
                    if ($transaction->type === 'credit') {
                        $wallet->credit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id);
                    } else {
                        $wallet->debit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id);
                    }
                } catch (\RuntimeException $e) {
                    // Revert status change if wallet operation fails
                    $transaction->update(['status' => $oldStatus]);
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
            }
        }

        // Reverse wallet if transaction is reversed (only if was previously success)
        if ($request->status === 'reversed' && $oldStatus === 'success') {
            $wallet = Wallet::company();
            if ($wallet->status === 'active') {
                $desc = 'Reversal of ' . $transaction->transaction_id;
                try {
                    if ($transaction->type === 'credit') {
                        $wallet->debit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id);
                    } else {
                        $wallet->credit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id);
                    }
                } catch (\RuntimeException $e) {
                    $transaction->update(['status' => $oldStatus]);
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
                }
            }
        }

        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'action' => 'status_changed',
            'from_status' => $oldStatus,
            'to_status' => $request->status,
            'performed_by' => auth()->id(),
            'notes' => $request->notes,
            'ip_address' => $request->ip(),
        ]);

        // Send email notification to transaction owner
        if ($transaction->user && $transaction->user->email) {
            try {
                Mail::to($transaction->user->email)
                    ->queue(new TransactionStatusMail($transaction, $oldStatus));
            } catch (\Exception $e) {
                // Don't fail the status update if mail fails
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction status updated.',
            'status' => $transaction->status,
        ]);
    }

    public function edit(Transaction $transaction)
    {
        $users = User::active()->orderBy('name')->get();
        return view('admin.transactions.edit', compact('transaction', 'users'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'category'         => 'required|string',
            'payment_method'   => 'nullable|string',
            'sender_name'      => 'nullable|string|max:255',
            'sender_account'   => 'nullable|string|max:100',
            'sender_bank'      => 'nullable|string|max:255',
            'sender_mobile'    => 'nullable|string|max:20',
            'sender_company'   => 'nullable|string|max:255',
            'receiver_name'    => 'nullable|string|max:255',
            'receiver_account' => 'nullable|string|max:100',
            'receiver_bank'    => 'nullable|string|max:255',
            'receiver_mobile'  => 'nullable|string|max:20',
            'receiver_company' => 'nullable|string|max:255',
            'receiver_address' => 'nullable|string|max:500',
            'reference'        => 'nullable|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $transaction->update($request->only([
            'category', 'payment_method',
            'sender_name', 'sender_account', 'sender_bank', 'sender_mobile', 'sender_company',
            'receiver_name', 'receiver_account', 'receiver_bank',
            'receiver_mobile', 'receiver_company', 'receiver_address',
            'reference', 'description', 'notes',
        ]));

        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'action'         => 'edited',
            'to_status'      => $transaction->status,
            'performed_by'   => auth()->id(),
            'notes'          => 'Transaction details updated.',
            'ip_address'     => $request->ip(),
        ]);

        return redirect()->route('admin.transactions.show', $transaction)
                         ->with('success', 'Transaction updated successfully.');
    }

    public function downloadPdf(Transaction $transaction)
    {
        $transaction->load('user', 'logs.performer');
        $pdf = Pdf::loadView('admin.transactions.receipt', compact('transaction'))
                  ->setPaper('a4', 'portrait');
        return $pdf->download('receipt_' . $transaction->transaction_id . '.pdf');
    }

    public function batchUpdate(Request $request)
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer|exists:transactions,id',
            'status' => 'required|in:pending,processing,success,failed,cancelled,reversed',
        ]);

        $transactions = Transaction::whereIn('id', $request->ids)->get();
        $updated = 0;

        foreach ($transactions as $transaction) {
            $oldStatus = $transaction->status;
            $transaction->update(['status' => $request->status]);

            if ($request->status === 'success' && $oldStatus !== 'success') {
                $wallet = Wallet::company();
                if ($wallet->status === 'active') {
                    $desc = 'Transaction ' . $transaction->transaction_id;
                    $transaction->type === 'credit'
                        ? $wallet->credit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id)
                        : $wallet->debit((float) $transaction->net_amount, $desc, auth()->id(), $transaction->transaction_id);
                }
            }

            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'action'         => 'status_changed',
                'from_status'    => $oldStatus,
                'to_status'      => $request->status,
                'performed_by'   => auth()->id(),
                'notes'          => 'Batch status update.',
                'ip_address'     => $request->ip(),
            ]);
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$updated} transaction(s) updated to " . $request->status . '.',
        ]);
    }

    public function refund(Request $request, Transaction $transaction)
    {
        if ($transaction->status !== 'success') {
            return response()->json(['success' => false, 'message' => 'Only successful transactions can be refunded.'], 422);
        }
        if ($transaction->is_refunded) {
            return response()->json(['success' => false, 'message' => 'This transaction has already been refunded.'], 422);
        }

        // Create the reverse transaction
        $refundTx = Transaction::create([
            'user_id'        => $transaction->user_id,
            'category'       => $transaction->category,
            'type'           => $transaction->type === 'debit' ? 'credit' : 'debit',
            'amount'         => $transaction->amount,
            'currency'       => $transaction->currency,
            'fee'            => 0,
            'status'         => 'success',
            'payment_method' => $transaction->payment_method,
            'sender_name'    => $transaction->receiver_name,
            'receiver_name'  => $transaction->sender_name,
            'description'    => 'Refund for ' . $transaction->transaction_id,
            'reference'      => $transaction->transaction_id,
            'ip_address'     => $request->ip(),
            'processed_at'   => now(),
        ]);

        // Mark original as refunded and reversed
        $transaction->update([
            'is_refunded'           => true,
            'refund_transaction_id' => $refundTx->id,
            'status'                => 'reversed',
        ]);

        // Reverse wallet side effect
        $wallet = Wallet::company();
        if ($wallet->status === 'active') {
            $desc = 'Refund: ' . $transaction->transaction_id;
            try {
                // Original debit → refund credits back; original credit → refund debits back
                if ($transaction->type === 'debit') {
                    $wallet->credit((float) $transaction->net_amount, $desc, auth()->id(), $refundTx->transaction_id);
                } else {
                    $wallet->debit((float) $transaction->net_amount, $desc, auth()->id(), $refundTx->transaction_id);
                }
            } catch (\RuntimeException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
        }

        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'action'         => 'refunded',
            'from_status'    => 'success',
            'to_status'      => 'reversed',
            'performed_by'   => auth()->id(),
            'notes'          => 'Refund processed → ' . $refundTx->transaction_id,
            'ip_address'     => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund processed. New transaction: ' . $refundTx->transaction_id,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $transactions = Transaction::with('user')
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('transaction_id', 'like', '%'.$request->search.'%')
                   ->orWhere('sender_name', 'like', '%'.$request->search.'%')
                   ->orWhere('receiver_name', 'like', '%'.$request->search.'%');
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->amount_min, fn($q) => $q->where('amount', '>=', $request->amount_min))
            ->when($request->amount_max, fn($q) => $q->where('amount', '<=', $request->amount_max))
            ->when($request->is_flagged !== null && $request->is_flagged !== '', fn($q) => $q->where('is_flagged', $request->boolean('is_flagged')))
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Transaction ID', 'User', 'Category', 'Type', 'Amount', 'Currency', 'Status', 'Risk Score', 'Date']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->id, $t->transaction_id, $t->user?->name ?? 'N/A', $t->category,
                    $t->type, $t->amount, $t->currency, $t->status, $t->risk_score,
                    $t->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $transactions = Transaction::with('user')
            ->when($request->search, fn($q) => $q->where(function ($q2) use ($request) {
                $q2->where('transaction_id', 'like', '%'.$request->search.'%')
                   ->orWhere('sender_name', 'like', '%'.$request->search.'%')
                   ->orWhere('receiver_name', 'like', '%'.$request->search.'%');
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->when($request->amount_min, fn($q) => $q->where('amount', '>=', $request->amount_min))
            ->when($request->amount_max, fn($q) => $q->where('amount', '<=', $request->amount_max))
            ->when($request->is_flagged !== null && $request->is_flagged !== '', fn($q) => $q->where('is_flagged', $request->boolean('is_flagged')))
            ->latest()
            ->limit(500)
            ->get();
        $pdf = Pdf::loadView('admin.transactions.pdf', compact('transactions'));
        return $pdf->download('transactions_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['status', 'type', 'date_from', 'date_to', 'is_flagged']);
        return Excel::download(
            new TransactionsExport($filters),
            'transactions_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function voucherPdf(Transaction $transaction)
    {
        $transaction->load('user');
        $amountWords = $this->numberToWords((float) $transaction->net_amount);
        $pdf = Pdf::loadView('admin.transactions.voucher', compact('transaction', 'amountWords'))
                  ->setPaper('a5', 'portrait');
        return $pdf->download('voucher_' . $transaction->transaction_id . '.pdf');
    }

    public function blankVoucher()
    {
        $pdf = Pdf::loadView('admin.transactions.voucher-blank')
                  ->setPaper('a4', 'portrait');
        return $pdf->download('cash_voucher_blank.pdf');
    }

    private function numberToWords(float $amount): string
    {
        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen',
        ];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $num   = (int) floor(abs($amount));
        $paise = (int) round((abs($amount) - $num) * 100);

        if ($num === 0 && $paise === 0) return 'Zero Rupees Only';

        $below100 = function (int $n) use ($ones, $tens): string {
            if ($n < 20) return $ones[$n];
            return $tens[(int) ($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
        };

        $below1000 = function (int $n) use ($ones, $below100): string {
            if ($n < 100) return $below100($n);
            return $ones[(int) ($n / 100)] . ' Hundred' . ($n % 100 ? ' ' . $below100($n % 100) : '');
        };

        $parts = [];
        if ($num >= 10000000) { $parts[] = $below100((int) ($num / 10000000)) . ' Crore';   $num %= 10000000; }
        if ($num >= 100000)   { $parts[] = $below100((int) ($num / 100000))   . ' Lakh';    $num %= 100000; }
        if ($num >= 1000)     { $parts[] = $below100((int) ($num / 1000))     . ' Thousand'; $num %= 1000; }
        if ($num > 0)         { $parts[] = $below1000($num); }

        $result = implode(' ', $parts) . ' Rupees';
        if ($paise > 0) $result .= ' and ' . $below100($paise) . ' Paise';
        return $result . ' Only';
    }
}
