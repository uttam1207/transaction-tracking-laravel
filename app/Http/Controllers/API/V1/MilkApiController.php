<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Animal;
use App\Models\MilkEntry;
use Illuminate\Http\Request;

class MilkApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = MilkEntry::with('animal');

        if ($user->isFarmer()) {
            $query->where('recorded_by', $user->id);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }
        if ($request->filled('animal_id')) {
            $query->where('animal_id', $request->animal_id);
        }

        $entries = $query->orderByDesc('date')->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return $this->paginated($entries, 'Milk entries retrieved successfully.');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'animal_id'        => 'required|exists:animals,id',
            'date'             => 'required|date',
            'shift'            => 'required|in:Morning,Evening,Noon',
            'quantity_liters'  => 'required|numeric|min:0',
            'fat_percentage'   => 'nullable|numeric|min:0|max:20',
            'snf_percentage'   => 'nullable|numeric|min:0|max:20',
            'clr_value'        => 'nullable|numeric|min:0',
            'quality_rating'   => 'nullable|in:A,B,C',
            'rejected_liters'  => 'nullable|numeric|min:0',
        ]);

        // Ensure farmer records only their own animals
        if ($user->isFarmer()) {
            $farmerAnimalIds = Animal::where('created_by', $user->id)->pluck('id');
            if (!$farmerAnimalIds->contains($validated['animal_id'])) {
                return $this->error('You can only record milk for your own animals.', 403);
            }
        }

        $validated['recorded_by'] = $user->id;

        $entry = MilkEntry::create($validated);

        return $this->success($entry->load('animal'), 'Milk entry recorded successfully.', 201);
    }

    public function show(Request $request, MilkEntry $milkEntry)
    {
        $user = $request->user();
        if ($user->isFarmer() && $milkEntry->recorded_by !== $user->id) {
            return $this->error('You do not have access to this entry.', 403);
        }

        return $this->success($milkEntry->load('animal'), 'Milk entry retrieved successfully.');
    }

    public function update(Request $request, MilkEntry $milkEntry)
    {
        $user = $request->user();
        if ($user->isFarmer() && $milkEntry->recorded_by !== $user->id) {
            return $this->error('You do not have access to this entry.', 403);
        }

        $validated = $request->validate([
            'quantity_liters' => 'sometimes|numeric|min:0',
            'fat_percentage'  => 'nullable|numeric|min:0|max:20',
            'snf_percentage'  => 'nullable|numeric|min:0|max:20',
            'clr_value'       => 'nullable|numeric|min:0',
            'quality_rating'  => 'nullable|in:A,B,C',
            'rejected_liters' => 'nullable|numeric|min:0',
        ]);

        $milkEntry->update($validated);

        return $this->success($milkEntry->load('animal'), 'Milk entry updated successfully.');
    }

    public function destroy(Request $request, MilkEntry $milkEntry)
    {
        $user = $request->user();
        if ($user->isFarmer() && $milkEntry->recorded_by !== $user->id) {
            return $this->error('You do not have access to this entry.', 403);
        }

        $milkEntry->delete();

        return $this->success(null, 'Milk entry deleted successfully.');
    }

    public function summary(Request $request)
    {
        $user  = $request->user();
        $query = MilkEntry::query();

        if ($user->isFarmer()) {
            $query->where('recorded_by', $user->id);
        }

        $data = [
            'today'         => (clone $query)->whereDate('date', today())->sum('quantity_liters'),
            'this_month'    => (clone $query)->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('quantity_liters'),
            'this_year'     => (clone $query)->whereYear('date', now()->year)->sum('quantity_liters'),
            'morning_today' => (clone $query)->whereDate('date', today())->where('shift', 'Morning')->sum('quantity_liters'),
            'evening_today' => (clone $query)->whereDate('date', today())->where('shift', 'Evening')->sum('quantity_liters'),
            'daily_trend'   => (clone $query)
                ->selectRaw('date, SUM(quantity_liters) as total')
                ->whereDate('date', '>=', now()->subDays(29))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return $this->success($data, 'Milk summary retrieved.');
    }
}