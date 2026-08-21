<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmRecord;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    private function authorizeFarmerFarm(FarmRecord $farmRecord): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if ($user->isFarmer() && $farmRecord->created_by !== $user->getAuthIdentifier()) {
            abort(403, 'You do not have access to this farm record.');
        }
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user     = auth()->user();
        $isFarmer = $user->isFarmer();

        $query = FarmRecord::query();

        if ($isFarmer) {
            $query->where('created_by', $user->getAuthIdentifier());
        }

        if ($request->search) {
            $query->where(fn($q) => $q
                ->where('plot_name', 'like', '%'.$request->search.'%')
                ->orWhere('crop_type', 'like', '%'.$request->search.'%'));
        }
        if ($request->crop_type) {
            $query->where('crop_type', $request->crop_type);
        }

        $records  = $query->latest()->paginate(15)->withQueryString();
        $cropTypes = FarmRecord::when($isFarmer, fn($q) => $q->where('created_by', $user->getAuthIdentifier()))
            ->distinct()->pluck('crop_type')->sort()->values();

        $summaryQuery = FarmRecord::query();
        if ($isFarmer) {
            $summaryQuery->where('created_by', $user->getAuthIdentifier());
        }
        $summary = [
            'total_yield'   => (clone $summaryQuery)->sum('yield_kg'),
            'total_diesel'  => (clone $summaryQuery)->sum('diesel_liters'),
            'total_water'   => (clone $summaryQuery)->sum('water_usage_liters'),
            'active_plots'  => (clone $summaryQuery)->distinct('plot_name')->count('plot_name'),
        ];

        return view('admin.farm.index', compact('records', 'summary', 'cropTypes', 'isFarmer'));
    }

    public function create()
    {
        return view('admin.farm.create');
    }

    public function store(Request $request)
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

        $validated['created_by'] = auth()->user()?->id;

        FarmRecord::create($validated);
        return redirect()->route('admin.farm.index')->with('success', 'Farm record added.');
    }

    public function show(FarmRecord $farmRecord)
    {
        $this->authorizeFarmerFarm($farmRecord);
        return view('admin.farm.show', compact('farmRecord'));
    }

    public function edit(FarmRecord $farmRecord)
    {
        $this->authorizeFarmerFarm($farmRecord);
        return view('admin.farm.edit', compact('farmRecord'));
    }

    public function update(Request $request, FarmRecord $farmRecord)
    {
        $this->authorizeFarmerFarm($farmRecord);

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
        $this->authorizeFarmerFarm($farmRecord);
        $farmRecord->delete();
        return redirect()->route('admin.farm.index')
            ->with('success', 'Farm record deleted.');
    }
}