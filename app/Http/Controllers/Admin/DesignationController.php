<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $query = Designation::withCount('employees');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->level) {
            $query->where('level', $request->level);
        }

        $designations = $query->orderBy('level')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.designations.index', compact('designations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:designations,name',
            'code'        => 'required|string|max:50|unique:designations,code',
            'level'       => 'required|integer|between:1,4',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        Designation::create($data);

        return back()->with('success', 'Designation "' . $data['name'] . '" created.');
    }

    public function update(Request $request, Designation $designation)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:designations,name,' . $designation->id,
            'code'        => 'required|string|max:50|unique:designations,code,' . $designation->id,
            'level'       => 'required|integer|between:1,4',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $designation->update($data);

        return back()->with('success', 'Designation updated.');
    }

    public function destroy(Designation $designation)
    {
        if ($designation->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete designation with assigned employees.');
        }
        $designation->delete();
        return back()->with('success', 'Designation deleted.');
    }
}
