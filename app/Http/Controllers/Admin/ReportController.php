<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Task;
use App\Models\WorkReport;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function transactionReport(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now()->endOfMonth();

        $transactions = Transaction::with('user')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $summary = [
            'total_count' => Transaction::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_amount' => Transaction::whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount'),
            'success_amount' => Transaction::where('status', 'success')->whereBetween('created_at', [$dateFrom, $dateTo])->sum('amount'),
            'failed_count' => Transaction::where('status', 'failed')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'flagged_count' => Transaction::where('is_flagged', true)->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
        ];

        $byCategory = Transaction::select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('category')
            ->get();

        return view('admin.reports.transactions', compact('transactions', 'summary', 'byCategory', 'dateFrom', 'dateTo'));
    }

    public function employeeReport(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $employees = Employee::with('user', 'department')
            ->active()
            ->get()
            ->map(function ($employee) use ($month, $year) {
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->whereMonth('date', $month)->whereYear('date', $year)->get();

                $tasks = Task::where('assigned_to', $employee->id)
                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();

                return [
                    'employee' => $employee,
                    'attendance' => [
                        'present' => $attendance->whereIn('status', ['present', 'late'])->count(),
                        'absent' => max(0, now()->day - $attendance->count()),
                        'total_hours' => round($attendance->sum('work_hours'), 2),
                    ],
                    'tasks' => [
                        'total' => $tasks->count(),
                        'completed' => $tasks->where('status', 'completed')->count(),
                        'pending' => $tasks->whereIn('status', ['pending', 'in_progress'])->count(),
                    ],
                    'performance_score' => $employee->performance_score,
                ];
            });

        return view('admin.reports.employees', compact('employees', 'month', 'year'));
    }

    public function attendanceReport(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $data = Attendance::with('employee.user', 'employee.department')
            ->whereMonth('date', $month)->whereYear('date', $year)
            ->get()
            ->groupBy('employee_id');

        return view('admin.reports.attendance', compact('data', 'month', 'year'));
    }

    public function exportPdf(Request $request, string $type)
    {
        $data = match($type) {
            'transactions' => Transaction::with('user')->latest()->limit(500)->get(),
            'attendance' => Attendance::with('employee.user')->whereMonth('date', now()->month)->get(),
            default => collect(),
        };

        $pdf = Pdf::loadView("admin.reports.pdf.{$type}", compact('data'));
        return $pdf->download("{$type}_report_" . now()->format('Y-m-d') . ".pdf");
    }

    public function auditLogs(Request $request)
    {
        $query = \App\Models\AuditLog::with('user');

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->event) {
            $query->where('event', $request->event);
        }

        if ($request->module) {
            $query->where('module', $request->module);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();
        return view('admin.reports.audit-logs', compact('logs'));
    }
}
