@extends('layouts.app')
@section('title', 'Profitability & Investor Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.center') }}">Reports</a></li>
    <li class="breadcrumb-item active">Profitability Report</li>
@endsection

@section('content')

<div class="page-hero" style="background:linear-gradient(135deg,#1e40af,#2563eb,#3b82f6);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <div style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Module 17 — Reports</div>
            <h4 style="color:#fff;">Profitability & Investor Report</h4>
            <p style="color:rgba(255,255,255,.75);">Revenue vs expenses, P&L summary, payroll costs, profitability trend</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.reports.center') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-grid-3x3-gap me-1"></i>All Reports
            </a>
        </div>
    </div>
</div>

{{-- Period Filter --}}
<div class="card-glass p-3 mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">From Date</label>
            <input type="date" name="date_from" class="form-control form-control-sm"
                value="{{ request('date_from', now()->startOfYear()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">To Date</label>
            <input type="date" name="date_to" class="form-control form-control-sm"
                value="{{ request('date_to', now()->toDateString()) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.78rem;">Period Grouping</label>
            <select name="group_by" class="form-select form-select-sm">
                <option value="month" @selected(request('group_by','month')==='month')>Monthly</option>
                <option value="quarter" @selected(request('group_by')==='quarter')>Quarterly</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary-grad btn-sm w-100">
                <i class="bi bi-funnel me-1"></i>Apply Filter
            </button>
        </div>
    </form>
</div>

@php
    use App\Models\Expense;
    use App\Models\SalesOrder;
    use App\Models\EmployeeSalary;
    use Illuminate\Support\Facades\DB;

    $dateFrom = request('date_from', now()->startOfYear()->toDateString());
    $dateTo   = request('date_to', now()->toDateString());

    // ── Revenue (Sales Orders) ──
    $salesRevenue = SalesOrder::whereBetween('order_date', [$dateFrom, $dateTo])
        ->whereIn('status', ['Delivered', 'Paid', 'Completed'])
        ->sum('total_amount');

    // ── Expenses ──
    $totalExpenses = Expense::whereBetween('expense_date', [$dateFrom, $dateTo])->sum('amount');
    $expenseByCategory = Expense::with('category')
        ->select('expense_category_id', DB::raw('SUM(amount) as total'))
        ->whereBetween('expense_date', [$dateFrom, $dateTo])
        ->groupBy('expense_category_id')
        ->get();

    // ── Payroll costs ──
    $salaryBreakdown = EmployeeSalary::whereBetween(
        DB::raw("CONCAT(year, '-', LPAD(month_number,2,'0'), '-01')"),
        [$dateFrom, $dateTo]
    )->selectRaw('SUM(net_salary) as total_net, SUM(gross_salary) as total_gross, SUM(pf_deduction+tax_deduction+other_deductions) as total_deductions, COUNT(DISTINCT employee_id) as emp_count')->first();
    $payrollCost = $salaryBreakdown?->total_gross ?? 0;

    $totalCosts = $totalExpenses + $payrollCost;
    $netProfit  = $salesRevenue - $totalCosts;
    $profitMargin = $salesRevenue > 0 ? round(($netProfit / $salesRevenue) * 100, 1) : 0;

    // ── Monthly breakdown (last 12 months) ──
    $monthlyRevenue = SalesOrder::select(
            DB::raw('YEAR(order_date) as yr'),
            DB::raw('MONTH(order_date) as mo'),
            DB::raw('SUM(total_amount) as revenue')
        )
        ->whereBetween('order_date', [$dateFrom, $dateTo])
        ->whereIn('status', ['Delivered', 'Paid', 'Completed'])
        ->groupBy('yr', 'mo')
        ->orderBy('yr')->orderBy('mo')
        ->get();

    $monthlyExpenses = Expense::select(
            DB::raw('YEAR(expense_date) as yr'),
            DB::raw('MONTH(expense_date) as mo'),
            DB::raw('SUM(amount) as expenses')
        )
        ->whereBetween('expense_date', [$dateFrom, $dateTo])
        ->groupBy('yr', 'mo')
        ->orderBy('yr')->orderBy('mo')
        ->get();

    // Build month-by-month merged array
    $months = [];
    foreach ($monthlyRevenue as $row) {
        $key = $row->yr . '-' . str_pad($row->mo, 2, '0', STR_PAD_LEFT);
        $months[$key]['label']   = \Carbon\Carbon::createFromDate($row->yr, $row->mo, 1)->format('M Y');
        $months[$key]['revenue'] = $row->revenue;
    }
    foreach ($monthlyExpenses as $row) {
        $key = $row->yr . '-' . str_pad($row->mo, 2, '0', STR_PAD_LEFT);
        if (!isset($months[$key]['label'])) {
            $months[$key]['label']   = \Carbon\Carbon::createFromDate($row->yr, $row->mo, 1)->format('M Y');
            $months[$key]['revenue'] = 0;
        }
        $months[$key]['expenses'] = $row->expenses;
    }
    ksort($months);
    foreach ($months as &$m) { $m['profit'] = ($m['revenue'] ?? 0) - ($m['expenses'] ?? 0); }
@endphp

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#16a34a);">
            <i class="bi bi-graph-up-arrow kpi-icon"></i>
            <div class="kpi-value">₹{{ number_format($salesRevenue, 0) }}</div>
            <div class="kpi-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">
            <i class="bi bi-graph-down-arrow kpi-icon"></i>
            <div class="kpi-value">₹{{ number_format($totalCosts, 0) }}</div>
            <div class="kpi-label">Total Costs</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,{{ $netProfit >= 0 ? '#1d4ed8,#3b82f6' : '#b91c1c,#dc2626' }});">
            <i class="bi bi-cash-stack kpi-icon"></i>
            <div class="kpi-value">₹{{ number_format(abs($netProfit), 0) }}</div>
            <div class="kpi-label">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
            <i class="bi bi-percent kpi-icon"></i>
            <div class="kpi-value">{{ $profitMargin }}%</div>
            <div class="kpi-label">Profit Margin</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- P&L Summary --}}
    <div class="col-md-5">
        <div class="card-glass p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                <i class="bi bi-calculator-fill me-1 text-primary"></i>P&L Summary
            </h6>

            {{-- Revenue --}}
            <div class="mb-3">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#059669;letter-spacing:.05em;">Revenue</div>
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span style="font-size:.85rem;">Sales Orders</span>
                    <span class="fw-bold text-success">₹{{ number_format($salesRevenue, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-1 border-top">
                    <span class="fw-bold" style="font-size:.88rem;">Total Revenue</span>
                    <span class="fw-bold text-success" style="font-size:1rem;">₹{{ number_format($salesRevenue, 2) }}</span>
                </div>
            </div>

            {{-- Expenses --}}
            <div class="mb-3">
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;color:#dc2626;letter-spacing:.05em;">Costs</div>
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span style="font-size:.85rem;">Operating Expenses</span>
                    <span class="fw-bold" style="color:#dc2626;">₹{{ number_format($totalExpenses, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-1">
                    <span style="font-size:.85rem;">Payroll / Salaries</span>
                    <span class="fw-bold" style="color:#dc2626;">₹{{ number_format($payrollCost, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-1 border-top">
                    <span class="fw-bold" style="font-size:.88rem;">Total Costs</span>
                    <span class="fw-bold" style="color:#dc2626;font-size:1rem;">₹{{ number_format($totalCosts, 2) }}</span>
                </div>
            </div>

            <hr class="my-2 opacity-25">
            <div class="d-flex justify-content-between align-items-center py-2" style="background:{{ $netProfit >= 0 ? '#f0fdf4' : '#fef2f2' }};border-radius:8px;padding:10px 14px!important;">
                <span class="fw-bold" style="font-size:.95rem;">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</span>
                <span class="fw-bold" style="font-size:1.1rem;color:{{ $netProfit >= 0 ? '#059669' : '#dc2626' }};">
                    {{ $netProfit >= 0 ? '+' : '-' }}₹{{ number_format(abs($netProfit), 2) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Expense Breakdown by Category --}}
    <div class="col-md-7">
        <div class="card-glass p-4 h-100">
            <h6 class="fw-bold mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
                <i class="bi bi-pie-chart-fill me-1 text-danger"></i>Expense Breakdown by Category
            </h6>
            @forelse($expenseByCategory->sortByDesc('total') as $row)
            @php $pct = $totalExpenses > 0 ? round($row->total / $totalExpenses * 100, 1) : 0; @endphp
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span style="font-size:.82rem;font-weight:600;">
                    {{ $row->category?->name ?? 'Uncategorized' }}
                </span>
                <div class="d-flex align-items-center gap-2">
                    <div style="width:90px;height:6px;border-radius:4px;background:#f1f5f9;overflow:hidden;">
                        <div style="width:{{ $pct }}%;height:100%;background:#dc2626;border-radius:4px;"></div>
                    </div>
                    <span class="fw-bold" style="font-size:.82rem;color:#dc2626;min-width:80px;text-align:right;">₹{{ number_format($row->total, 0) }}</span>
                    <span style="font-size:.72rem;color:#9ca3af;min-width:30px;">{{ $pct }}%</span>
                </div>
            </div>
            @empty
            <p class="text-muted" style="font-size:.82rem;">No expense data for selected period.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Monthly Trend Chart --}}
@if(count($months) > 0)
<div class="card-glass p-4 mb-4">
    <h6 class="fw-bold mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">
        <i class="bi bi-bar-chart-line-fill me-1 text-primary"></i>Monthly Revenue vs Expenses Trend
    </h6>
    <div id="plChart" style="min-height:280px;"></div>
</div>
@endif

{{-- Month-by-Month Table --}}
@if(count($months) > 0)
<div class="card-glass overflow-hidden">
    <div class="px-4 py-3 border-bottom">
        <span class="fw-bold">Period-wise P&L Breakdown</span>
    </div>
    <div class="table-responsive">
        <table class="modern-table table mb-0">
            <thead>
                <tr>
                    <th>Period</th>
                    <th class="text-end">Revenue (₹)</th>
                    <th class="text-end">Expenses (₹)</th>
                    <th class="text-end">Net Profit/Loss (₹)</th>
                    <th>Margin</th>
                </tr>
            </thead>
            <tbody>
                @foreach($months as $mk => $m)
                @php
                    $rev = $m['revenue'] ?? 0;
                    $exp = $m['expenses'] ?? 0;
                    $net = $m['profit'];
                    $mg  = $rev > 0 ? round($net/$rev*100, 1) : 0;
                @endphp
                <tr>
                    <td class="fw-semibold" style="font-size:.85rem;">{{ $m['label'] }}</td>
                    <td class="text-end fw-bold text-success">{{ number_format($rev, 2) }}</td>
                    <td class="text-end fw-bold" style="color:#dc2626;">{{ number_format($exp, 2) }}</td>
                    <td class="text-end fw-bold" style="color:{{ $net >= 0 ? '#059669' : '#dc2626' }};">
                        {{ $net >= 0 ? '+' : '' }}{{ number_format($net, 2) }}
                    </td>
                    <td>
                        <span class="spill {{ $mg >= 15 ? 'spill-success' : ($mg >= 0 ? 'spill-warning' : 'spill-danger') }}" style="font-size:.72rem;">
                            {{ $mg }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700;">
                    <td>TOTAL</td>
                    <td class="text-end text-success">{{ number_format($salesRevenue, 2) }}</td>
                    <td class="text-end" style="color:#dc2626;">{{ number_format($totalExpenses, 2) }}</td>
                    <td class="text-end" style="color:{{ $netProfit >= 0 ? '#059669' : '#dc2626' }};">
                        {{ $netProfit >= 0 ? '+' : '' }}{{ number_format($netProfit, 2) }}
                    </td>
                    <td><span class="spill {{ $profitMargin >= 15 ? 'spill-success' : ($profitMargin >= 0 ? 'spill-warning' : 'spill-danger') }}" style="font-size:.72rem;">{{ $profitMargin }}%</span></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

@endsection

@if(count($months) > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
const months = @json(array_values($months));
new ApexCharts(document.getElementById('plChart'), {
    chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
    series: [
        { name: 'Revenue', data: months.map(m => +(m.revenue ?? 0).toFixed(2)) },
        { name: 'Expenses', data: months.map(m => +(m.expenses ?? 0).toFixed(2)) },
        { name: 'Net Profit', data: months.map(m => +(m.profit ?? 0).toFixed(2)), type: 'line' },
    ],
    xaxis: { categories: months.map(m => m.label), labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v.toFixed(0)) } },
    colors: ['#059669', '#dc2626', '#2563eb'],
    plotOptions: { bar: { columnWidth: '60%', borderRadius: 4 } },
    legend: { position: 'top' },
    stroke: { width: [0, 0, 2], curve: 'smooth' },
    tooltip: { y: { formatter: v => '₹' + v.toLocaleString('en-IN', { minimumFractionDigits: 2 }) } },
    grid: { borderColor: '#f1f5f9' },
}).render();
</script>
@endpush
@endif
