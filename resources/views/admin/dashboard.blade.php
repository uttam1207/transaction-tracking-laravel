@extends('layouts.app')
@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
<style>
    /* ── Welcome Banner ── */
    .dash-welcome {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4f46e5 80%, #6d28d9 100%);
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        color: #fff;
    }
    .dash-welcome::before {
        content: '';
        position: absolute;
        width: 320px; height: 320px;
        background: rgba(255,255,255,.05);
        border-radius: 50%;
        top: -120px; right: -80px;
    }
    .dash-welcome::after {
        content: '';
        position: absolute;
        width: 200px; height: 200px;
        background: rgba(255,255,255,.04);
        border-radius: 50%;
        bottom: -80px; left: 30%;
    }
    .dash-welcome .live-dot {
        width: 8px; height: 8px;
        background: #4ade80;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
        animation: pulse-dot 2s infinite;
    }
    @keyframes pulse-dot {
        0%,100% { opacity:1; transform:scale(1); }
        50%      { opacity:.5; transform:scale(1.3); }
    }
    .quick-action-btn {
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        color: #fff;
        border-radius: 10px;
        padding: 8px 16px;
        font-size: .8rem;
        font-weight: 500;
        text-decoration: none;
        transition: background .2s;
        backdrop-filter: blur(4px);
    }
    .quick-action-btn:hover { background: rgba(255,255,255,.22); color: #fff; }

    /* ── KPI Cards ── */
    .kpi-card {
        border-radius: 14px;
        border: 1.5px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        padding: 20px 22px;
        transition: transform .2s, box-shadow .2s;
        position: relative;
        overflow: hidden;
    }
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 14px 14px 0 0;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,.09); }
    .kpi-card.green::before { background: linear-gradient(90deg,#10b981,#34d399); }
    .kpi-card.indigo::before { background: linear-gradient(90deg,#4f46e5,#818cf8); }
    .kpi-card.red::before   { background: linear-gradient(90deg,#ef4444,#f87171); }
    .kpi-card.amber::before { background: linear-gradient(90deg,#f59e0b,#fbbf24); }

    .kpi-icon {
        width: 46px; height: 46px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }
    .kpi-label { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .8px; color: #9ca3af; margin-bottom: 4px; }
    .kpi-value { font-size: 1.75rem; font-weight: 800; letter-spacing: -1px; line-height: 1; }
    .kpi-sub   { font-size: .75rem; margin-top: 6px; }

    /* ── Mini Stat Row ── */
    .mini-stat {
        border-radius: 12px;
        border: 1.5px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        padding: 16px 18px;
        display: flex; align-items: center; justify-content: space-between;
        transition: box-shadow .2s;
    }
    .mini-stat:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
    .mini-stat-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    /* ── Chart Cards ── */
    .chart-card {
        border-radius: 14px;
        border: 1.5px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        overflow: hidden;
    }
    .chart-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--bs-border-color);
        display: flex; align-items: center; justify-content: space-between;
    }
    .chart-card-title { font-size: .9rem; font-weight: 700; letter-spacing: -.2px; }
    .chart-card-body { padding: 16px 20px; }

    /* ── Period Buttons ── */
    .period-btn {
        padding: 4px 12px;
        border-radius: 8px;
        font-size: .75rem;
        font-weight: 500;
        border: 1.5px solid var(--bs-border-color);
        background: transparent;
        color: var(--bs-body-color);
        cursor: pointer;
        transition: all .15s;
    }
    .period-btn:hover, .period-btn.active {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }

    /* ── Widget Section ── */
    .widget-toolbar {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 16px;
        background: var(--bs-body-bg);
        border: 1.5px solid var(--bs-border-color);
        border-radius: 10px;
        margin-bottom: 16px;
    }
    .sortable-ghost   { opacity: .35; }
    .sortable-chosen  { box-shadow: 0 0 0 3px rgba(79,70,229,.4); border-radius: 14px; }
    #widgetGrid.drag-mode [data-widget] { cursor: grab; }
    #widgetGrid.drag-mode .chart-card  { border: 2px dashed rgba(79,70,229,.4); }

    /* ── Transactions Table ── */
    .tx-table th {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: #9ca3af;
        border-bottom: 1.5px solid var(--bs-border-color);
        padding: 10px 16px;
        background: transparent;
    }
    .tx-table td { padding: 12px 16px; vertical-align: middle; border-color: var(--bs-border-color); }
    .tx-table tbody tr:hover { background: rgba(79,70,229,.03); }
    .tx-id { font-size: .82rem; font-weight: 600; font-family: monospace; }

    /* ── Top Performers ── */
    .performer-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .performer-item:last-child { border-bottom: none; padding-bottom: 0; }
    .performer-rank {
        width: 22px; height: 22px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .68rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .rank-1 { background: #fef3c7; color: #d97706; }
    .rank-2 { background: #f1f5f9; color: #64748b; }
    .rank-3 { background: #fef2f2; color: #dc2626; }
    .rank-n { background: rgba(79,70,229,.08); color: #4f46e5; }
    .perf-bar-bg {
        height: 4px; border-radius: 4px; background: var(--bs-border-color); margin-top: 4px; overflow: hidden;
    }
    .perf-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg,#4f46e5,#7c3aed); }
</style>
@endpush

@section('content')

{{-- ── Welcome Banner ── --}}
<div class="dash-welcome">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3" style="position:relative;z-index:1;">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="live-dot"></span>
                <span style="font-size:.75rem; opacity:.75;">Live Dashboard</span>
                <span style="font-size:.75rem; opacity:.55;" id="dashClock"></span>
            </div>
            <h4 class="fw-bold mb-1">Good {{ date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }} 👋</h4>
            <p class="mb-0 opacity-75 small">Here's what's happening with your system today, {{ now()->format('l, d F Y') }}.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.transactions.index') }}"  class="quick-action-btn"><i class="bi bi-arrow-left-right me-1"></i>Transactions</a>
            <a href="{{ route('admin.fraud-alerts.index') }}"  class="quick-action-btn"><i class="bi bi-shield-exclamation me-1"></i>Fraud Alerts</a>
            <a href="{{ route('admin.reports.transactions') }}" class="quick-action-btn"><i class="bi bi-bar-chart me-1"></i>Reports</a>
        </div>
    </div>
</div>

{{-- ── KPI Cards ── --}}
<div class="row g-3 mb-4">
    {{-- Wallet Balance --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Wallet Balance</div>
                    <div class="kpi-value text-success">₹{{ number_format($stats['wallet_balance'], 0) }}</div>
                    <div class="kpi-sub text-success">
                        <i class="bi bi-arrow-up-short"></i>Today txns: ₹{{ number_format($stats['today_transactions_amount'], 0) }}
                    </div>
                </div>
                <div class="kpi-icon" style="background:rgba(16,185,129,.12); color:#10b981;">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card indigo">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Transactions</div>
                    <div class="kpi-value" style="color:#4f46e5;">{{ number_format($stats['total_transactions']) }}</div>
                    <div class="kpi-sub text-primary">Today: {{ $stats['today_transactions'] }} new</div>
                </div>
                <div class="kpi-icon" style="background:rgba(79,70,229,.12); color:#4f46e5;">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Fraud Alerts --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card red">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Fraud Alerts</div>
                    <div class="kpi-value text-danger">{{ $stats['fraud_alerts_open'] ?? 0 }}</div>
                    <div class="kpi-sub text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ $stats['fraud_alerts_critical'] }} critical
                    </div>
                </div>
                <div class="kpi-icon" style="background:rgba(239,68,68,.12); color:#ef4444;">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Users --}}
    <div class="col-6 col-md-3">
        <div class="kpi-card amber">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="kpi-label">Active Users</div>
                    <div class="kpi-value" style="color:#f59e0b;">{{ $stats['active_users'] }}</div>
                    <div class="kpi-sub text-muted">{{ $stats['total_employees'] }} employees total</div>
                </div>
                <div class="kpi-icon" style="background:rgba(245,158,11,.12); color:#f59e0b;">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Mini Stats Row ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div>
                <div style="font-size:.72rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.8px;">Present Today</div>
                <div class="fw-bold fs-5 text-success mt-1">{{ $stats['present_today'] }}</div>
                <div style="font-size:.72rem;" class="text-muted">On leave: {{ $stats['on_leave_today'] }}</div>
            </div>
            <div class="mini-stat-icon" style="background:rgba(16,185,129,.1); color:#10b981;">
                <i class="bi bi-person-check"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div>
                <div style="font-size:.72rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.8px;">Pending Tasks</div>
                <div class="fw-bold fs-5 text-warning mt-1">{{ $stats['pending_tasks'] }}</div>
                <div style="font-size:.72rem;" class="text-danger">{{ $stats['overdue_tasks'] }} overdue</div>
            </div>
            <div class="mini-stat-icon" style="background:rgba(245,158,11,.1); color:#f59e0b;">
                <i class="bi bi-kanban"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div>
                <div style="font-size:.72rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.8px;">Failed Tx Today</div>
                <div class="fw-bold fs-5 text-danger mt-1">{{ $stats['failed_transactions'] }}</div>
                <div style="font-size:.72rem;" class="text-muted">Needs review</div>
            </div>
            <div class="mini-stat-icon" style="background:rgba(239,68,68,.1); color:#ef4444;">
                <i class="bi bi-x-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="mini-stat">
            <div>
                <div style="font-size:.72rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:.8px;">Departments</div>
                <div class="fw-bold fs-5 mt-1" style="color:#6366f1;">{{ \App\Models\Department::active()->count() }}</div>
                <div style="font-size:.72rem;" class="text-muted">Active units</div>
            </div>
            <div class="mini-stat-icon" style="background:rgba(99,102,241,.1); color:#6366f1;">
                <i class="bi bi-building"></i>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row 1 ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <span class="chart-card-title"><i class="bi bi-graph-up me-2 text-primary"></i>Transaction Volume</span>
                <div class="d-flex gap-1">
                    <button class="period-btn" onclick="updateChart('transactions',7)">7D</button>
                    <button class="period-btn active" onclick="updateChart('transactions',30)">30D</button>
                    <button class="period-btn" onclick="updateChart('transactions',90)">90D</button>
                </div>
            </div>
            <div class="chart-card-body">
                <div id="transactionChart" style="height:280px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <span class="chart-card-title"><i class="bi bi-pie-chart me-2 text-danger"></i>Fraud by Type</span>
            </div>
            <div class="chart-card-body">
                <div id="fraudChart" style="height:280px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Charts Row 2 ── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <span class="chart-card-title"><i class="bi bi-calendar-check me-2 text-success"></i>Attendance Overview (30 days)</span>
            </div>
            <div class="chart-card-body">
                <div id="attendanceChart" style="height:230px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <span class="chart-card-title"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Monthly Transaction Trend</span>
            </div>
            <div class="chart-card-body">
                <div id="revenueChart" style="height:230px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Draggable Widget Toolbar ── --}}
<div class="widget-toolbar">
    <i class="bi bi-grid-3x3-gap text-muted"></i>
    <span class="small text-muted fw-semibold">Widgets</span>
    <button class="btn btn-sm btn-outline-secondary py-0 px-3 ms-1" id="toggleWidgets" style="border-radius:8px; font-size:.78rem;">
        <i class="bi bi-arrows-move me-1"></i>Arrange
    </button>
    <button class="btn btn-sm btn-outline-danger py-0 px-3 d-none" id="resetLayout" style="border-radius:8px; font-size:.78rem;">
        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
    </button>
    <span class="badge text-bg-primary d-none ms-1" id="dragHint" style="border-radius:8px;">Drag to rearrange</span>
</div>

<div id="widgetGrid" class="row g-3 mb-4">
    <div class="col-12" data-widget="quick-stats">
        <div class="chart-card">
            <div class="chart-card-header">
                <span class="chart-card-title">
                    <i class="bi bi-lightning-charge me-2 text-warning"></i>Quick Stats
                </span>
                <button class="btn btn-sm btn-outline-secondary widget-hide py-0 px-2" style="border-radius:8px;">
                    <i class="bi bi-dash"></i>
                </button>
            </div>
            <div class="chart-card-body widget-body">
                <div class="row g-3 text-center">
                    <div class="col-3">
                        <div class="fw-bold text-primary fs-5">{{ $stats['total_transactions'] }}</div>
                        <div class="small text-muted">Transactions</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-success fs-5">₹{{ number_format($stats['wallet_balance'], 0) }}</div>
                        <div class="small text-muted">Wallet</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold text-danger fs-5">{{ $stats['fraud_alerts_open'] }}</div>
                        <div class="small text-muted">Open Alerts</div>
                    </div>
                    <div class="col-3">
                        <div class="fw-bold fs-5" style="color:#6366f1;">{{ $stats['total_employees'] }}</div>
                        <div class="small text-muted">Employees</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Recent Transactions + Top Performers ── --}}
<div class="row g-3">
    {{-- Recent Transactions --}}
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <span class="chart-card-title"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Transactions</span>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-sm btn-outline-primary py-0 px-3" style="border-radius:8px; font-size:.78rem;">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="chart-card-body p-0">
                <div class="table-responsive">
                    <table class="table tx-table mb-0">
                        <thead>
                            <tr>
                                <th>Transaction ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Risk</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $tx)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.transactions.show', $tx) }}"
                                       class="tx-id text-decoration-none text-primary">
                                        {{ $tx->transaction_id }}
                                    </a>
                                    @if($tx->is_flagged)
                                        <i class="bi bi-flag-fill text-danger ms-1" style="font-size:.7rem;"></i>
                                    @endif
                                </td>
                                <td class="fw-semibold small">₹{{ number_format($tx->amount, 2) }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $tx->status_badge }}-subtle text-{{ $tx->status_badge }}" style="font-size:.7rem;">
                                        {{ ucfirst($tx->status) }}
                                    </span>
                                </td>
                                <td>
                                    @php $risk = $tx->risk_score; $rc = $risk >= 70 ? 'danger' : ($risk >= 40 ? 'warning' : 'success'); @endphp
                                    <span class="badge rounded-pill bg-{{ $rc }}-subtle text-{{ $rc }}" style="font-size:.7rem;">
                                        {{ $risk }}%
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $tx->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>No transactions yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Performers --}}
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <div class="chart-card-header">
                <span class="chart-card-title"><i class="bi bi-trophy me-2 text-warning"></i>Top Performers</span>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-primary py-0 px-3" style="border-radius:8px; font-size:.78rem;">
                    All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="chart-card-body">
                @forelse($topEmployees as $i => $emp)
                <div class="performer-item">
                    <div class="performer-rank {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-n')) }}">
                        {{ $i + 1 }}
                    </div>
                    <img src="{{ $emp->user->avatar_url }}" class="rounded-circle border" width="34" height="34" alt="">
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="small fw-semibold text-truncate">{{ $emp->user->name }}</div>
                        <div class="perf-bar-bg">
                            <div class="perf-bar-fill" style="width:{{ $emp->performance_score }}%;"></div>
                        </div>
                    </div>
                    <span class="badge rounded-pill bg-success-subtle text-success" style="font-size:.7rem; white-space:nowrap;">
                        {{ $emp->performance_score }}%
                    </span>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-3 d-block mb-2"></i>No employee data
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
// ── Live Clock ──
function dashClock() {
    const el = document.getElementById('dashClock');
    if (el) el.textContent = new Date().toLocaleTimeString();
}
dashClock();
setInterval(dashClock, 1000);

// ── Period buttons ──
document.querySelectorAll('.period-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

// ── Drag-drop widgets ──
const widgetGrid = document.getElementById('widgetGrid');
let sortable = null;
function getStoredOrder() { try { return JSON.parse(localStorage.getItem('dashboard_widget_order')||'null'); } catch { return null; } }
function applyStoredOrder() {
    const order = getStoredOrder();
    if (!order) return;
    order.forEach(id => { const el = widgetGrid.querySelector(`[data-widget="${id}"]`); if (el) widgetGrid.appendChild(el); });
}
function saveOrder() {
    localStorage.setItem('dashboard_widget_order', JSON.stringify([...widgetGrid.querySelectorAll('[data-widget]')].map(e => e.dataset.widget)));
}
applyStoredOrder();

document.getElementById('toggleWidgets').addEventListener('click', function () {
    const active = widgetGrid.classList.toggle('drag-mode');
    document.getElementById('dragHint').classList.toggle('d-none', !active);
    document.getElementById('resetLayout').classList.toggle('d-none', !active);
    this.innerHTML = active ? '<i class="bi bi-check2 me-1"></i>Done' : '<i class="bi bi-arrows-move me-1"></i>Arrange';
    if (active && !sortable) {
        sortable = Sortable.create(widgetGrid, { animation: 150, ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen', onEnd: saveOrder });
    } else if (!active && sortable) { sortable.destroy(); sortable = null; }
});
document.getElementById('resetLayout').addEventListener('click', () => { localStorage.removeItem('dashboard_widget_order'); location.reload(); });

// ── Widget collapse ──
document.querySelectorAll('.widget-hide').forEach(btn => {
    btn.addEventListener('click', function () {
        const body = this.closest('.chart-card').querySelector('.widget-body');
        const icon = this.querySelector('i');
        if (body) {
            body.style.display = body.style.display === 'none' ? '' : 'none';
            icon.className = body.style.display === 'none' ? 'bi bi-plus' : 'bi bi-dash';
        }
    });
});

// ── Charts ──
const txData = @json($transactionChart);
const txChart = new ApexCharts(document.getElementById('transactionChart'), {
    series: [{ name: 'Volume ($)', data: txData.amounts }],
    chart: { type: 'area', height: 280, toolbar: { show: false } },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
    colors: ['#4f46e5'],
    xaxis: { categories: txData.labels, labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(1)+'K' : v) } },
    tooltip: { y: { formatter: v => '$' + Number(v).toLocaleString() } },
    grid: { borderColor: 'rgba(0,0,0,0.04)' },
    dataLabels: { enabled: false },
});
txChart.render();

const fraudData = @json($fraudByType);
new ApexCharts(document.getElementById('fraudChart'), {
    series: fraudData.counts.length ? fraudData.counts : [1],
    labels: fraudData.labels.length ? fraudData.labels : ['No Alerts'],
    chart: { type: 'donut', height: 280 },
    colors: ['#ef4444','#f59e0b','#6366f1','#10b981','#3b82f6'],
    legend: { position: 'bottom', fontSize: '11px' },
    plotOptions: { pie: { donut: { size: '68%' } } },
    dataLabels: { style: { fontSize: '11px' } },
}).render();

const attData = @json($attendanceChart);
new ApexCharts(document.getElementById('attendanceChart'), {
    series: [{ name: 'Present', data: attData.present }, { name: 'Absent', data: attData.absent }],
    chart: { type: 'bar', height: 230, toolbar: { show: false }, stacked: true },
    colors: ['#10b981','#ef4444'],
    xaxis: { categories: attData.labels, labels: { style: { fontSize: '10px' } } },
    plotOptions: { bar: { borderRadius: 4 } },
    legend: { position: 'top', fontSize: '11px' },
    dataLabels: { enabled: false },
}).render();

const revData = @json($monthlyRevenue);
new ApexCharts(document.getElementById('revenueChart'), {
    series: [{ name: 'Transactions (₹)', data: revData.revenue }],
    chart: { type: 'bar', height: 230, toolbar: { show: false } },
    colors: ['#4f46e5'],
    xaxis: { categories: revData.labels, labels: { style: { fontSize: '10px' } } },
    yaxis: { labels: { formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) } },
    plotOptions: { bar: { borderRadius: 6 } },
    dataLabels: { enabled: false },
}).render();

function updateChart(type, days) {
    $.get('/admin/dashboard/chart', { type, days }, function (data) {
        txChart.updateSeries([{ name: 'Volume ($)', data: data.amounts }]);
        txChart.updateOptions({ xaxis: { categories: data.labels } });
    });
}
</script>
@endpush
