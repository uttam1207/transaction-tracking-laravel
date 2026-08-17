<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeApiController extends Controller
{
    /**
     * GET /api/v1/employees
     * List employees (admin/hr: all; employee: self only)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'employee') {
            $emp = $user->employee;
            if (!$emp) return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            return response()->json(['success' => true, 'data' => [$this->formatEmployee($emp)]]);
        }

        $query = Employee::with('user', 'department', 'designation');

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"));
        }

        $employees = $query->latest()->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $employees->map(fn($e) => $this->formatEmployee($e)),
            'meta'    => [
                'current_page' => $employees->currentPage(),
                'last_page'    => $employees->lastPage(),
                'total'        => $employees->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/employees/{employee}
     */
    public function show(Request $request, Employee $employee)
    {
        $user = $request->user();

        // Employee can only view their own profile
        if ($user->role === 'employee' && $user->employee?->id !== $employee->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $employee->load('user', 'department', 'designation', 'leaves', 'attendances');

        return response()->json([
            'success' => true,
            'data'    => $this->formatEmployee($employee, detailed: true),
        ]);
    }

    /**
     * GET /api/v1/employees/me
     * Current user's employee profile
     */
    public function me(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'No employee record linked.'], 404);
        }

        $employee->load('user', 'department', 'designation');

        return response()->json([
            'success' => true,
            'data'    => $this->formatEmployee($employee, detailed: true),
        ]);
    }

    private function formatEmployee(Employee $e, bool $detailed = false): array
    {
        $base = [
            'id'             => $e->id,
            'employee_id'    => $e->employee_id ?? null,
            'name'           => $e->user?->name,
            'email'          => $e->user?->email,
            'phone'          => $e->phone ?? $e->user?->phone ?? null,
            'department'     => $e->department?->name ?? null,
            'designation'    => $e->designation?->name ?? null,
            'join_date'      => $e->join_date?->format('Y-m-d') ?? null,
            'status'         => $e->status ?? null,
            'gender'         => $e->gender ?? null,
        ];

        if ($detailed) {
            $base['address']          = $e->address ?? null;
            $base['emergency_contact']= $e->emergency_contact ?? null;
            $base['bank_account']     = $e->bank_account ?? null;
            $base['leave_balance']    = $e->leaveBalance ?? null;
        }

        return $base;
    }
}
