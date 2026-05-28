@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Stats Cards Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Total Revenue</span>
                    <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <h4 class="mb-0 fw-bold">${{ number_format($stats['total_revenue'], 0) }}</h4>
                <small class="text-success"><i class="bi bi-arrow-up"></i> Today: ${{ number_format($stats['today_revenue'], 0) }}</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Transactions</span>
                    <div class="stat-icon" style="background: rgba(79,70,229,0.1); color: #4f46e5;">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                </div>
                <h4 class="mb-0 fw-bold">{{ number_format($stats['total_transactions']) }}</h4>
                <small class="text-primary">Today: {{ $stats['today_transactions'] }}</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Fraud Alerts</span>
                    <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                </div>
                <h4 class="mb-0 fw-bold text-danger">{{ $stats['fraud_alerts_open'] }}</h4>
                <small class="text-danger">{{ $stats['fraud_alerts_critical'] }} critical</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small">Active Users</span>
                    <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <h4 class="mb-0 fw-bold">{{ $stats['active_users'] }}</h4>
                <small class="text-muted">{{ $stats['total_employees'] }} employees</small>
            </div>
        </div>
    </div>
</div>

<!-- Second Row Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">Present Today</div>
                        <h5 class="mb-0 fw-bold text-success">{{ $stats['present_today'] }}</h5>
                    </div>
                    <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981; width:40px; height:40px; font-size:1.1rem;">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
                <div class="mt-2 small text-muted">On Leave: {{ $stats['on_leave_today'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">Pending Tasks</div>
                        <h5 class="mb-0 fw-bold text-warning">{{ $stats['pending_tasks'] }}</h5>
                    </div>
                    <div class="stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b; width:40px; height:40px; font-size:1.1rem;">
                        <i class="bi bi-kanban"></i>
                    </div>
                </div>
                <div class="mt-2 small text-danger">{{ $stats['overdue_tasks'] }} overdue</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">Failed Tx Today</div>
                        <h5 class="mb-0 fw-bold text-danger">{{ $stats['failed_transactions'] }}</h5>
                    </div>
                    <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444; width:40px; height:40px; font-size:1.1rem;">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="text-muted small mb-1">Departments</div>
                        <h5 class="mb-0 fw-bold">{{ \App\Models\Department::active()->count() }}</h5>
                    </div>
                    <div class="stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1; width:40px; height:40px; font-size:1.1rem;">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">Transaction Volume (30 days)</h6>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="updateChart('transactions', 7)">7D</button>
                    <button class="btn btn-outline-secondary active" onclick="updateChart('transactions', 30)">30D</button>
                    <button class="btn btn-outline-secondary" onclick="updateChart('transactions', 90)">90D</button>
                </div>
            </div>
            <div class="card-body">
                <div id="transactionChart" style="height: 280px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold">Fraud by Alert Type</h6>
            </div>
            <div class="card-body">
                <div id="fraudChart" style="height: 280px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold">Attendance Overview (30 days)</h6>
            </div>
            <div class="card-body">
                <div id="attendanceChart" style="height: 230px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header py-3">
                <h6 class="mb-0 fw-semibold">Monthly Revenue Trend</h6>
            </div>
            <div class="card-body">
                <div id="revenueChart" style="height: 230px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ── Drag-Drop Widget Toolbar ─────────────────────────────────────────── -->
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="small text-muted">Widgets:</span>
    <button class="btn btn-xs btn-outline-secondary py-0 px-2" id="toggleWidgets" title="Toggle drag mode">
        <i class="bi bi-grid-3x3-gap"></i> Arrange
    </button>
    <button class="btn btn-xs btn-outline-danger py-0 px-2 d-none" id="resetLayout">
        <i class="bi bi-arrow-counterclockwise"></i> Reset Layout
    </button>
    <span class="badge bg-secondary small d-none" id="dragHint">Drag cards to rearrange</span>
</div>

<div id="widgetGrid" class="row g-3 mb-3">
    <div class="col-12" data-widget="quick-stats">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-lightning me-1 text-warning"></i>Quick Stats</span>
                <button class="btn btn-xs btn-outline-secondary widget-hide py-0 px-1"><i class="bi bi-dash"></i></button>
            </div>
            <div class="card-body widget-body">
                <div class="row g-2 text-center">
                    <div class="col-3"><div class="fw-bold text-primary">{{ $stats['total_transactions'] }}</div><div class="small text-muted">Transactions</div></div>
                    <div class="col-3"><div class="fw-bold text-success">${{ number_format($stats['total_revenue'],0) }}</div><div class="small text-muted">Revenue</div></div>
                    <div class="col-3"><div class="fw-bold text-danger">{{ $stats['fraud_alerts'] }}</div><div class="small text-muted">Fraud Alerts</div></div>
                    <div class="col-3"><div class="fw-bold text-info">{{ $stats['active_employees'] }}</div><div class="small text-muted">Active Employees</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions & Top Employees -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">Recent Transactions</h6>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 small">Transaction ID</th>
                                <th class="small">Amount</th>
                                <th class="small">Status</th>
                                <th class="small">Risk</th>
                                <th class="small">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $tx)
                            <tr>
                                <td class="px-3">
                                    <a href="{{ route('admin.transactions.show', $tx) }}" class="text-decoration-none small fw-semibold">
                                        {{ $tx->transaction_id }}
                                    </a>
                                    @if($tx->is_flagged) <i class="bi bi-flag-fill text-danger small ms-1"></i> @endif
                                </td>
                                <td><span class="fw-semibold">{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $tx->status_badge }}-subtle text-{{ $tx->status_badge }} border border-{{ $tx->status_badge }}-subtle">
                                        {{ ucfirst($tx->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $tx->risk_score >= 70 ? 'danger' : ($tx->risk_score >= 40 ? 'warning' : 'success') }}-subtle
                                                        text-{{ $tx->risk_score >= 70 ? 'danger' : ($tx->risk_score >= 40 ? 'warning' : 'success') }}">
                                        {{ $tx->risk_score }}%
                                    </span>
                                </td>
                                <td><span class="small text-muted">{{ $tx->created_at->diffForHumans() }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No transactions yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-semibold">Top Performers</h6>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @forelse($topEmployees as $emp)
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ $emp->user->avatar_url }}" class="rounded-circle" width="36" height="36" alt="">
                    <div class="flex-grow-1">
                        <div class="small fw-semibold">{{ $emp->user->name }}</div>
                        <div class="small text-muted">{{ $emp->designation }}</div>
                    </div>
                    <div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            {{ $emp->performance_score }}%
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-muted small text-center py-3">No employee data</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.sortable-ghost  { opacity: .4; }
.sortable-chosen { box-shadow: 0 0 0 3px rgba(79,70,229,.4); }
#widgetGrid [data-widget] { cursor: default; }
#widgetGrid.drag-mode [data-widget] { cursor: grab; }
#widgetGrid.drag-mode .card { border: 2px dashed rgba(79,70,229,.4); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
// ── Drag-drop widgets ────────────────────────────────────────────────────────
const widgetGrid = document.getElementById('widgetGrid');
let sortable = null;

function getStoredOrder() {
    try { return JSON.parse(localStorage.getItem('dashboard_widget_order') || 'null'); } catch { return null; }
}

function applyStoredOrder() {
    const order = getStoredOrder();
    if (!order) return;
    order.forEach(id => {
        const el = widgetGrid.querySelector(`[data-widget="${id}"]`);
        if (el) widgetGrid.appendChild(el);
    });
}

function saveOrder() {
    const order = [...widgetGrid.querySelectorAll('[data-widget]')].map(el => el.dataset.widget);
    localStorage.setItem('dashboard_widget_order', JSON.stringify(order));
}

applyStoredOrder();

document.getElementById('toggleWidgets').addEventListener('click', function () {
    const active = widgetGrid.classList.toggle('drag-mode');
    document.getElementById('dragHint').classList.toggle('d-none', !active);
    document.getElementById('resetLayout').classList.toggle('d-none', !active);
    this.innerHTML = active ? '<i class="bi bi-check2"></i> Done' : '<i class="bi bi-grid-3x3-gap"></i> Arrange';

    if (active && !sortable) {
        sortable = Sortable.create(widgetGrid, {
            animation: 150, ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
            onEnd: saveOrder,
        });
    } else if (!active && sortable) {
        sortable.destroy(); sortable = null;
    }
});

document.getElementById('resetLayout').addEventListener('click', function () {
    localStorage.removeItem('dashboard_widget_order');
    location.reload();
});

// Widget collapse/expand
document.querySelectorAll('.widget-hide').forEach(btn => {
    btn.addEventListener('click', function () {
        const body = this.closest('.card').querySelector('.widget-body');
        const icon = this.querySelector('i');
        if (body) {
            body.style.display = body.style.display === 'none' ? '' : 'none';
            icon.className = body.style.display === 'none' ? 'bi bi-plus' : 'bi bi-dash';
        }
    });
});
</script>
<script>
// Transaction Chart
const txData = @json($transactionChart);
const txChart = new ApexCharts(document.getElementById('transactionChart'), {
    series: [{ name: 'Volume ($)', data: txData.amounts }],
    chart: { type: 'area', height: 280, toolbar: { show: false }, sparkline: { enabled: false } },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
    colors: ['#4f46e5'],
    xaxis: { categories: txData.labels, labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(1)+'K' : v) } },
    tooltip: { y: { formatter: v => '$' + Number(v).toLocaleString() } },
    grid: { borderColor: 'rgba(0,0,0,0.05)' },
});
txChart.render();

// Fraud Chart
const fraudData = @json($fraudByType);
new ApexCharts(document.getElementById('fraudChart'), {
    series: fraudData.counts.length ? fraudData.counts : [1],
    labels: fraudData.labels.length ? fraudData.labels : ['No Alerts'],
    chart: { type: 'donut', height: 280 },
    colors: ['#ef4444', '#f59e0b', '#6366f1', '#10b981', '#3b82f6'],
    legend: { position: 'bottom', fontSize: '11px' },
    plotOptions: { pie: { donut: { size: '65%' } } },
}).render();

// Attendance Chart
const attData = @json($attendanceChart);
new ApexCharts(document.getElementById('attendanceChart'), {
    series: [
        { name: 'Present', data: attData.present },
        { name: 'Absent', data: attData.absent },
    ],
    chart: { type: 'bar', height: 230, toolbar: { show: false }, stacked: true },
    colors: ['#10b981', '#ef4444'],
    xaxis: { categories: attData.labels, labels: { style: { fontSize: '10px' } } },
    plotOptions: { bar: { borderRadius: 4 } },
    legend: { position: 'top' },
}).render();

// Revenue Chart
const revData = @json($monthlyRevenue);
new ApexCharts(document.getElementById('revenueChart'), {
    series: [{ name: 'Revenue', data: revData.revenue }],
    chart: { type: 'bar', height: 230, toolbar: { show: false } },
    colors: ['#4f46e5'],
    xaxis: { categories: revData.labels, labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) } },
    plotOptions: { bar: { borderRadius: 6 } },
}).render();

function updateChart(type, days) {
    $.get('/admin/dashboard/chart', { type, days }, function(data) {
        txChart.updateSeries([{ name: 'Volume ($)', data: data.amounts }]);
        txChart.updateOptions({ xaxis: { categories: data.labels } });
    });
}
</script>
@endpush
