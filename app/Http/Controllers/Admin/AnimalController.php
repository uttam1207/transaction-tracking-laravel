<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalAction;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index(Request $request)
    {
        $query = Animal::with('actions');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('tag_number', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->pregnancy_status) {
            $query->where('pregnancy_status', $request->pregnancy_status);
        }

        $animals = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total' => Animal::count(),
            'milking' => Animal::where('pregnancy_status', '!=', 'Dry')->where('lactation_number', '>', 0)->count(),
            'dry' => Animal::where('pregnancy_status', 'Dry')->count(),
            'pregnant' => Animal::where('pregnancy_status', 'Pregnant')->count(),
            'calves' => Animal::where('lactation_number', 0)->count(),
        ];

        return view('admin.animals.index', compact('animals', 'summary'));
    }

    public function create()
    {
        return view('admin.animals.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_number' => 'required|string|unique:animals,tag_number',
            'name' => 'nullable|string|max:100',
            'breed' => 'required|string|max:100',
            'dob' => 'nullable|date',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'current_weight' => 'nullable|numeric|min:0',
            'lactation_number' => 'required|integer|min:0',
            'health_status' => 'required|in:Healthy,Sick,Under Treatment',
            'pregnancy_status' => 'required|in:Open,Inseminated,Pregnant,Dry',
            'shed_number' => 'required|string|max:50',
        ]);

        Animal::create($validated);

        return redirect()->route('admin.animals.index')
            ->with('success', 'Animal registered successfully.');
    }

    public function show(Animal $animal)
    {
        $animal->load('actions', 'milkEntries', 'breedingRecords', 'healthRecords');
        return view('admin.animals.show', compact('animal'));
    }

    public function storeAction(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'action_type' => 'required|in:Vaccination,Deworming,Heat Detection,AI,Pregnancy Check,Calving,Dry Off,Sale,Death',
            'action_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['animal_id'] = $animal->id;
        $validated['performed_by'] = auth()->id();

        AnimalAction::create($validated);

        // Update animal status if applicable
        if ($validated['action_type'] === 'Dry Off') {
            $animal->update(['pregnancy_status' => 'Dry']);
        } elseif ($validated['action_type'] === 'Sale') {
            $animal->update(['status' => 'Sold']);
        } elseif ($validated['action_type'] === 'Death') {
            $animal->update(['status' => 'Deceased']);
        }

        return back()->with('success', 'Animal action recorded.');
    }
    public function edit(Animal $animal)
    {
        return view('admin.animals.edit', compact('animal'));
    }

    public function update(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'tag_number'       => 'required|string|unique:animals,tag_number,' . $animal->id,
            'name'             => 'nullable|string|max:100',
            'breed'            => 'required|string|max:100',
            'dob'              => 'nullable|date',
            'purchase_date'    => 'nullable|date',
            'purchase_cost'    => 'nullable|numeric|min:0',
            'current_weight'   => 'nullable|numeric|min:0',
            'lactation_number' => 'required|integer|min:0',
            'health_status'    => 'required|in:Healthy,Sick,Under Treatment',
            'pregnancy_status' => 'required|in:Open,Inseminated,Pregnant,Dry',
            'shed_number'      => 'required|string|max:50',
            'status'           => 'required|in:Active,Sold,Deceased',
        ]);

        $animal->update($validated);

        return redirect()->route('admin.animals.show', $animal)
            ->with('success', 'Animal record updated.');
    }

    public function destroy(Animal $animal)
    {
        $animal->delete();

        return redirect()->route('admin.animals.index')
            ->with('success', 'Animal removed from register.');
    }
}