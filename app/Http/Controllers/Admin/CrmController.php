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
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:Lead,Contacted,Active Customer,Partner,Inactive',
        ]);

        CrmCustomer::create($validated);
        return redirect()->route('admin.crm.index')->with('success', 'Customer / Lead added.');
    }

    public function show(CrmCustomer $crmCustomer)
    {
        $crmCustomer->load('salesOrders');
        return view('admin.crm.show', compact('crmCustomer'));
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
            'phone'                => 'nullable|string|max:20',
            'email'                => 'nullable|email|max:100',
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
