<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\FarmRecord;
use Illuminate\Http\Request;

class FarmApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = FarmRecord::query();

        if ($user->isFarmer()) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('crop_type')) {
            $query->where('crop_type', $request->crop_type);
        }
        if ($request->filled('plot_name')) {
            $query->where('plot_name', 'like', '%'.$request->plot_name.'%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('plantation_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('harvest_date', '<=', $request->date_to);
        }

        $records = $query->latest()->paginate($request->per_page ?? 15);

        return $this->paginated($records, 'Farm records retrieved successfully.');
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

        $validated['created_by'] = $request->user()->id;

        $record = FarmRecord::create($validated);

        return $this->success($record, 'Farm record created successfully.', 201);
    }

    public function show(Request $request, FarmRecord $farmRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && $farmRecord->created_by !== $user->id) {
            return $this->error('You do not have access to this farm record.', 403);
        }

        return $this->success($farmRecord, 'Farm record retrieved successfully.');
    }

    public function update(Request $request, FarmRecord $farmRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && $farmRecord->created_by !== $user->id) {
            return $this->error('You do not have access to this farm record.', 403);
        }

        $validated = $request->validate([
            'plot_name'          => 'sometimes|string|max:100',
            'crop_type'          => 'sometimes|string|max:100',
            'plantation_date'    => 'nullable|date',
            'fertilizer_used'    => 'nullable|string|max:200',
            'harvest_date'       => 'nullable|date',
            'yield_kg'           => 'nullable|numeric|min:0',
            'diesel_liters'      => 'nullable|numeric|min:0',
            'water_usage_liters' => 'nullable|numeric|min:0',
        ]);

        $farmRecord->update($validated);

        return $this->success($farmRecord, 'Farm record updated successfully.');
    }

    public function destroy(Request $request, FarmRecord $farmRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && $farmRecord->created_by !== $user->id) {
            return $this->error('You do not have access to this farm record.', 403);
        }

        $farmRecord->delete();

        return $this->success(null, 'Farm record deleted successfully.');
    }

    public function summary(Request $request)
    {
        $user  = $request->user();
        $query = FarmRecord::query();

        if ($user->isFarmer()) {
            $query->where('created_by', $user->id);
        }

        $data = [
            'total_records'     => (clone $query)->count(),
            'total_yield_kg'    => (clone $query)->sum('yield_kg'),
            'total_diesel_l'    => (clone $query)->sum('diesel_liters'),
            'total_water_l'     => (clone $query)->sum('water_usage_liters'),
            'active_plots'      => (clone $query)->distinct('plot_name')->count('plot_name'),
            'by_crop'           => (clone $query)->selectRaw('crop_type, SUM(yield_kg) as total_yield, COUNT(*) as count')
                ->groupBy('crop_type')->get(),
        ];

        return $this->success($data, 'Farm summary retrieved.');
    }
}