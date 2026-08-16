<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::with(['company', 'manager', 'departments'])
                       ->withCount(['departments', 'employees']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $branches  = $query->orderByDesc('is_headquarters')->orderBy('name')->paginate(15)->withQueryString();
        $companies = Company::orderBy('name')->get();
        $managers  = User::where('status', 'active')
                         ->whereIn('role', ['super_admin', 'admin', 'manager'])
                         ->orderBy('name')->get();

        return view('admin.branches.index', compact('branches', 'companies', 'managers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:50|unique:branches,code',
            'address'          => 'nullable|string|max:500',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'country'          => 'nullable|string|max:100',
            'postal_code'      => 'nullable|string|max:20',
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:255',
            'manager_id'       => 'nullable|exists:users,id',
            'is_headquarters'  => 'boolean',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string',
        ]);

        // Only one HQ allowed per company
        if (!empty($data['is_headquarters'])) {
            Branch::where('company_id', $data['company_id'])->update(['is_headquarters' => false]);
        }

        Branch::create($data);

        return back()->with('success', 'Branch "' . $data['name'] . '" created successfully.');
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'company_id'       => 'required|exists:companies,id',
            'name'             => 'required|string|max:255',
            'code'             => 'required|string|max:50|unique:branches,code,' . $branch->id,
            'address'          => 'nullable|string|max:500',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'country'          => 'nullable|string|max:100',
            'postal_code'      => 'nullable|string|max:20',
            'phone'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:255',
            'manager_id'       => 'nullable|exists:users,id',
            'is_headquarters'  => 'boolean',
            'is_active'        => 'boolean',
            'description'      => 'nullable|string',
        ]);

        if (!empty($data['is_headquarters'])) {
            Branch::where('company_id', $data['company_id'])
                  ->where('id', '!=', $branch->id)
                  ->update(['is_headquarters' => false]);
        }

        $branch->update($data);

        return back()->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        if ($branch->employees()->count() > 0 || $branch->departments()->count() > 0) {
            return back()->with('error', 'Cannot delete branch with active employees or departments. Reassign them first.');
        }

        $branch->delete();
        return back()->with('success', 'Branch deleted.');
    }
}
