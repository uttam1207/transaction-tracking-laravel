<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FixedAsset;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = FixedAsset::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $request->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->asset_type) {
            $query->where('asset_type', $request->asset_type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $assets = $query->latest()->paginate(20)->withQueryString();

        // Summary stats
        $all        = FixedAsset::all();
        $totalCount = $all->count();
        $totalPurchase  = $all->sum('purchase_value');
        $totalBookValue = $all->sum(fn($a) => $a->book_value);
        $totalDepr      = $all->sum(fn($a) => $a->accumulated_depreciation);

        $typeBreakdown = FixedAsset::selectRaw('asset_type, COUNT(*) as count, SUM(purchase_value) as total')
            ->groupBy('asset_type')
            ->get();

        return view('admin.fixed-assets.index', compact(
            'assets', 'totalCount', 'totalPurchase', 'totalBookValue', 'totalDepr', 'typeBreakdown'
        ));
    }

    public function create()
    {
        return view('admin.fixed-assets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:200',
            'asset_type'         => 'required|in:' . implode(',', array_keys(FixedAsset::ASSET_TYPES)),
            'description'        => 'nullable|string|max:1000',
            'serial_number'      => 'nullable|string|max:100',
            'location'           => 'nullable|string|max:200',
            'vendor'             => 'nullable|string|max:200',
            'purchase_date'      => 'required|date',
            'purchase_value'     => 'required|numeric|min:0',
            'salvage_value'      => 'nullable|numeric|min:0',
            'useful_life_years'  => 'required|integer|min:1|max:99',
            'depreciation_method'=> 'required|in:straight_line,reducing_balance,none',
            'depreciation_rate'  => 'nullable|numeric|min:0|max:100',
            'status'             => 'required|in:' . implode(',', array_keys(FixedAsset::STATUSES)),
            'disposal_date'      => 'nullable|date|after_or_equal:purchase_date',
            'disposal_value'     => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:2000',
        ]);

        $validated['asset_code']   = FixedAsset::generateCode($validated['asset_type']);
        $validated['salvage_value'] = $validated['salvage_value'] ?? 0;
        $validated['created_by']   = auth()->id();

        // Auto-compute depreciation_rate for straight_line if not provided
        if ($validated['depreciation_method'] === 'straight_line' && empty($validated['depreciation_rate'])) {
            $validated['depreciation_rate'] = $validated['useful_life_years'] > 0
                ? round(100 / $validated['useful_life_years'], 2)
                : 0;
        }

        FixedAsset::create($validated);

        return redirect()->route('admin.fixed-assets.index')
            ->with('success', 'Fixed asset "' . $validated['name'] . '" registered successfully.');
    }

    public function show(FixedAsset $fixedAsset)
    {
        return view('admin.fixed-assets.show', compact('fixedAsset'));
    }

    public function edit(FixedAsset $fixedAsset)
    {
        return view('admin.fixed-assets.edit', compact('fixedAsset'));
    }

    public function update(Request $request, FixedAsset $fixedAsset)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:200',
            'asset_type'         => 'required|in:' . implode(',', array_keys(FixedAsset::ASSET_TYPES)),
            'description'        => 'nullable|string|max:1000',
            'serial_number'      => 'nullable|string|max:100',
            'location'           => 'nullable|string|max:200',
            'vendor'             => 'nullable|string|max:200',
            'purchase_date'      => 'required|date',
            'purchase_value'     => 'required|numeric|min:0',
            'salvage_value'      => 'nullable|numeric|min:0',
            'useful_life_years'  => 'required|integer|min:1|max:99',
            'depreciation_method'=> 'required|in:straight_line,reducing_balance,none',
            'depreciation_rate'  => 'nullable|numeric|min:0|max:100',
            'status'             => 'required|in:' . implode(',', array_keys(FixedAsset::STATUSES)),
            'disposal_date'      => 'nullable|date|after_or_equal:purchase_date',
            'disposal_value'     => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:2000',
        ]);

        $validated['salvage_value'] = $validated['salvage_value'] ?? 0;

        if ($validated['depreciation_method'] === 'straight_line' && empty($validated['depreciation_rate'])) {
            $validated['depreciation_rate'] = $validated['useful_life_years'] > 0
                ? round(100 / $validated['useful_life_years'], 2)
                : 0;
        }

        $fixedAsset->update($validated);

        return redirect()->route('admin.fixed-assets.show', $fixedAsset)
            ->with('success', 'Asset updated successfully.');
    }

    public function destroy(FixedAsset $fixedAsset)
    {
        $name = $fixedAsset->name;
        $fixedAsset->delete();
        return redirect()->route('admin.fixed-assets.index')
            ->with('success', "Asset \"{$name}\" has been removed.");
    }
}
