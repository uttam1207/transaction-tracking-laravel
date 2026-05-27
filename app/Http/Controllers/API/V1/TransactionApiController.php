<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\FraudDetectionService;
use Illuminate\Http\Request;

class TransactionApiController extends Controller
{
    public function __construct(private FraudDetectionService $fraudService)
    {
    }

    public function index(Request $request)
    {
        $query = Transaction::with('user', 'fraudAlerts');

        // Role-based data access
        if ($request->user()->isEmployee()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'type' => 'required|in:debit,credit',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'receiver_account' => 'nullable|string',
        ]);

        $transaction = Transaction::create(array_merge(
            $request->only([
                'category', 'type', 'amount', 'currency', 'fee', 'payment_method',
                'sender_name', 'sender_account', 'sender_bank',
                'receiver_name', 'receiver_account', 'receiver_bank',
                'reference', 'description', 'notes',
            ]),
            [
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'ip_address' => $request->ip(),
            ]
        ));

        // Run fraud detection
        $analysis = $this->fraudService->analyze($transaction);
        $transaction->update([
            'risk_score' => $analysis['risk_score'],
            'is_flagged' => $analysis['is_flagged'],
        ]);

        if ($analysis['is_flagged']) {
            $this->fraudService->createAlert($transaction, $analysis);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction created.',
            'data' => $transaction,
            'risk_analysis' => $analysis,
        ], 201);
    }

    public function show(Request $request, Transaction $transaction)
    {
        if ($request->user()->isEmployee() && $transaction->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $transaction->load('user', 'logs', 'fraudAlerts'),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,success,failed,cancelled,reversed',
        ]);

        $transaction->update($request->only(['status', 'notes']));

        return response()->json(['success' => true, 'message' => 'Transaction updated.', 'data' => $transaction]);
    }

    public function statistics(Request $request)
    {
        $stats = [
            'total' => Transaction::count(),
            'success' => Transaction::where('status', 'success')->count(),
            'failed' => Transaction::where('status', 'failed')->count(),
            'pending' => Transaction::where('status', 'pending')->count(),
            'flagged' => Transaction::where('is_flagged', true)->count(),
            'total_volume' => Transaction::where('status', 'success')->sum('amount'),
            'today_volume' => Transaction::where('status', 'success')->whereDate('created_at', today())->sum('amount'),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}
