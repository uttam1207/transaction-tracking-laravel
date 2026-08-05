<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use App\Models\Animal;
use Illuminate\Http\Request;

class BreedController extends Controller
{
    public function index()
    {
        $breeds = Breed::withCount('animals')->orderBy('animal_type')->orderBy('name')->get();
        return view('admin.breeds.index', compact('breeds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:breeds,name',
            'animal_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        Breed::create($validated);

        return back()->with('success', 'Breed "' . $validated['name'] . '" added successfully.');
    }

    public function update(Request $request, Breed $breed)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:breeds,name,' . $breed->id,
            'animal_type' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ]);

        // If name changed, update all animals that used the old breed name
        if ($breed->name !== $validated['name']) {
            Animal::where('breed', $breed->name)->update(['breed' => $validated['name']]);
        }

        $breed->update($validated);

        return back()->with('success', 'Breed updated successfully.');
    }

    public function destroy(Breed $breed)
    {
        $usedBy = Animal::where('breed', $breed->name)->count();

        if ($usedBy > 0) {
            return back()->with('error', "Cannot delete \"{$breed->name}\" — it is assigned to {$usedBy} animal(s). Reassign those animals first.");
        }

        $breed->delete();

        return back()->with('success', 'Breed deleted successfully.');
    }
}
