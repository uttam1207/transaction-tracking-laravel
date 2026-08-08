<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\MilkEntry;
use App\Models\StockMovement;
use App\Services\DashboardService;
use App\Services\FeedCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
        private FeedCalculationService $feedService
    ) {
    }

    public function index()
    {
        $stats              = $this->dashboardService->getAdminStats();
        $transactionChart   = $this->dashboardService->getTransactionChartData(30);
        $attendanceChart    = $this->dashboardService->getAttendanceChartData(30);
        $recentTransactions = $this->dashboardService->getRecentTransactions(10);
        $fraudByType        = $this->dashboardService->getFraudAlertsByType();
        $topEmployees       = $this->dashboardService->getEmployeeProductivityData(5);
        $monthlyRevenue     = $this->dashboardService->getMonthlyRevenue();

        // ── ASDairy: Feed Calculation ──
        $feedData = $this->feedService->getFeedCalculationSummary();

        // ── ASDairy: Expense KPIs ──
        $asdairyExpenses = [
            'today'      => Expense::whereDate('expense_date', today())->sum('amount'),
            'this_month' => Expense::whereMonth('expense_date', now()->month)
                                   ->whereYear('expense_date', now()->year)->sum('amount'),
            'pending'    => Expense::where('payment_status', 'pending')->sum('amount'),
            'recent'     => Expense::with('category')->latest('expense_date')->take(5)->get(),
        ];

        // ── ASDairy: Animal KPIs (from Animal records) ──
        $animalStats = [
            'total'    => Animal::where('status', 'Active')->count(),
            'milking'  => Animal::where('status', 'Active')->where('pregnancy_status', '!=', 'Dry')->where('lactation_number', '>', 0)->count(),
            'pregnant' => Animal::where('status', 'Active')->where('pregnancy_status', 'Pregnant')->count(),
            'dry'      => Animal::where('status', 'Active')->where('pregnancy_status', 'Dry')->count(),
            'calves'   => Animal::where('status', 'Active')->where('animal_type', 'Calf')->count(),
            'healthy'  => Animal::where('status', 'Active')->where('health_status', 'Healthy')->count(),
            'sick'     => Animal::where('status', 'Active')->whereIn('health_status', ['Sick', 'Under Treatment'])->count(),
        ];

        // ── ASDairy: Milk KPIs ──
        $milkStats = [
            'today_liters'    => MilkEntry::whereDate('date', today())->sum('quantity_liters'),
            'today_rejected'  => MilkEntry::whereDate('date', today())->sum('rejected_liters'),
            'month_liters'    => MilkEntry::whereMonth('date', now()->month)
                                          ->whereYear('date', now()->year)->sum('quantity_liters'),
            'avg_fat'         => MilkEntry::whereDate('date', today())->avg('fat_percentage'),
            'avg_snf'         => MilkEntry::whereDate('date', today())->avg('snf_percentage'),
        ];

        // ── ASDairy: Milk trend (last 14 days) ──
        $milkTrend = MilkEntry::select(
                DB::raw('DATE(date) as day'),
                DB::raw('SUM(quantity_liters) as liters'),
                DB::raw('AVG(fat_percentage) as fat_pct')
            )
            ->where('date', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('day')
            ->get();

        // ── ASDairy: Monthly Revenue vs Expenses (last 6 months) ──
        $monthlyExpenses = Expense::select(
                DB::raw('YEAR(expense_date) as yr'),
                DB::raw('MONTH(expense_date) as mo'),
                DB::raw('SUM(amount) as total')
            )
            ->where('expense_date', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('yr', 'mo')
            ->orderBy('yr')->orderBy('mo')
            ->get();

        // ── ASDairy: Stock alerts ──
        $stockAlerts = [
            'low_stock'     => InventoryItem::all()->filter(fn ($i) => $i->stock_status === 'Low Stock')->count(),
            'out_of_stock'  => InventoryItem::all()->filter(fn ($i) => $i->stock_status === 'Out of Stock')->count(),
            'expiring_soon' => InventoryItem::whereNotNull('expiry_date')
                                ->where('expiry_date', '<=', now()->addDays(30))->count(),
        ];

        return view('admin.dashboard', compact(
            'stats', 'transactionChart', 'attendanceChart',
            'recentTransactions', 'fraudByType', 'topEmployees', 'monthlyRevenue',
            'feedData', 'asdairyExpenses',
            'animalStats', 'milkStats', 'milkTrend', 'monthlyExpenses', 'stockAlerts'
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
            'attendance'   => $this->dashboardService->getAttendanceChartData($days),
            'revenue'      => $this->dashboardService->getMonthlyRevenue(),
            default        => [],
        };

        return response()->json($data);
    }
}
