<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\AnimalAction;
use App\Models\ActionType;
use App\Models\Breed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->breed) {
            $query->where('breed', $request->breed);
        }

        $animals = $query->latest()->paginate(15)->withQueryString();

        $summary = [
            'total' => Animal::count(),
            'milking' => Animal::where('pregnancy_status', '!=', 'Dry')->where('lactation_number', '>', 0)->count(),
            'dry' => Animal::where('pregnancy_status', 'Dry')->count(),
            'pregnant' => Animal::where('pregnancy_status', 'Pregnant')->count(),
            'calves' => Animal::where('lactation_number', 0)->count(),
        ];

        $allBreeds = Breed::orderBy('animal_type')->orderBy('name')->get();

        return view('admin.animals.index', compact('animals', 'summary', 'allBreeds'));
    }

    public function create()
    {
        $breeds = Breed::orderBy('animal_type')->orderBy('name')->get();
        return view('admin.animals.create', compact('breeds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_number'       => 'required|string|unique:animals,tag_number',
            'name'             => 'nullable|string|max:100',
            'breed'            => 'required|string|max:100',
            'dob'              => 'nullable|date',
            'born_in_farm'     => 'nullable|boolean',
            'purchase_from'    => 'nullable|string|max:200',
            'purchase_date'    => 'nullable|date',
            'purchase_cost'    => 'nullable|numeric|min:0',
            'current_weight'   => 'nullable|numeric|min:0',
            'lactation_number' => 'required|integer|min:0',
            'health_status'    => 'required|in:Healthy,Sick,Under Treatment',
            'pregnancy_status' => 'required|in:Open,Inseminated,Pregnant,Dry',
            'shed_number'      => 'required|string|max:50',
        ]);

        $validated['born_in_farm'] = $request->boolean('born_in_farm');

        Animal::create($validated);

        return redirect()->route('admin.animals.index')
            ->with('success', 'Animal registered successfully.');
    }

    public function show(Animal $animal)
    {
        $animal->load('actions', 'milkEntries', 'breedingRecords', 'healthRecords');
        $actionTypes = ActionType::active()->orderByDesc('is_system')->orderBy('name')->get();
        return view('admin.animals.show', compact('animal', 'actionTypes'));
    }

    public function storeAction(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'action_type'  => 'required|string|max:100|exists:action_types,name',
            'action_date'  => 'required|date',
            'cost'         => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:1000',
            'document'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $validated['animal_id']   = $animal->id;
        $validated['performed_by'] = auth()->id();

        // Handle optional document upload
        if ($request->hasFile('document')) {
            $validated['document_path'] = $request->file('document')
                ->store('animal-docs', 'public');
        }

        unset($validated['document']); // not a DB column
        AnimalAction::create($validated);

        // Auto-update animal status based on action type
        if ($validated['action_type'] === 'Dry Off') {
            $animal->update(['pregnancy_status' => 'Dry']);
        } elseif ($validated['action_type'] === 'Sale') {
            $animal->update(['status' => 'Sold']);
        } elseif ($validated['action_type'] === 'Death') {
            $animal->update(['status' => 'Deceased']);
        }

        return back()->with('success', 'Action "' . $validated['action_type'] . '" recorded successfully.');
    }

    public function destroyAction(Animal $animal, AnimalAction $action)
    {
        // Delete the uploaded file if it exists
        if ($action->document_path) {
            Storage::disk('public')->delete($action->document_path);
        }

        $action->delete();

        return back()->with('success', 'Action record deleted.');
    }

    public function edit(Animal $animal)
    {
        $breeds = Breed::orderBy('animal_type')->orderBy('name')->get();
        return view('admin.animals.edit', compact('animal', 'breeds'));
    }

    public function update(Request $request, Animal $animal)
    {
        $validated = $request->validate([
            'tag_number'       => 'required|string|unique:animals,tag_number,' . $animal->id,
            'name'             => 'nullable|string|max:100',
            'breed'            => 'required|string|max:100',
            'dob'              => 'nullable|date',
            'born_in_farm'     => 'nullable|boolean',
            'purchase_from'    => 'nullable|string|max:200',
            'purchase_date'    => 'nullable|date',
            'purchase_cost'    => 'nullable|numeric|min:0',
            'current_weight'   => 'nullable|numeric|min:0',
            'lactation_number' => 'required|integer|min:0',
            'health_status'    => 'required|in:Healthy,Sick,Under Treatment',
            'pregnancy_status' => 'required|in:Open,Inseminated,Pregnant,Dry',
            'shed_number'      => 'required|string|max:50',
            'status'           => 'required|in:Active,Sold,Deceased',
        ]);

        $validated['born_in_farm'] = $request->boolean('born_in_farm');

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