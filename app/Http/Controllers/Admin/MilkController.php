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

    public function index(Request $request)
    {
        $query = MilkEntry::with('animal');

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

        $todayTotal    = MilkEntry::whereDate('date', today())->sum('quantity_liters');
        $morningTotal  = MilkEntry::whereDate('date', today())->where('shift', 'Morning')->sum('quantity_liters');
        $eveningTotal  = MilkEntry::whereDate('date', today())->where('shift', 'Evening')->sum('quantity_liters');
        $avgFat        = MilkEntry::whereDate('date', today())->avg('fat_percentage') ?? 7.5;
        $rejectedTotal = MilkEntry::whereDate('date', today())->sum('rejected_liters');

        $sheds   = $this->getSheds();
        $animals = Animal::where('status', 'Active')->orderBy('tag_number')->get();

        return view('admin.milk.index', compact(
            'entries', 'todayTotal', 'morningTotal', 'eveningTotal', 'avgFat', 'rejectedTotal', 'animals', 'sheds'
        ));
    }

    public function create()
    {
        $animals = Animal::where('status', 'Active')->orderBy('tag_number')->get();
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
        $milkEntry->load('animal');
        return view('admin.milk.show', compact('milkEntry'));
    }

    public function edit(MilkEntry $milkEntry)
    {
        $animals = Animal::where('status', 'Active')->orderBy('tag_number')->get();
        $sheds   = $this->getSheds();
        return view('admin.milk.edit', compact('milkEntry', 'animals', 'sheds'));
    }

    public function update(Request $request, MilkEntry $milkEntry)
    {
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
        $milkEntry->delete();
        return redirect()->route('admin.milk.index')
            ->with('success', 'Milk entry deleted.');
    }
}
