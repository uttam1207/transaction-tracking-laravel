<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\BreedingRecord;
use Illuminate\Http\Request;

class BreedingController extends Controller
{
    public function index()
    {
        $records = BreedingRecord::with('animal')->latest()->paginate(15);
        $animals = Animal::where('status', 'Active')->orderBy('tag_number')->get();

        $summary = [
            'inseminated' => BreedingRecord::where('status', 'AI Done')->count(),
            'pregnant' => BreedingRecord::where('status', 'Confirmed Pregnant')->count(),
            'expected_calving_month' => BreedingRecord::whereMonth('expected_calving_date', now()->month)->count(),
            'repeat_breeders' => BreedingRecord::where('status', 'Repeat Breeder')->count(),
        ];

        return view('admin.breeding.index', compact('records', 'animals', 'summary'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'animal_id' => 'required|exists:animals,id',
            'heat_date' => 'required|date',
            'ai_date' => 'nullable|date',
            'bull_semen_code' => 'nullable|string|max:100',
            'status' => 'required|in:Heat Detected,AI Done,Confirmed Pregnant,Calved,Repeat Breeder',
            'expected_calving_date' => 'nullable|date',
        ]);

        BreedingRecord::create($validated);

        if ($validated['status'] === 'Confirmed Pregnant') {
            Animal::where('id', $validated['animal_id'])->update(['pregnancy_status' => 'Pregnant']);
        }

        return redirect()->route('admin.breeding.index')->with('success', 'Breeding record saved.');
    }

    public function show(BreedingRecord $breedingRecord)
    {
        $breedingRecord->load('animal');
        return view('admin.breeding.show', compact('breedingRecord'));
    }

    public function edit(BreedingRecord $breedingRecord)
    {
        $animals = Animal::where('status', 'Active')->orderBy('tag_number')->get();
        return view('admin.breeding.edit', compact('breedingRecord', 'animals'));
    }

    public function update(Request $request, BreedingRecord $breedingRecord)
    {
        $validated = $request->validate([
            'animal_id'             => 'required|exists:animals,id',
            'heat_date'             => 'required|date',
            'ai_date'               => 'nullable|date',
            'bull_semen_code'       => 'nullable|string|max:100',
            'status'                => 'required|in:Heat Detected,AI Done,Confirmed Pregnant,Calved,Repeat Breeder',
            'pregnancy_check_date'  => 'nullable|date',
            'is_pregnant'           => 'nullable|boolean',
            'expected_calving_date' => 'nullable|date',
            'actual_calving_date'   => 'nullable|date',
            'calf_tag_number'       => 'nullable|string|max:50',
        ]);

        $breedingRecord->update($validated);

        if ($validated['status'] === 'Confirmed Pregnant') {
            Animal::where('id', $validated['animal_id'])->update(['pregnancy_status' => 'Pregnant']);
        } elseif ($validated['status'] === 'Calved') {
            Animal::where('id', $validated['animal_id'])->update(['pregnancy_status' => 'Open']);
        }

        return redirect()->route('admin.breeding.index')
            ->with('success', 'Breeding record updated.');
    }

    public function destroy(BreedingRecord $breedingRecord)
    {
        $breedingRecord->delete();
        return redirect()->route('admin.breeding.index')
            ->with('success', 'Breeding record deleted.');
    }
}