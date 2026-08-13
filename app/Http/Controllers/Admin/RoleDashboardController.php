<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePermission;
use App\Models\Animal;
use App\Models\MilkEntry;
use App\Models\Expense;
use App\Models\Transaction;

class RoleDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // All active services this user can access
        $accessibleServices = ServicePermission::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(fn($svc) => ServicePermission::canAccess($svc->service_key, $user))
            ->values();

        // Build stat cards — only for services the user can access
        // Each item: [icon, label, value, gradient]
        $statCards = [];

        if (ServicePermission::canAccess('animals', $user)) {
            $statCards[] = ['icon'=>'boxes',        'label'=>'Total Herd',  'value'=> Animal::where('status','Active')->count(),                                                                                          'gradient'=>'linear-gradient(135deg,#6366f1,#8b5cf6)'];
            $statCards[] = ['icon'=>'droplet-fill', 'label'=>'Milking',     'value'=> Animal::where('status','Active')->where('lactation_number','>',0)->where('pregnancy_status','!=','Dry')->count(),                  'gradient'=>'linear-gradient(135deg,#10b981,#0ea5e9)'];
            $statCards[] = ['icon'=>'moon-stars',   'label'=>'Dry',         'value'=> Animal::where('status','Active')->where('pregnancy_status','Dry')->count(),                                                         'gradient'=>'linear-gradient(135deg,#334155,#475569)'];
            $statCards[] = ['icon'=>'heart-pulse',  'label'=>'Pregnant',    'value'=> Animal::where('status','Active')->where('pregnancy_status','Pregnant')->count(),                                                    'gradient'=>'linear-gradient(135deg,#ec4899,#a855f7)'];
            $statCards[] = ['icon'=>'stars',        'label'=>'Calves',      'value'=> Animal::where('status','Active')->where('animal_type','Calf')->count(),                                                            'gradient'=>'linear-gradient(135deg,#f59e0b,#ef4444)'];
        }

        if (ServicePermission::canAccess('milk', $user)) {
            $statCards[] = ['icon'=>'droplet-half', 'label'=>'Milk Today (L)',  'value'=> number_format(MilkEntry::whereDate('date', today())->sum('quantity_liters'), 1),   'gradient'=>'linear-gradient(135deg,#0ea5e9,#38bdf8)'];
        }

        if (ServicePermission::canAccess('transactions', $user)) {
            $statCards[] = ['icon'=>'arrow-left-right', 'label'=>'Transactions', 'value'=> Transaction::count(), 'gradient'=>'linear-gradient(135deg,#6366f1,#4f46e5)'];
        }

        if (ServicePermission::canAccess('expenses', $user)) {
            $statCards[] = ['icon'=>'receipt', 'label'=>'Expenses / Month', 'value'=> '₹'.number_format(Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount')), 'gradient'=>'linear-gradient(135deg,#f59e0b,#f97316)'];
        }

        return view('admin.role-dashboard', compact('accessibleServices', 'statCards'));
    }
}
