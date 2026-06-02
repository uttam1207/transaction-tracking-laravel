<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::company();

        $transactions = $wallet->transactions()
            ->with('performer')
            ->latest()
            ->paginate(25);

        $stats = [
            'total_credited' => $wallet->transactions()->where('type', 'credit')->sum('amount'),
            'total_debited'  => $wallet->transactions()->where('type', 'debit')->sum('amount'),
            'txn_count'      => $wallet->transactions()->count(),
        ];

        return view('admin.wallets.index', compact('wallet', 'transactions', 'stats'));
    }

    public function addMoney(Request $request, Wallet $wallet)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1|max:9999999999',
            'description' => 'required|string|max:255',
        ]);

        $amount = (float) $request->amount;
        $wallet->credit($amount, $request->description, auth()->id());

        AuditLog::create([
            'user_id'        => auth()->id(),
            'event'          => 'wallet_top_up',
            'auditable_type' => Wallet::class,
            'auditable_id'   => $wallet->id,
            'new_values'     => ['amount' => $amount, 'description' => $request->description],
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'url'            => $request->fullUrl(),
            'module'         => 'wallet',
            'description'    => auth()->user()->name . ' added ₹' . number_format($amount, 2) . ' to company wallet',
        ]);

        return redirect()->route('admin.wallets.index')
            ->with('success', '₹' . number_format($amount, 2) . ' added to company wallet successfully.');
    }

    public function toggleFreeze(Wallet $wallet)
    {
        $wallet->update([
            'status' => $wallet->status === 'active' ? 'frozen' : 'active',
        ]);

        return back()->with('success', 'Wallet status updated.');
    }
}
