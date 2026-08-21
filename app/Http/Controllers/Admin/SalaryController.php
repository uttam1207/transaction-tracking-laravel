<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\SalaryStructure;
use App\Services\JournalPostingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int)($request->year  ?? now()->year);
        $month = (int)($request->month ?? now()->month);

        $query = EmployeeSalary::with('employee.user', 'employee.department')
            ->where('year', $year)
            ->where('month_number', $month);

        if ($request->department) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department));
        }
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        $salaries = $query->get();

        $summary = [
            'total_payroll'   => $salaries->sum('net_salary'),
            'paid_count'      => $salaries->where('payment_status', 'Paid')->count(),
            'pending_count'   => $salaries->where('payment_status', 'Pending')->count(),
            'total_employees' => Employee::where('status', 'active')->count(),
        ];

        $departments = Department::active()->orderBy('name')->get();

        return view('admin.salaries.index', compact('salaries', 'summary', 'departments', 'year', 'month'));
    }

    public function create()
    {
        $employees = Employee::with('user', 'department')->where('status', 'active')->orderBy('employee_id')->get();
        return view('admin.salaries.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'      => 'required|exists:employees,id',
            'year'             => 'required|integer|min:2020|max:2099',
            'month_number'     => 'required|integer|min:1|max:12',
            'basic_salary'     => 'required|numeric|min:0',
            'hra'              => 'nullable|numeric|min:0',
            'other_allowances' => 'nullable|numeric|min:0',
            'pf_deduction'     => 'nullable|numeric|min:0',
            'tax_deduction'    => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'days_worked'      => 'nullable|numeric|min:0',
            'days_absent'      => 'nullable|numeric|min:0',
            'payment_status'   => 'required|in:Pending,Processing,Paid',
            'payment_date'     => 'nullable|date',
            'payment_mode'     => 'nullable|string|max:50',
            'remarks'          => 'nullable|string|max:500',
        ]);

        $hra              = (float)($validated['hra'] ?? 0);
        $otherAllowances  = (float)($validated['other_allowances'] ?? 0);
        $pfDeduction      = (float)($validated['pf_deduction'] ?? 0);
        $taxDeduction     = (float)($validated['tax_deduction'] ?? 0);
        $otherDeductions  = (float)($validated['other_deductions'] ?? 0);
        $grossSalary      = $validated['basic_salary'] + $hra + $otherAllowances;
        $netSalary        = $grossSalary - $pfDeduction - $taxDeduction - $otherDeductions;

        $existing = EmployeeSalary::where([
            'employee_id'  => $validated['employee_id'],
            'year'         => $validated['year'],
            'month_number' => $validated['month_number'],
        ])->first();

        $wasUnpaid = !$existing || $existing->payment_status !== 'Paid';

        $salary = EmployeeSalary::updateOrCreate(
            [
                'employee_id'  => $validated['employee_id'],
                'year'         => $validated['year'],
                'month_number' => $validated['month_number'],
            ],
            [
                'month_label'      => Carbon::createFromDate($validated['year'], $validated['month_number'], 1)->format('M Y'),
                'basic_salary'     => $validated['basic_salary'],
                'hra'              => $hra,
                'other_allowances' => $otherAllowances,
                'gross_salary'     => $grossSalary,
                'pf_deduction'     => $pfDeduction,
                'tax_deduction'    => $taxDeduction,
                'other_deductions' => $otherDeductions,
                'net_salary'       => $netSalary,
                'days_worked'      => $validated['days_worked'] ?? 0,
                'days_absent'      => $validated['days_absent'] ?? 0,
                'payment_status'   => $validated['payment_status'],
                'payment_date'     => $validated['payment_date'] ?? null,
                'payment_mode'     => $validated['payment_mode'] ?? null,
                'remarks'          => $validated['remarks'] ?? null,
            ]
        );

        // Auto-post to General Ledger when salary is marked as Paid for the first time
        if ($wasUnpaid && $salary->payment_status === 'Paid') {
            app(JournalPostingService::class)->postSalary($salary->load('employee.user'));
        }

        return redirect()->route('admin.salaries.index', ['year' => $validated['year'], 'month' => $validated['month_number']])
            ->with('success', 'Salary record saved successfully.');
    }

    public function show(EmployeeSalary $salary)
    {
        $salary->load('employee.user', 'employee.department');
        return view('admin.salaries.show', compact('salary'));
    }

    public function payslip(EmployeeSalary $salary)
    {
        $salary->load('employee.user', 'employee.department');
        return view('admin.salaries.payslip', compact('salary'));
    }

    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'year'  => 'required|integer|min:2020|max:2099',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $year   = (int)$request->year;
        $month  = (int)$request->month;
        $label  = Carbon::createFromDate($year, $month, 1)->format('M Y');

        $employees = Employee::where('status', 'active')->get();
        $generated = 0;

        foreach ($employees as $emp) {
            $basic = (float)($emp->salary ?? 0);
            if ($basic <= 0) continue;

            $structure = $emp->salaryStructure();

            if ($structure) {
                // Use configured salary structure components
                $breakdown       = $structure->calculateNet($basic);
                $hra             = 0;
                $otherAllowances = 0;
                $pfDeduction     = 0;
                $otherDeductions = 0;

                foreach ($breakdown['breakdown'] as $item) {
                    $compCode = strtoupper($item['code'] ?? '');
                    if ($item['type'] === 'earning') {
                        if ($compCode === 'HRA') {
                            $hra += $item['amount'];
                        } else {
                            $otherAllowances += $item['amount'];
                        }
                    } else {
                        if ($compCode === 'PF') {
                            $pfDeduction += $item['amount'];
                        } else {
                            $otherDeductions += $item['amount'];
                        }
                    }
                }

                $gross = $breakdown['gross'];
                $net   = $breakdown['net'];
            } else {
                // Fallback: hardcoded HRA 20%, PF 12%
                $hra             = round($basic * 0.20, 2);
                $gross           = $basic + $hra;
                $pfDeduction     = round($basic * 0.12, 2);
                $otherAllowances = 0;
                $otherDeductions = 0;
                $net             = $gross - $pfDeduction;
            }

            EmployeeSalary::firstOrCreate(
                ['employee_id' => $emp->id, 'year' => $year, 'month_number' => $month],
                [
                    'month_label'      => $label,
                    'basic_salary'     => $basic,
                    'hra'              => $hra,
                    'other_allowances' => $otherAllowances,
                    'gross_salary'     => $gross,
                    'pf_deduction'     => $pfDeduction,
                    'tax_deduction'    => 0,
                    'other_deductions' => $otherDeductions,
                    'net_salary'       => $net,
                    'days_worked'      => 0,
                    'days_absent'      => 0,
                    'payment_status'   => 'Pending',
                ]
            );
            $generated++;
        }

        return back()->with('success', "Bulk generated salary for {$generated} employees for {$label}.");
    }

    public function markPaid(Request $request, EmployeeSalary $salary)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string|max:50',
        ]);

        $salary->update([
            'payment_status' => 'Paid',
            'payment_date'   => $request->payment_date,
            'payment_mode'   => $request->payment_mode,
        ]);

        return back()->with('success', 'Salary marked as paid for ' . $salary->employee?->user?->name . '.');
    }

    public function destroy(EmployeeSalary $salary)
    {
        $salary->delete();
        return redirect()->route('admin.salaries.index')->with('success', 'Salary record deleted.');
    }
}
