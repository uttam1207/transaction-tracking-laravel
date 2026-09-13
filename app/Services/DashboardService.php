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
use App\Models\SalesOrder;
use App\Models\PurchaseOrder;
use App\Models\ChartOfAccount;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getAdminStats(): array
    {
        $today = today();
        $thisMonth = now();

        // Cash & Bank balances from journal entries (asset: balance = DR − CR)
        $cashId = ChartOfAccount::where('code', '1000')->value('id');
        $bankId = ChartOfAccount::where('code', '1010')->value('id');
        $cashBalance = $cashId ? $this->ledgerBalance($cashId) : 0.0;
        $bankBalance = $bankId ? $this->ledgerBalance($bankId) : (float) Wallet::company()->balance;

        return [
            'total_transactions' => Transaction::count(),
            'today_transactions' => Transaction::whereDate('created_at', $today)->count(),
            'wallet_balance'     => (float) Wallet::company()->balance,
            'bank_balance'       => $bankBalance,
            'cash_balance'       => $cashBalance,
            'cash_bank_total'    => $cashBalance + $bankBalance,
            'today_transactions_amount' => Transaction::where('status', 'success')->whereDate('created_at', $today)->sum('net_amount'),

            // ── Money Flow: Credit In (Sales) vs Debit Out (Purchases) ──
            'sales_credit_in'          => (float) SalesOrder::where('payment_status', 'Paid')->sum('total_amount'),
            'sales_credit_in_month'    => (float) SalesOrder::where('payment_status', 'Paid')
                                            ->whereMonth('sale_date', $thisMonth->month)
                                            ->whereYear('sale_date',  $thisMonth->year)
                                            ->sum('total_amount'),
            'purchase_debit_out'       => (float) PurchaseOrder::where('status', 'Paid')->sum('total_amount'),
            'purchase_debit_out_month' => (float) PurchaseOrder::where('status', 'Paid')
                                            ->whereMonth('order_date', $thisMonth->month)
                                            ->whereYear('order_date',  $thisMonth->year)
                                            ->sum('total_amount'),
            'pending_receivable'       => (float) SalesOrder::whereIn('payment_status', ['Pending', 'Partial'])->sum('total_amount'),
            'pending_payable'          => (float) PurchaseOrder::where('status', 'Received')->sum('total_amount'),
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
            'dates'  => $data->pluck('date')->toArray(),
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
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $yearExpr  = $isSqlite ? "CAST(strftime('%Y', created_at) AS INTEGER)" : 'YEAR(created_at)';
        $monthExpr = $isSqlite ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';

        $data = Transaction::select(
            DB::raw("$yearExpr as year"),
            DB::raw("$monthExpr as month"),
            DB::raw("SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as revenue")
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

    /**
     * Net balance for an asset account from posted journal entry lines.
     * Asset normal balance = Debit − Credit.
     */
    private function ledgerBalance(int $accountId): float
    {
        $result = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.status', 'posted')
            ->where('journal_entry_lines.account_id', $accountId)
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance')
            ->value('balance');

        return (float) ($result ?? 0);
    }
}
