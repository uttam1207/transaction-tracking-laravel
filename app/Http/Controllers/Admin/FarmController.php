<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmRecord;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function index(Request $request)
    {
        $query = FarmRecord::query();

        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('plot_name', 'like', '%'.$request->search.'%')
                ->orWhere('crop_type', 'like', '%'.$request->search.'%'));
        }
        if ($request->crop_type) {
            $query->where('crop_type', $request->crop_type);
        }

        $records = $query->latest()->paginate(15)->withQueryString();
        $cropTypes = FarmRecord::distinct()->pluck('crop_type')->sort()->values();
        $summary = [
            'total_yield' => FarmRecord::sum('yield_kg'),
            'total_diesel' => FarmRecord::sum('diesel_liters'),
            'total_water' => FarmRecord::sum('water_usage_liters'),
            'active_plots' => FarmRecord::distinct('plot_name')->count('plot_name'),
        ];
        return view('admin.farm.index', compact('records', 'summary', 'cropTypes'));
    }

    public function create()
    {
        return view('admin.farm.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plot_name' => 'required|string|max:100',
            'crop_type' => 'required|string|max:100',
            'plantation_date' => 'nullable|date',
            'fertilizer_used' => 'nullable|string|max:200',
            'harvest_date' => 'nullable|date',
            'yield_kg' => 'nullable|numeric|min:0',
            'diesel_liters' => 'nullable|numeric|min:0',
            'water_usage_liters' => 'nullable|numeric|min:0',
        ]);

        FarmRecord::create($validated);
        return redirect()->route('admin.farm.index')->with('success', 'Farm record added.');
    }

    public function show(FarmRecord $farmRecord)
    {
        return view('admin.farm.show', compact('farmRecord'));
    }

    public function edit(FarmRecord $farmRecord)
    {
        return view('admin.farm.edit', compact('farmRecord'));
    }

    public function update(Request $request, FarmRecord $farmRecord)
    {
        $validated = $request->validate([
            'plot_name'          => 'required|string|max:100',
            'crop_type'          => 'required|string|max:100',
            'plantation_date'    => 'nullable|date',
            'fertilizer_used'    => 'nullable|string|max:200',
            'harvest_date'       => 'nullable|date',
            'yield_kg'           => 'nullable|numeric|min:0',
            'diesel_liters'      => 'nullable|numeric|min:0',
            'water_usage_liters' => 'nullable|numeric|min:0',
        ]);

        $farmRecord->update($validated);

        return redirect()->route('admin.farm.index')
            ->with('success', 'Farm record updated.');
    }

    public function destroy(FarmRecord $farmRecord)
    {
        $farmRecord->delete();
        return redirect()->route('admin.farm.index')
            ->with('success', 'Farm record deleted.');
    }
}