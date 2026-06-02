@extends('layouts.app')
@section('title', 'Financial Summary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.transactions') }}">Reports</a></li>
    <li class="breadcrumb-item active">Financial Summary</li>
@endsection

@push('styles')
<style>
.report-hero {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #059669 100%);
    border-radius: 16px; padding: 24px 28px; margin-bottom: 24px; color: #fff; position: relative; overflow: hidden;
}
.report-hero::before { content:''; position:absolute; top:-40px; right:-30px; width:180px; height:180px; background:rgba(255,255,255,.06); border-radius:50%; }
.kpi-box {
    background:#fff; border:1px solid #e5e7eb; border-radius:14px;
    padding:20px 22px; box-shadow:0 1px 4px rgba(0,0,0,.04);
    position:relative; overflow:hidden;
}
.kpi-box::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; border-radius:14px 14px 0 0; }
.kpi-box.green::before { background: linear-gradient(90deg,#10b981,#34d399); }
.kpi-box.red::before   { background: linear-gradient(90deg,#ef4444,#f87171); }
.kpi-box.blue::before  { background: linear-gradient(90deg,#3b82f6,#60a5fa); }
.kpi-box.purple::before{ background: linear-gradient(90deg,#8b5cf6,#a78bfa); }
.kpi-box.amber::before { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
.kpi-val  { font-size:1.6rem; font-weight:800; letter-spacing:-.5px; line-height:1; }
.kpi-lbl  { font-size:.72rem; color:#9ca3af; text-transform:uppercase; letter-spacing:.5px; margin-top:4px; }
.chart-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.04); overflow:hidden; margin-bottom:20px; }
.chart-card-header { padding:14px 20px; border-bottom:1px solid #f3f4f6; font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#6b7280; background:#f9fafb; display:flex; align-items:center; gap:8px; }
.chart-card-header i { color:#4f46e5; font-size:.9rem; }
.chart-card-body { padding:20px; }
.cat-row { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid #f9fafb; }
.cat-row:last-child { border-bottom:none; }
.cat-name { flex:1; font-size:.85rem; font-weight:600; color:#111827; }
.cat-bar-wrap { width:160px; }
.cat-bar-bg { height:6px; background:#f3f4f6; border-radius:3px; overflow:hidden; }
.cat-bar-fill { height:100%; border-radius:3px; background:linear-gradient(90deg,#4f46e5,#7c3aed); }
.cat-amount { font-size:.82rem; font-weight:700; color:#4f46e5; min-width:90px; text-align:right; }
.cat-count { font-size:.72rem; color:#9ca3af; min-width:50px; text-align:right; }
</style>
@endpush

@section('content')

<div class="report-hero">
    <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h4 class="mb-1 fw-bold" style="font-weight:800;">Financial Summary</h4>
            <p class="mb-0" style="opacity:.7;font-size:.83rem;">Credit vs debit analysis, category breakdown, and net balance trend</p>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label style="font-size:.8rem;opacity:.8;">Year</label>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()"
                    style="width:100px;border-radius:8px;background:rgba(255,255,255,.15);
                           border:1px solid rgba(255,255,255,.3);color:#fff;font-weight:600;">
                @foreach(array_reverse($years) as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }} style="color:#111;background:#fff;">{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-2 col-6">
        <div class="kpi-box green">
            <div class="kpi-val text-success">₹{{ number_format($totals['total_credit'], 0) }}</div>
            <div class="kpi-lbl">Total Income</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="kpi-box red">
            <div class="kpi-val text-danger">₹{{ number_format($totals['total_debit'], 0) }}</div>
            <div class="kpi-lbl">Total Expenses</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="kpi-box {{ $totals['net_balance'] >= 0 ? 'green' : 'red' }}">
            <div class="kpi-val {{ $totals['net_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                ₹{{ number_format(abs($totals['net_balance']), 0) }}
            </div>
            <div class="kpi-lbl">Net Balance {{ $totals['net_balance'] < 0 ? '(Deficit)' : '' }}</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="kpi-box blue">
            <div class="kpi-val text-primary">{{ number_format($totals['total_txn']) }}</div>
            <div class="kpi-lbl">Total Transactions</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="kpi-box purple">
            <div class="kpi-val" style="color:#7c3aed;">{{ number_format($totals['success_count']) }}</div>
            <div class="kpi-lbl">Successful</div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="kpi-box amber">
            <div class="kpi-val" style="color:#d97706;">{{ number_format($totals['flagged_count']) }}</div>
            <div class="kpi-lbl">Flagged</div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Monthly Chart --}}
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="bi bi-bar-chart-line"></i>Monthly Income vs Expenses — {{ $year }}
            </div>
            <div class="chart-card-body">
                <div id="monthlyChart" style="height:300px;"></div>
            </div>
        </div>

        {{-- Net Balance Trend --}}
        <div class="chart-card">
            <div class="chart-card-header">
                <i class="bi bi-graph-up"></i>Net Balance Trend — {{ $year }}
            </div>
            <div class="chart-card-body">
                <div id="netChart" style="height:200px;"></div>
            </div>
        </div>
    </div>

    {{-- Categories --}}
    <div class="col-lg-4">
        <div class="chart-card" style="height:100%;">
            <div class="chart-card-header"><i class="bi bi-pie-chart"></i>Top Categories ({{ $year }})</div>
            <div class="chart-card-body">
                @php $maxCatTotal = $byCategory->max('total') ?: 1; @endphp
                @forelse($byCategory as $cat)
                <div class="cat-row">
                    <div class="cat-name">{{ ucfirst($cat->category) }}</div>
                    <div class="cat-bar-wrap">
                        <div class="cat-bar-bg">
                            <div class="cat-bar-fill" style="width:{{ min(100, ($cat->total / $maxCatTotal) * 100) }}%;"></div>
                        </div>
                    </div>
                    <div class="cat-amount">₹{{ number_format($cat->total, 0) }}</div>
                    <div class="cat-count">{{ $cat->count }}x</div>
                </div>
                @empty
                <p style="text-align:center;color:#9ca3af;padding:24px 0;margin:0;">No data for {{ $year }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const months      = @json($monthLabels);
const creditData  = @json($creditData);
const debitData   = @json($debitData);
const netData     = @json($netData);

// Monthly Income vs Expenses (grouped bar)
new ApexCharts(document.getElementById('monthlyChart'), {
    series: [
        { name: 'Income (Credit)', data: creditData },
        { name: 'Expenses (Debit)', data: debitData },
    ],
    chart: { type: 'bar', height: 300, toolbar: { show: false } },
    colors: ['#10b981', '#ef4444'],
    plotOptions: { bar: { columnWidth: '60%', borderRadius: 4, grouped: true } },
    xaxis: { categories: months, labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) } },
    tooltip: { y: { formatter: v => '₹' + Number(v).toLocaleString() } },
    legend: { position: 'top' },
    dataLabels: { enabled: false },
    grid: { borderColor: 'rgba(0,0,0,0.04)' },
}).render();

// Net Balance Trend (area)
new ApexCharts(document.getElementById('netChart'), {
    series: [{ name: 'Net Balance', data: netData }],
    chart: { type: 'area', height: 200, toolbar: { show: false } },
    colors: [netData.some(v => v < 0) ? '#f59e0b' : '#4f46e5'],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.02 } },
    xaxis: { categories: months, labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : (v <= -1000 ? '-'+Math.abs(v/1000).toFixed(0)+'K' : v)) } },
    tooltip: { y: { formatter: v => '₹' + Number(v).toLocaleString() } },
    dataLabels: { enabled: false },
    annotations: { yaxis: [{ y: 0, borderColor: '#9ca3af', strokeDashArray: 4, label: { text: 'Break-even', style: { fontSize: '10px', color: '#9ca3af', background: 'transparent' } } }] },
}).render();
</script>
@endpush
