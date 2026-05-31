<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Task;
use App\Models\User;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\FraudAlert;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAdminStats(): array
    {
        $today = today();
        $thisMonth = now();

        return [
            'total_transactions' => Transaction::count(),
            'today_transactions' => Transaction::whereDate('created_at', $today)->count(),
            'total_revenue' => Transaction::where('status', 'success')->sum('amount'),
            'today_revenue' => Transaction::where('status', 'success')->whereDate('created_at', $today)->sum('amount'),
            'fraud_alerts' => FraudAlert::count(),
            'fraud_alerts_open' => FraudAlert::open()->count(),
            'fraud_alerts_critical' => FraudAlert::critical()->open()->count(),
            'active_users' => User::active()->count(),
            'total_employees' => Employee::active()->count(),
            'active_employees' => Employee::active()->count(),
            'present_today' => Attendance::whereDate('date', $today)->whereIn('status', ['present', 'late'])->count(),
            'on_leave_today' => Leave::where('status', 'approved')
                ->where('from_date', '<=', $today)
                ->where('to_date', '>=', $today)
                ->count(),
            'pending_tasks' => Task::whereIn('status', ['pending', 'in_progress'])->count(),
            'overdue_tasks' => Task::overdue()->count(),
            'failed_transactions' => Transaction::where('status', 'failed')->whereDate('created_at', $today)->count(),
        ];
    }

    public function getTransactionChartData(int $days = 30): array
    {
        $data = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as successful_amount')
        )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
            'counts' => $data->pluck('count')->toArray(),
            'amounts' => $data->pluck('successful_amount')->toArray(),
        ];
    }

    public function getAttendanceChartData(int $days = 30): array
    {
        $data = Attendance::select(
            DB::raw('DATE(date) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status IN ("present", "late") THEN 1 ELSE 0 END) as present'),
            DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
        )
            ->where('date', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d'))->toArray(),
            'present' => $data->pluck('present')->toArray(),
            'absent' => $data->pluck('absent')->toArray(),
        ];
    }

    public function getRecentTransactions(int $limit = 10): \Illuminate\Support\Collection
    {
        return Transaction::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getFraudAlertsByType(): array
    {
        $data = FraudAlert::select('alert_type', DB::raw('COUNT(*) as count'))
            ->groupBy('alert_type')
            ->get();

        return [
            'labels' => $data->pluck('alert_type')->toArray(),
            'counts' => $data->pluck('count')->toArray(),
        ];
    }

    public function getEmployeeProductivityData(int $limit = 10): \Illuminate\Support\Collection
    {
        return Employee::with('user', 'department')
            ->active()
            ->orderBy('performance_score', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getMonthlyRevenue(): array
    {
        $data = Transaction::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(CASE WHEN status = "success" THEN amount ELSE 0 END) as revenue')
        )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $data->map(fn($r) => Carbon::createFromDate($r->year, $r->month, 1)->format('M Y'))->toArray(),
            'revenue' => $data->pluck('revenue')->toArray(),
        ];
    }

    public function getEmployeeDashboard(int $employeeId): array
    {
        $employee = Employee::findOrFail($employeeId);
        $today = today();

        $todayAttendance = Attendance::where('employee_id', $employeeId)->whereDate('date', $today)->first();

        return [
            'today_status' => $todayAttendance?->status ?? 'not_marked',
            'is_checked_in' => $todayAttendance && $todayAttendance->check_in && !$todayAttendance->check_out,
            'check_in_time' => $todayAttendance?->check_in,
            'work_hours_today' => $todayAttendance?->work_hours ?? 0,
            'pending_tasks' => Task::where('assigned_to', $employeeId)->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_tasks_month' => Task::where('assigned_to', $employeeId)
                ->where('status', 'completed')
                ->whereMonth('completed_at', now()->month)
                ->count(),
            'leave_balance' => [
                'annual' => $employee->annual_leave_balance,
                'sick' => $employee->sick_leave_balance,
            ],
            'performance_score' => $employee->performance_score,
            'pending_reports' => \App\Models\WorkReport::where('employee_id', $employeeId)->where('status', 'draft')->count(),
        ];
    }
}
