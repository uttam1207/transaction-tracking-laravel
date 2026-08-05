<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActionType;
use Illuminate\Http\Request;

class ActionTypeController extends Controller
{
    public function index()
    {
        $actionTypes = ActionType::orderByDesc('is_system')->orderBy('name')->get();
        return view('admin.action-types.index', compact('actionTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:action_types,name',
        ]);

        ActionType::create([
            'name'      => trim($validated['name']),
            'is_system' => false,
            'is_active' => true,
        ]);

        return back()->with('success', 'Action type "' . trim($validated['name']) . '" added successfully.');
    }

    public function update(Request $request, ActionType $actionType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:action_types,name,' . $actionType->id,
        ]);

        $actionType->update(['name' => trim($validated['name'])]);

        return back()->with('success', 'Action type updated to "' . trim($validated['name']) . '".');
    }

    public function destroy(ActionType $actionType)
    {
        if ($actionType->is_system) {
            return back()->with('error', 'System action types cannot be deleted.');
        }

        $actionType->delete();

        return back()->with('success', 'Action type "' . $actionType->name . '" deleted.');
    }
}