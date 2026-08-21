<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\HealthRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    private function farmerAnimalIds(): \Illuminate\Support\Collection
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return Animal::where('created_by', $user->getAuthIdentifier())->pluck('id');
    }

    private function authorizeFarmerHealth(HealthRecord $record): void
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        if ($user->isFarmer() && !$this->farmerAnimalIds()->contains($record->animal_id)) {
            abort(403, 'You do not have access to this health record.');
        }
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user     = auth()->user();
        $isFarmer = $user->isFarmer();

        $query = HealthRecord::with('animal');

        if ($isFarmer) {
            $farmerAnimalIds = $this->farmerAnimalIds();
            $query->whereIn('animal_id', $farmerAnimalIds);
        }

        if ($request->search) {
            $query->whereHas('animal', fn($q) => $q
                ->where('tag_number', 'like', '%'.$request->search.'%')
                ->orWhere('name', 'like', '%'.$request->search.'%'));
        }
        if ($request->record_type) {
            $query->where('record_type', $request->record_type);
        }

        $records = $query->latest('date')->paginate(15)->withQueryString();

        $animalsQuery = Animal::where('status', 'Active')->orderBy('tag_number');
        if ($isFarmer) {
            $animalsQuery->where('created_by', $user->getAuthIdentifier());
        }
        $animals = $animalsQuery->get();

        if ($isFarmer) {
            $farmerAnimalIds = $this->farmerAnimalIds();
            $summary = [
                'sick_animals'        => Animal::whereIn('id', $farmerAnimalIds)->where('health_status', 'Sick')->count(),
                'under_treatment'     => Animal::whereIn('id', $farmerAnimalIds)->where('health_status', 'Under Treatment')->count(),
                'recent_vaccinations' => HealthRecord::whereIn('animal_id', $farmerAnimalIds)->where('record_type', 'Vaccination')->whereMonth('date', now()->month)->count(),
            ];
        } else {
            $summary = [
                'sick_animals'        => Animal::where('health_status', 'Sick')->count(),
                'under_treatment'     => Animal::where('health_status', 'Under Treatment')->count(),
                'recent_vaccinations' => HealthRecord::where('record_type', 'Vaccination')->whereMonth('date', now()->month)->count(),
            ];
        }

        return view('admin.health.index', compact('records', 'animals', 'summary', 'isFarmer'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $animalsQuery = Animal::where('status', 'Active')->orderBy('tag_number');
        if ($user->isFarmer()) {
            $animalsQuery->where('created_by', $user->getAuthIdentifier());
        }
        $animals = $animalsQuery->get();
        return view('admin.health.create', compact('animals'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

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
            'report_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Ensure farmer can only add records for their own animals
        if ($user->isFarmer() && !$this->farmerAnimalIds()->contains($validated['animal_id'])) {
            return back()->with('error', 'You can only add health records for your own animals.');
        }

        if ($request->hasFile('report_file')) {
            $validated['report_path'] = $request->file('report_file')->store('health-reports', 'uploads');
        }
        unset($validated['report_file']);

        HealthRecord::create($validated);

        if ($validated['record_type'] === 'Treatment' || $validated['record_type'] === 'Emergency') {
            Animal::where('id', $validated['animal_id'])->update(['health_status' => 'Under Treatment']);
        }

        return redirect()->route('admin.health.index')->with('success', 'Health record logged.');
    }

    public function show(HealthRecord $healthRecord)
    {
        $this->authorizeFarmerHealth($healthRecord);
        $healthRecord->load('animal');
        return view('admin.health.show', compact('healthRecord'));
    }

    public function edit(HealthRecord $healthRecord)
    {
        $this->authorizeFarmerHealth($healthRecord);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $animalsQuery = Animal::where('status', 'Active')->orderBy('tag_number');
        if ($user->isFarmer()) {
            $animalsQuery->where('created_by', $user->getAuthIdentifier());
        }
        $animals = $animalsQuery->get();

        return view('admin.health.edit', compact('healthRecord', 'animals'));
    }

    public function update(Request $request, HealthRecord $healthRecord)
    {
        $this->authorizeFarmerHealth($healthRecord);

        /** @var \App\Models\User $user */
        $user = auth()->user();

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
            'report_file'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Ensure farmer can only assign their own animals
        if ($user->isFarmer() && !$this->farmerAnimalIds()->contains($validated['animal_id'])) {
            return back()->with('error', 'You can only use your own animals in health records.');
        }

        if ($request->hasFile('report_file')) {
            if ($healthRecord->report_path) {
                Storage::disk('uploads')->delete($healthRecord->report_path);
            }
            $validated['report_path'] = $request->file('report_file')->store('health-reports', 'uploads');
        }
        unset($validated['report_file']);

        $healthRecord->update($validated);

        return redirect()->route('admin.health.index')
            ->with('success', 'Health record updated.');
    }

    public function destroy(HealthRecord $healthRecord)
    {
        $this->authorizeFarmerHealth($healthRecord);
        $healthRecord->delete();
        return redirect()->route('admin.health.index')
            ->with('success', 'Health record deleted.');
    }
}