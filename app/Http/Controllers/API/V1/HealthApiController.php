<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Animal;
use App\Models\HealthRecord;
use Illuminate\Http\Request;

class HealthApiController extends Controller
{
    use ApiResponse;

    private function farmerAnimalIds(int $userId): \Illuminate\Support\Collection
    {
        return Animal::where('created_by', $userId)->pluck('id');
    }

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = HealthRecord::with('animal');

        if ($user->isFarmer()) {
            $query->whereIn('animal_id', $this->farmerAnimalIds($user->id));
        }

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }
        if ($request->filled('animal_id')) {
            $query->where('animal_id', $request->animal_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $records = $query->latest('date')->paginate($request->per_page ?? 15);

        return $this->paginated($records, 'Health records retrieved successfully.');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'animal_id'        => 'required|exists:animals,id',
            'record_type'      => 'required|in:Vaccination,Deworming,Treatment,Doctor Visit,Emergency',
            'date'             => 'required|date',
            'disease_symptoms' => 'nullable|string|max:255',
            'treatment_given'  => 'nullable|string|max:255',
            'medicine_used'    => 'nullable|string|max:255',
            'vet_doctor_name'  => 'nullable|string|max:100',
            'body_temp'        => 'nullable|numeric',
            'cost'             => 'nullable|numeric|min:0',
            'status'           => 'nullable|string|max:100',
        ]);

        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($validated['animal_id'])) {
            return $this->error('You can only add health records for your own animals.', 403);
        }

        $record = HealthRecord::create($validated);

        if (in_array($validated['record_type'], ['Treatment', 'Emergency'])) {
            Animal::where('id', $validated['animal_id'])->update(['health_status' => 'Under Treatment']);
        }

        return $this->success($record->load('animal'), 'Health record created successfully.', 201);
    }

    public function show(Request $request, HealthRecord $healthRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($healthRecord->animal_id)) {
            return $this->error('You do not have access to this record.', 403);
        }

        return $this->success($healthRecord->load('animal'), 'Health record retrieved successfully.');
    }

    public function update(Request $request, HealthRecord $healthRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($healthRecord->animal_id)) {
            return $this->error('You do not have access to this record.', 403);
        }

        $validated = $request->validate([
            'animal_id'        => 'sometimes|exists:animals,id',
            'record_type'      => 'sometimes|in:Vaccination,Deworming,Treatment,Doctor Visit,Emergency',
            'date'             => 'sometimes|date',
            'disease_symptoms' => 'nullable|string|max:255',
            'treatment_given'  => 'nullable|string|max:255',
            'medicine_used'    => 'nullable|string|max:255',
            'vet_doctor_name'  => 'nullable|string|max:100',
            'body_temp'        => 'nullable|numeric',
            'cost'             => 'nullable|numeric|min:0',
            'status'           => 'nullable|string|max:100',
        ]);

        if ($user->isFarmer() && isset($validated['animal_id']) && !$this->farmerAnimalIds($user->id)->contains($validated['animal_id'])) {
            return $this->error('You can only use your own animals.', 403);
        }

        $healthRecord->update($validated);

        return $this->success($healthRecord->load('animal'), 'Health record updated successfully.');
    }

    public function destroy(Request $request, HealthRecord $healthRecord)
    {
        $user = $request->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds($user->id)->contains($healthRecord->animal_id)) {
            return $this->error('You do not have access to this record.', 403);
        }

        $healthRecord->delete();

        return $this->success(null, 'Health record deleted successfully.');
    }
}