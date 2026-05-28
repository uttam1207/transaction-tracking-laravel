<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->year ?? date('Y');
        $holidays = Holiday::whereYear('date', $year)->orderBy('date')->get();
        return view('admin.holidays.index', compact('holidays', 'year'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'date'      => 'required|date',
            'type'      => 'required|in:public,company,optional,restricted',
            'is_active' => 'boolean',
        ]);

        Holiday::create([
            'name'      => $request->name,
            'date'      => $request->date,
            'type'      => $request->type,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Holiday added.');
    }

    public function update(Request $request, Holiday $holiday)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'date'      => 'required|date',
            'type'      => 'required|in:public,company,optional,restricted',
            'is_active' => 'boolean',
        ]);

        $holiday->update([
            'name'      => $request->name,
            'date'      => $request->date,
            'type'      => $request->type,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Holiday updated.');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        return response()->json(['success' => true, 'message' => 'Holiday deleted.']);
    }
}
