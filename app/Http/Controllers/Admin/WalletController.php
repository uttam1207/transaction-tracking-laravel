<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallets = Wallet::with('user')
            ->orderByDesc('balance')
            ->paginate(20);

        $stats = [
            'total'        => Wallet::count(),
            'total_balance'=> Wallet::sum('balance'),
            'frozen'       => Wallet::where('status', 'frozen')->count(),
            'active'       => Wallet::where('status', 'active')->count(),
        ];

        return view('admin.wallets.index', compact('wallets', 'stats'));
    }

    public function show(User $user)
    {
        $wallet = Wallet::findOrCreateForUser($user->id);
        $wallet->load('user');

        $transactions = $wallet->transactions()
            ->with('performer')
            ->latest()
            ->paginate(20);

        return view('admin.wallets.show', compact('wallet', 'transactions', 'user'));
    }

    public function addMoney(Request $request, Wallet $wallet)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:1|max:10000000',
            'description' => 'required|string|max:255',
        ]);

        $wallet->credit(
            (float) $request->amount,
            $request->description,
            auth()->id()
        );

        return redirect()->route('admin.wallets.show', $wallet->user_id)
            ->with('success', '₹' . number_format($request->amount, 2) . ' added to wallet successfully.');
    }

    public function toggleFreeze(Wallet $wallet)
    {
        $wallet->update([
            'status' => $wallet->status === 'active' ? 'frozen' : 'active',
        ]);

        return back()->with('success', 'Wallet status updated.');
    }
}
