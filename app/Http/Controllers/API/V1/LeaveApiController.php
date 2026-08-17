<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveApiController extends Controller
{
    /**
     * GET /api/v1/leaves
     * Employee: own leaves; Admin/Manager: all or filtered
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Leave::with('employee.user');

        if ($user->role === 'employee') {
            $employee = $user->employee;
            if (!$employee) return response()->json(['success' => false, 'message' => 'Employee not found.'], 404);
            $query->where('employee_id', $employee->id);
        } else {
            if ($request->employee_id) $query->where('employee_id', $request->employee_id);
            if ($request->department_id) {
                $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
            }
        }

        if ($request->status) $query->where('status', $request->status);
        if ($request->type) $query->where('type', $request->type);
        if ($request->year) $query->whereYear('from_date', $request->year);

        $leaves = $query->latest('from_date')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $leaves->map(fn($l) => $this->formatLeave($l)),
            'meta'    => [
                'current_page' => $leaves->currentPage(),
                'last_page'    => $leaves->lastPage(),
                'total'        => $leaves->total(),
            ],
        ]);
    }

    /**
     * POST /api/v1/leaves
     * Employee submits a leave request
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'      => 'required|in:annual,sick,casual,maternity,paternity,unpaid,other',
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'reason'    => 'nullable|string',
        ]);

        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
        }

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'type'        => $request->type,
            'from_date'   => $request->from_date,
            'to_date'     => $request->to_date,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted.',
            'data'    => $this->formatLeave($leave),
        ], 201);
    }

    /**
     * GET /api/v1/leaves/{leave}
     */
    public function show(Request $request, Leave $leave)
    {
        $user = $request->user();

        if ($user->role === 'employee' && $user->employee?->id !== $leave->employee_id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $leave->load('employee.user', 'approver');

        return response()->json(['success' => true, 'data' => $this->formatLeave($leave, detailed: true)]);
    }

    /**
     * DELETE /api/v1/leaves/{leave}
     * Employee can cancel their own pending leave
     */
    public function destroy(Request $request, Leave $leave)
    {
        $employee = $request->user()->employee;

        if (!$employee || $employee->id !== $leave->employee_id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        if ($leave->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Only pending leaves can be cancelled.'], 422);
        }

        $leave->delete();
        return response()->json(['success' => true, 'message' => 'Leave request cancelled.']);
    }

    private function formatLeave(Leave $l, bool $detailed = false): array
    {
        $data = [
            'id'          => $l->id,
            'employee'    => $l->employee?->user?->name ?? null,
            'type'        => $l->type,
            'from_date'   => $l->from_date?->format('Y-m-d'),
            'to_date'     => $l->to_date?->format('Y-m-d'),
            'status'      => $l->status,
            'created_at'  => $l->created_at?->format('Y-m-d H:i:s'),
        ];

        if ($detailed) {
            $data['reason']           = $l->reason ?? null;
            $data['rejection_reason'] = $l->rejection_reason ?? null;
            $data['approved_by']      = $l->approver?->name ?? null;
            $data['actioned_at']      = $l->actioned_at?->format('Y-m-d H:i:s') ?? null;
        }

        return $data;
    }
}
