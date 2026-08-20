<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\MilkEntry;
use Illuminate\Http\Request;

class MilkController extends Controller
{
    /** Distinct active sheds with total animal count per shed */
    private function getSheds()
    {
        return Animal::where('status', 'Active')
            ->select('shed_number')
            ->selectRaw('COUNT(*) as animal_count')
            ->groupBy('shed_number')
            ->orderBy('shed_number')
            ->get();
    }

    /** Abort 403 if the current user is a farmer who didn't record this entry. */
    private function authorizeFarmerMilkEntry(MilkEntry $entry): void
    {
        $user = auth()->user();
        if ($user->isFarmer() && $entry->recorded_by !== $user->id) {
            abort(403);
        }
    }

    /** Active animals visible to the current user (farmer sees only their own). */
    private function visibleAnimals()
    {
        $user  = auth()->user();
        $query = Animal::where('status', 'Active')->orderBy('tag_number');
        if ($user->isFarmer()) {
            $query->where('created_by', $user->id);
        }
        return $query->get();
    }

    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = MilkEntry::with('animal');

        // Farmer sees only their own entries
        if ($user->isFarmer()) {
            $query->where('recorded_by', $user->id);
        }

        if ($request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->shift) {
            $query->where('shift', $request->shift);
        }
        if ($request->entry_type) {
            $query->where('entry_type', $request->entry_type);
        }
        if ($request->shed_number) {
            $query->where('shed_number', $request->shed_number);
        }

        $entries = $query->latest()->paginate(20)->withQueryString();

        // Summary stats scoped for farmer
        $base = $user->isFarmer()
            ? MilkEntry::where('recorded_by', $user->id)
            : MilkEntry::query();

        $todayTotal    = (clone $base)->whereDate('date', today())->sum('quantity_liters');
        $morningTotal  = (clone $base)->whereDate('date', today())->where('shift', 'Morning')->sum('quantity_liters');
        $eveningTotal  = (clone $base)->whereDate('date', today())->where('shift', 'Evening')->sum('quantity_liters');
        $avgFat        = (clone $base)->whereDate('date', today())->avg('fat_percentage') ?? 7.5;
        $rejectedTotal = (clone $base)->whereDate('date', today())->sum('rejected_liters');

        $sheds   = $this->getSheds();
        $animals = $this->visibleAnimals();

        return view('admin.milk.index', compact(
            'entries', 'todayTotal', 'morningTotal', 'eveningTotal', 'avgFat', 'rejectedTotal', 'animals', 'sheds'
        ));
    }

    public function create()
    {
        $animals = $this->visibleAnimals();
        $sheds   = $this->getSheds();
        return view('admin.milk.create', compact('animals', 'sheds'));
    }

    public function store(Request $request)
    {
        $entryType = $request->input('entry_type', 'per_animal');

        $rules = [
            'date'             => 'required|date',
            'shift'            => 'required|in:Morning,Evening',
            'entry_type'       => 'required|in:per_animal,per_shed,entire_farm',
            'quantity_liters'  => 'required|numeric|min:0.1',
            'fat_percentage'   => 'required|numeric|min:1|max:15',
            'snf_percentage'   => 'required|numeric|min:1|max:15',
            'clr_value'        => 'nullable|numeric|min:0|max:50',
            'quality_rating'   => 'required|string',
            'rejected_liters'  => 'nullable|numeric|min:0',
        ];

        if ($entryType === 'per_animal') {
            $rules['animal_id']   = 'required|exists:animals,id';
            $rules['shed_number'] = 'nullable';
        } elseif ($entryType === 'per_shed') {
            $rules['animal_id']   = 'nullable';
            $rules['shed_number'] = 'required|string|max:100';
        } else {
            $rules['animal_id']   = 'nullable';
            $rules['shed_number'] = 'nullable';
        }

        $validated = $request->validate($rules);
        $validated['recorded_by'] = auth()->id();

        // Clear irrelevant fields
        if ($entryType !== 'per_animal') {
            $validated['animal_id'] = null;
        }
        if ($entryType !== 'per_shed') {
            $validated['shed_number'] = null;
        }

        MilkEntry::create($validated);

        return redirect()->route('admin.milk.index')
            ->with('success', 'Milk entry recorded successfully.');
    }

    public function show(MilkEntry $milkEntry)
    {
        $this->authorizeFarmerMilkEntry($milkEntry);
        $milkEntry->load('animal');
        return view('admin.milk.show', compact('milkEntry'));
    }

    public function edit(MilkEntry $milkEntry)
    {
        $this->authorizeFarmerMilkEntry($milkEntry);
        $animals = $this->visibleAnimals();
        $sheds   = $this->getSheds();
        return view('admin.milk.edit', compact('milkEntry', 'animals', 'sheds'));
    }

    public function update(Request $request, MilkEntry $milkEntry)
    {
        $this->authorizeFarmerMilkEntry($milkEntry);
        $entryType = $request->input('entry_type', $milkEntry->entry_type ?? 'per_animal');

        $rules = [
            'date'            => 'required|date',
            'shift'           => 'required|in:Morning,Evening',
            'entry_type'      => 'required|in:per_animal,per_shed,entire_farm',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat_percentage'  => 'required|numeric|min:1|max:15',
            'snf_percentage'  => 'required|numeric|min:1|max:15',
            'clr_value'       => 'nullable|numeric|min:0|max:50',
            'quality_rating'  => 'required|string',
            'rejected_liters' => 'nullable|numeric|min:0',
        ];

        if ($entryType === 'per_animal') {
            $rules['animal_id']   = 'required|exists:animals,id';
            $rules['shed_number'] = 'nullable';
        } elseif ($entryType === 'per_shed') {
            $rules['animal_id']   = 'nullable';
            $rules['shed_number'] = 'required|string|max:100';
        } else {
            $rules['animal_id']   = 'nullable';
            $rules['shed_number'] = 'nullable';
        }

        $validated = $request->validate($rules);

        if ($entryType !== 'per_animal') {
            $validated['animal_id'] = null;
        }
        if ($entryType !== 'per_shed') {
            $validated['shed_number'] = null;
        }

        $milkEntry->update($validated);

        return redirect()->route('admin.milk.index')
            ->with('success', 'Milk entry updated.');
    }

    public function destroy(MilkEntry $milkEntry)
    {
        $this->authorizeFarmerMilkEntry($milkEntry);
        $milkEntry->delete();
        return redirect()->route('admin.milk.index')
            ->with('success', 'Milk entry deleted.');
    }
}
