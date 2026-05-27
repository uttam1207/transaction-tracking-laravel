<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService)
    {
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

        return view('admin.dashboard', compact(
            'stats', 'transactionChart', 'attendanceChart',
            'recentTransactions', 'fraudByType', 'topEmployees', 'monthlyRevenue'
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
