<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\DashboardService;
use App\Services\FeedCalculationService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private FeedCalculationService $feedService
    ) {
    }

    public function index()
    {
        $stats = $this->dashboardService->getAdminStats();
        $transactionChart = $this->dashboardService->getTransactionChartData(30);
        $attendanceChart = $this->dashboardService->getAttendanceChartData(30);
        $recentTransactions = $this->dashboardService->getRecentTransactions(10);
        $fraudByType = $this->dashboardService->getFraudAlertsByType();
        $topEmployees = $this->dashboardService->getEmployeeProductivityData(5);
        $monthlyRevenue = $this->dashboardService->getMonthlyRevenue();

        // ASDairy Specific Calculations
        $feedData = $this->feedService->getFeedCalculationSummary();
        $asdairyExpenses = [
            'today' => Expense::whereDate('expense_date', today())->sum('amount'),
            'this_month' => Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount'),
            'recent' => Expense::with('category')->latest('expense_date')->take(5)->get(),
        ];

        return view('admin.dashboard', compact(
            'stats', 'transactionChart', 'attendanceChart',
            'recentTransactions', 'fraudByType', 'topEmployees', 'monthlyRevenue',
            'feedData', 'asdairyExpenses'
        ));
    }

    public function getStats(Request $request)
    {
        return response()->json($this->dashboardService->getAdminStats());
    }

    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'transactions');
        $days = $request->get('days', 30);

        $data = match($type) {
            'transactions' => $this->dashboardService->getTransactionChartData($days),
            'attendance' => $this->dashboardService->getAttendanceChartData($days),
            'revenue' => $this->dashboardService->getMonthlyRevenue(),
            default => [],
        };

        return response()->json($data);
    }
}
