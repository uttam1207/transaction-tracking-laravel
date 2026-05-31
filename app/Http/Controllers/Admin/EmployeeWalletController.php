<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;

class EmployeeWalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::company();

        $transactions = $wallet->transactions()
            ->with('performer')
            ->latest()
            ->paginate(15);

        return view('employee.wallet.index', compact('wallet', 'transactions'));
    }
}
