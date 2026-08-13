<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePermission;
use App\Models\Animal;
use App\Models\MilkEntry;
use App\Models\Expense;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class RoleDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Collect all active services this user can access
        $accessibleServices = ServicePermission::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($svc) => ServicePermission::canAccess($svc->service_key, $user))
            ->values();

        // Build quick stats only for services the user can access
        $stats = [];

        if (ServicePermission::canAccess('transactions', $user)) {
            $stats['transactions'] = Transaction::count();
        }
        if (ServicePermission::canAccess('animals', $user)) {
            $stats['animals'] = Animal::count();
        }
        if (ServicePermission::canAccess('milk', $user)) {
            $stats['milk_today'] = MilkEntry::whereDate('created_at', today())->sum('quantity');
        }
        if (ServicePermission::canAccess('expenses', $user)) {
            $stats['expenses_month'] = Expense::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');
        }

        return view('admin.role-dashboard', compact('accessibleServices', 'stats'));
    }
}
