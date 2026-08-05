<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Franchise;
use Illuminate\Http\Request;

class FranchiseController extends Controller
{
    public function index(Request $request)
    {
        $query = Franchise::query();

        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('franchise_code', 'like', '%'.$request->search.'%')
                ->orWhere('owner_name', 'like', '%'.$request->search.'%')
                ->orWhere('location', 'like', '%'.$request->search.'%'));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $franchises = $query->latest()->paginate(15)->withQueryString();
        $summary = [
            'total_active' => Franchise::where('status', 'Active')->count(),
            'total_investment' => Franchise::sum('investment_amount'),
            'total_milk_collected' => Franchise::sum('milk_collected_liters'),
        ];
        return view('admin.franchise.index', compact('franchises', 'summary'));
    }

    public function create()
    {
        return view('admin.franchise.create');
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

    public function show(Franchise $franchise)
    {
        return view('admin.franchise.show', compact('franchise'));
    }

    public function edit(Franchise $franchise)
    {
        return view('admin.franchise.edit', compact('franchise'));
    }

    public function update(Request $request, Franchise $franchise)
    {
        $validated = $request->validate([
            'franchise_code'      => 'required|string|unique:franchises,franchise_code,' . $franchise->id,
            'owner_name'          => 'required|string|max:100',
            'location'            => 'required|string|max:150',
            'contact_number'      => 'required|string|max:20',
            'agreement_date'      => 'required|date',
            'investment_amount'   => 'required|numeric|min:0',
            'royalty_percentage'  => 'required|numeric|min:0|max:100',
            'status'              => 'required|in:Active,Inactive,Suspended',
            'milk_collected_liters' => 'nullable|numeric|min:0',
        ]);

        $franchise->update($validated);

        return redirect()->route('admin.franchise.show', $franchise)
            ->with('success', 'Franchise updated.');
    }

    public function destroy(Franchise $franchise)
    {
        $franchise->delete();
        return redirect()->route('admin.franchise.index')
            ->with('success', 'Franchise removed.');
    }
}
