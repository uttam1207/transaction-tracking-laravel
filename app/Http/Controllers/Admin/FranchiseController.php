<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use Illuminate\Http\Request;

class FranchiseController extends Controller
{
    public function index()
    {
        $franchises = Franchise::latest()->paginate(15);
        $summary = [
            'total_active' => Franchise::where('status', 'Active')->count(),
            'total_investment' => Franchise::sum('investment_amount'),
            'total_milk_collected' => Franchise::sum('milk_collected_liters'),
        ];
        return view('admin.franchise.index', compact('franchises', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'franchise_code' => 'required|string|unique:franchises,franchise_code',
            'owner_name' => 'required|string|max:100',
            'location' => 'required|string|max:150',
            'contact_number' => 'required|string|max:20',
            'agreement_date' => 'required|date',
            'investment_amount' => 'required|numeric|min:0',
            'royalty_percentage' => 'required|numeric|min:0|max:100',
        ]);

        Franchise::create($validated);
        return redirect()->route('admin.franchise.index')->with('success', 'Franchise registered.');
    }
}
