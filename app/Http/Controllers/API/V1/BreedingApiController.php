<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Animal;
use App\Models\BreedingRecord;
use Illuminate\Http\Request;

class BreedingApiController extends Controller
{
    use ApiResponse;

    private function farmerAnimalIds(int $userId): \Illuminate\Support\Collection
    {
        return Animal::where('created_by', $userId)->pluck('id');
    }

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = BreedingRecord::with('animal');

        if ($user->isFarmer()) {
            $query->whereIn('animal_id', $this->farmerAnimalIds($user->id));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('animal_id')) {
            $query->where('animal_id', $request->animal_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('heat_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('heat_date', '<=', $request->date_to);
        }

        $records = $query->latest()->paginate($request->per_page ?? 15);

        return $this->paginated($records, 'Breeding records retrieved successfully.');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'animal_id'             => 'required|exists:animals,id',
            'heat_date'             => 'required|date',
            'ai_date'               => 'nullable|date',
            'bull_semen_code'       => 'nullable|string|max:100',
            'status'                => 'required|in:Heat Detected,AI Done,Confirmed Pregnant,Calved,Repeat Breeder,Not Pregnant',
            'expected_calving_date' => 'nullable|date',
        ]);

        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($validated['animal_id'])) {
            return $this->error('You can only add breeding records for your own animals.', 403);
        }

        $record = BreedingRecord::create($validated);

        if ($validated['status'] === 'Confirmed Pregnant') {
            Animal::where('id', $validated['animal_id'])->update(['pregnancy_status' => 'Pregnant']);
        }

        return $this->success($record->load('animal'), 'Breeding record created successfully.', 201);
    }

    public function show(Request $request, BreedingRecord $breedingRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($breedingRecord->animal_id)) {
            return $this->error('You do not have access to this record.', 403);
        }

        return $this->success($breedingRecord->load('animal'), 'Breeding record retrieved successfully.');
    }

    public function update(Request $request, BreedingRecord $breedingRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($breedingRecord->animal_id)) {
            return $this->error('You do not have access to this record.', 403);
        }

        $validated = $request->validate([
            'animal_id'             => 'sometimes|exists:animals,id',
            'heat_date'             => 'sometimes|date',
            'ai_date'               => 'nullable|date',
            'bull_semen_code'       => 'nullable|string|max:100',
            'status'                => 'sometimes|in:Heat Detected,AI Done,Confirmed Pregnant,Calved,Repeat Breeder,Not Pregnant',
            'pregnancy_check_date'  => 'nullable|date',
            'is_pregnant'           => 'nullable|boolean',
            'expected_calving_date' => 'nullable|date',
            'actual_calving_date'   => 'nullable|date',
            'calf_tag_number'       => 'nullable|string|max:50',
        ]);

        if ($user->isFarmer() && isset($validated['animal_id']) && !$this->farmerAnimalIds($user->id)->contains($validated['animal_id'])) {
            return $this->error('You can only use your own animals.', 403);
        }

        $breedingRecord->update($validated);

        if (($validated['status'] ?? '') === 'Confirmed Pregnant') {
            Animal::where('id', $breedingRecord->animal_id)->update(['pregnancy_status' => 'Pregnant']);
        } elseif (in_array($validated['status'] ?? '', ['Calved', 'Not Pregnant'])) {
            Animal::where('id', $breedingRecord->animal_id)->update(['pregnancy_status' => 'Open']);
        }

        return $this->success($breedingRecord->load('animal'), 'Breeding record updated successfully.');
    }

    public function destroy(Request $request, BreedingRecord $breedingRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($breedingRecord->animal_id)) {
            return $this->error('You do not have access to this record.', 403);
        }

        $breedingRecord->delete();

        return $this->success(null, 'Breeding record deleted successfully.');
    }
}