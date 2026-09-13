<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCustomer;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function index(Request $request)
    {
        $query = CrmCustomer::query();

        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('phone', 'like', '%'.$request->search.'%')
                ->orWhere('email', 'like', '%'.$request->search.'%'));
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $summary = [
            'total_buyers' => CrmCustomer::where('category', 'Milk Buyer')->count(),
            'franchise_leads' => CrmCustomer::where('category', 'Franchise Lead')->count(),
            'investors' => CrmCustomer::where('category', 'Investor')->count(),
            'total_business' => CrmCustomer::sum('total_business_value'),
        ];
        return view('admin.crm.index', compact('customers', 'summary'));
    }

    public function create()
    {
        return view('admin.crm.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'category' => 'required|in:Milk Buyer,Animal Buyer,Franchise Lead,Investor,Government Official,Veterinary Doctor',
            'phone' => 'required|regex:/^[6-9][0-9]{9}$/',
            'email' => 'required|email|max:100',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:Lead,Contacted,Active Customer,Partner,Inactive',
        ]);

        CrmCustomer::create($validated);
        return redirect()->route('admin.crm.index')->with('success', 'Customer / Lead added.');
    }

    public function show(CrmCustomer $crmCustomer)
    {
        $crmCustomer->load('salesOrders');

        $orders = $crmCustomer->salesOrders;
        $today  = now()->startOfDay();

        // AR aging: age from sale_date (no due_date column; assume invoice-date aging)
        $openOrders = $orders->whereIn('payment_status', ['Pending', 'Partial']);
        $aging = ['current' => 0.0, 'd30' => 0.0, 'd60' => 0.0, 'd90' => 0.0, 'd90plus' => 0.0];
        foreach ($openOrders as $o) {
            $outstanding = max(0, (float) $o->total_amount - (float) $o->amount_paid);
            if ($outstanding <= 0) continue;
            $days = (int) $o->sale_date->diffInDays($today, true);
            if      ($days <= 30)  $aging['current'] += $outstanding;
            elseif  ($days <= 60)  $aging['d30']     += $outstanding;
            elseif  ($days <= 90)  $aging['d60']     += $outstanding;
            elseif  ($days <= 120) $aging['d90']     += $outstanding;
            else                   $aging['d90plus']  += $outstanding;
        }

        $arStats = [
            'total_invoiced' => $orders->whereNotIn('payment_status', ['Unbilled'])->sum('total_amount'),
            'total_paid'     => $orders->sum('amount_paid'),
            'outstanding'    => $openOrders->sum(fn ($o) => max(0, (float) $o->total_amount - (float) $o->amount_paid)),
            'open_invoices'  => $openOrders->count(),
            'last_sale_date' => $orders->sortByDesc('sale_date')->first()?->sale_date,
            'aging'          => $aging,
        ];

        return view('admin.crm.show', compact('crmCustomer', 'arStats'));
    }

    public function edit(CrmCustomer $crmCustomer)
    {
        return view('admin.crm.edit', compact('crmCustomer'));
    }

    public function update(Request $request, CrmCustomer $crmCustomer)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'category'             => 'required|in:Milk Buyer,Animal Buyer,Franchise Lead,Investor,Government Official,Veterinary Doctor',
            'phone'                => 'required|regex:/^[6-9][0-9]{9}$/',
            'email'                => 'required|email|max:100',
            'address'              => 'nullable|string|max:255',
            'total_business_value' => 'nullable|numeric|min:0',
            'status'               => 'required|in:Lead,Contacted,Active Customer,Partner,Inactive',
        ]);

        $crmCustomer->update($validated);

        return redirect()->route('admin.crm.show', $crmCustomer)
            ->with('success', 'Customer updated.');
    }

    public function destroy(CrmCustomer $crmCustomer)
    {
        $crmCustomer->delete();
        return redirect()->route('admin.crm.index')
            ->with('success', 'Customer removed.');
    }
}
