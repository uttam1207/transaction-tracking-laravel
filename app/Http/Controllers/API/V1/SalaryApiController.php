<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryApiController extends Controller
{
    /**
     * GET /api/v1/salaries
     * Employee: own payslips; Admin/HR: all
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Salary::with('employee.user');

        if ($user->role === 'employee') {
            $employee = $user->employee;
            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'Employee record not found.'], 404);
            }
            $query->where('employee_id', $employee->id);
        } else {
            if ($request->employee_id) $query->where('employee_id', $request->employee_id);
            if ($request->department_id) {
                $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
            }
        }

        if ($request->month) $query->where('month', $request->month);
        if ($request->year) $query->where('year', $request->year);
        if ($request->status) $query->where('status', $request->status);

        $salaries = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $salaries->map(fn($s) => $this->formatSalary($s)),
            'meta'    => [
                'current_page' => $salaries->currentPage(),
                'last_page'    => $salaries->lastPage(),
                'total'        => $salaries->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/salaries/{salary}
     * Employee can view their own payslip detail
     */
    public function show(Request $request, Salary $salary)
    {
        $user = $request->user();

        if ($user->role === 'employee' && $user->employee?->id !== $salary->employee_id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $salary->load('employee.user', 'employee.department', 'employee.designation');

        return response()->json([
            'success' => true,
            'data'    => $this->formatSalary($salary, detailed: true),
        ]);
    }

    /**
     * GET /api/v1/salaries/summary
     * Total payroll summary (admin only)
     */
    public function summary(Request $request)
    {
        $query = Salary::query();

        if ($request->year) $query->where('year', $request->year);
        if ($request->month) $query->where('month', $request->month);

        $summary = [
            'total_gross'     => $query->sum('gross_salary'),
            'total_deductions'=> $query->sum('total_deductions'),
            'total_net'       => $query->sum('net_salary'),
            'count'           => $query->count(),
            'paid_count'      => (clone $query)->where('status', 'paid')->count(),
            'pending_count'   => (clone $query)->where('status', 'pending')->count(),
        ];

        return response()->json(['success' => true, 'data' => $summary]);
    }

    private function formatSalary(Salary $s, bool $detailed = false): array
    {
        $data = [
            'id'           => $s->id,
            'employee'     => $s->employee?->user?->name ?? null,
            'month'        => $s->month ?? null,
            'year'         => $s->year ?? null,
            'gross_salary' => $s->gross_salary ?? null,
            'net_salary'   => $s->net_salary ?? null,
            'status'       => $s->status ?? null,
        ];

        if ($detailed) {
            $data['basic_salary']     = $s->basic_salary ?? null;
            $data['allowances']       = $s->allowances ?? null;
            $data['total_deductions'] = $s->total_deductions ?? null;
            $data['department']       = $s->employee?->department?->name ?? null;
            $data['designation']      = $s->employee?->designation?->name ?? null;
            $data['paid_at']          = $s->paid_at?->format('Y-m-d') ?? null;
        }

        return $data;
    }
}
