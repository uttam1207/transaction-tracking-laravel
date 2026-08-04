<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmCustomer;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    public function index()
    {
        $customers = CrmCustomer::latest()->paginate(15);
        $summary = [
            'total_buyers' => CrmCustomer::where('category', 'Milk Buyer')->count(),
            'franchise_leads' => CrmCustomer::where('category', 'Franchise Lead')->count(),
            'investors' => CrmCustomer::where('category', 'Investor')->count(),
            'total_business' => CrmCustomer::sum('total_business_value'),
        ];
        return view('admin.crm.index', compact('customers', 'summary'));
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
}
