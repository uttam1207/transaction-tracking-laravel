<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\HealthRecord;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function index()
    {
        $records = HealthRecord::with('animal')->latest('date')->paginate(15);
        $animals = Animal::where('status', 'Active')->orderBy('tag_number')->get();

        $summary = [
            'sick_animals' => Animal::where('health_status', 'Sick')->count(),
            'under_treatment' => Animal::where('health_status', 'Under Treatment')->count(),
            'recent_vaccinations' => HealthRecord::where('record_type', 'Vaccination')->whereMonth('date', now()->month)->count(),
        ];

        return view('admin.health.index', compact('records', 'animals', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'record_type' => 'required|in:Vaccination,Deworming,Treatment,Doctor Visit,Emergency',
            'date' => 'required|date',
            'disease_symptoms' => 'nullable|string|max:255',
            'treatment_given' => 'nullable|string|max:255',
            'medicine_used' => 'nullable|string|max:255',
            'vet_doctor_name' => 'nullable|string|max:100',
            'body_temp' => 'nullable|numeric',
            'cost' => 'nullable|numeric|min:0',
        ]);

        HealthRecord::create($validated);

        if ($validated['record_type'] === 'Treatment' || $validated['record_type'] === 'Emergency') {
            Animal::where('id', $validated['animal_id'])->update(['health_status' => 'Under Treatment']);
        }

        return redirect()->route('admin.health.index')->with('success', 'Health record logged.');
    }

    public function show(HealthRecord $healthRecord)
    {
        $healthRecord->load('animal');
        return view('admin.health.show', compact('healthRecord'));
    }

    public function edit(HealthRecord $healthRecord)
    {
        $animals = Animal::where('status', 'Active')->orderBy('tag_number')->get();
        return view('admin.health.edit', compact('healthRecord', 'animals'));
    }

    public function update(Request $request, HealthRecord $healthRecord)
    {
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

        $healthRecord->update($validated);

        return redirect()->route('admin.health.index')
            ->with('success', 'Health record updated.');
    }

    public function destroy(HealthRecord $healthRecord)
    {
        $healthRecord->delete();
        return redirect()->route('admin.health.index')
            ->with('success', 'Health record deleted.');
    }
}