<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveType::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $leaveTypes = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.leave-types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'code'                  => 'required|string|max:20|unique:leave_types,code',
            'color'                 => 'nullable|string|max:20',
            'max_days_per_year'     => 'required|integer|min:0|max:365',
            'carry_forward_days'    => 'nullable|integer|min:0|max:365',
            'is_paid'               => 'boolean',
            'requires_approval'     => 'boolean',
            'applicable_after_days' => 'nullable|integer|min:0',
            'is_active'             => 'boolean',
            'description'           => 'nullable|string',
        ]);

        $data['is_paid']            = $request->boolean('is_paid', true);
        $data['requires_approval']  = $request->boolean('requires_approval', true);
        $data['is_active']          = $request->boolean('is_active', true);
        $data['color']              = $data['color'] ?? '#4f46e5';
        $data['carry_forward_days'] = $data['carry_forward_days'] ?? 0;

        LeaveType::create($data);

        return back()->with('success', 'Leave type "' . $data['name'] . '" created.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'code'                  => 'required|string|max:20|unique:leave_types,code,' . $leaveType->id,
            'color'                 => 'nullable|string|max:20',
            'max_days_per_year'     => 'required|integer|min:0|max:365',
            'carry_forward_days'    => 'nullable|integer|min:0|max:365',
            'is_paid'               => 'boolean',
            'requires_approval'     => 'boolean',
            'applicable_after_days' => 'nullable|integer|min:0',
            'is_active'             => 'boolean',
            'description'           => 'nullable|string',
        ]);

        $data['is_paid']           = $request->boolean('is_paid', true);
        $data['requires_approval'] = $request->boolean('requires_approval', true);
        $data['is_active']         = $request->boolean('is_active', true);
        $leaveType->update($data);

        return back()->with('success', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType)
    {
        if ($leaveType->leaves()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete — leave records are linked to this type.',
            ], 422);
        }

        $leaveType->delete();
        return response()->json(['success' => true]);
    }
}
