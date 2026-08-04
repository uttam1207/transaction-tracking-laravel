<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\MilkEntry;
use Illuminate\Http\Request;

class MilkController extends Controller
{
    public function index(Request $request)
    {
        $query = MilkEntry::with('animal');

        if ($request->date) {
            $query->whereDate('date', $request->date);
        } else {
            $query->whereDate('date', today());
        }

        if ($request->shift) {
            $query->where('shift', $request->shift);
        }

        $entries = $query->latest()->paginate(20)->withQueryString();

        $todayTotal = MilkEntry::whereDate('date', today())->sum('quantity_liters');
        $morningTotal = MilkEntry::whereDate('date', today())->where('shift', 'Morning')->sum('quantity_liters');
        $eveningTotal = MilkEntry::whereDate('date', today())->where('shift', 'Evening')->sum('quantity_liters');
        $avgFat = MilkEntry::whereDate('date', today())->avg('fat_percentage') ?? 7.5;
        $rejectedTotal = MilkEntry::whereDate('date', today())->sum('rejected_liters');

        $animals = Animal::where('status', 'Active')->where('pregnancy_status', '!=', 'Dry')->orderBy('tag_number')->get();

        return view('admin.milk.index', compact(
            'entries', 'todayTotal', 'morningTotal', 'eveningTotal', 'avgFat', 'rejectedTotal', 'animals'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|in:Morning,Evening',
            'animal_id' => 'nullable|exists:animals,id',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat_percentage' => 'required|numeric|min:1|max:15',
            'snf_percentage' => 'required|numeric|min:1|max:15',
            'quality_rating' => 'required|string',
            'rejected_liters' => 'nullable|numeric|min:0',
        ]);

        $validated['recorded_by'] = auth()->id();

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
        return view('admin.milk.edit', compact('milkEntry', 'animals'));
    }

    public function update(Request $request, MilkEntry $milkEntry)
    {
        $validated = $request->validate([
            'date'            => 'required|date',
            'shift'           => 'required|in:Morning,Evening',
            'animal_id'       => 'nullable|exists:animals,id',
            'quantity_liters' => 'required|numeric|min:0.1',
            'fat_percentage'  => 'required|numeric|min:1|max:15',
            'snf_percentage'  => 'required|numeric|min:1|max:15',
            'quality_rating'  => 'required|string',
            'rejected_liters' => 'nullable|numeric|min:0',
        ]);

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