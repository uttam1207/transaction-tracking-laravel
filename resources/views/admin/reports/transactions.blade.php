@extends('layouts.app')
@section('title', 'Transaction Report')

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Transaction Report</h4>
            <p>Comprehensive transaction analytics and export</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transactions.export.csv') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-file-earmark-excel me-1"></i>CSV
            </a>
            <a href="{{ route('admin.reports.pdf', 'transactions') }}" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
        </div>
    </div>
</div>

<div class="filter-card">
    <form method="GET" action="{{ route('admin.reports.transactions') }}" class="row g-2 align-items-end">
        <div class="col-md-2">
            <label class="flabel">From Date</label>
            <input type="date" name="from_date" class="form-control"
                value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}"
                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
        </div>
        <div class="col-md-2">
            <label class="flabel">To Date</label>
            <input type="date" name="to_date" class="form-control"
                value="{{ request('to_date', now()->format('Y-m-d')) }}"
                style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
        </div>
        <div class="col-md-2">
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Status</option>
                @foreach(['pending','success','failed','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Currency</label>
            <select name="currency" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Currencies</option>
                @foreach(['INR'] as $c)
                    <option value="{{ $c }}" @selected(request('currency') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Generate</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #6366f1;">
            <div style="font-size:1.8rem;font-weight:800;color:#6366f1;line-height:1;">{{ number_format($stats['total_count'] ?? 0) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Transactions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #16a34a;">
            <div style="font-size:1.8rem;font-weight:800;color:#16a34a;line-height:1;">₹{{ number_format($stats['total_amount'] ?? 0, 2) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Volume</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #dc2626;">
            <div style="font-size:1.8rem;font-weight:800;color:#dc2626;line-height:1;">{{ number_format($stats['flagged_count'] ?? 0) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Flagged</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-card text-center" style="padding:18px;border-top:4px solid #f59e0b;">
            <div style="font-size:1.8rem;font-weight:800;color:#f59e0b;line-height:1;">₹{{ number_format($stats['total_fees'] ?? 0, 2) }}</div>
            <div style="font-size:.82rem;color:#6b7280;margin-top:4px;">Total Fees</div>
        </div>
    </div>
</div>

<div class="info-card mb-4">
    <div class="info-card-hdr"><i class="bi bi-graph-up me-2"></i>Transaction Volume by Date</div>
    <div class="info-card-body" style="padding:16px;">
        <div id="txChart" style="height:240px;"></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-pie-chart me-2"></i>By Status</div>
            <div class="info-card-body">
                @foreach($byStatus ?? [] as $s)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:.85rem;color:#374151;">{{ ucfirst($s->status) }}</span>
                    <span style="font-weight:700;font-size:.85rem;color:#111827;">{{ number_format($s->count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-card-hdr"><i class="bi bi-credit-card me-2"></i>By Payment Method</div>
            <div class="info-card-body">
                @foreach($byMethod ?? [] as $m)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="font-size:.85rem;color:#374151;">{{ ucwords(str_replace('_', ' ', $m->payment_method)) }}</span>
                    <span style="font-weight:700;font-size:.85rem;color:#111827;">{{ number_format($m->count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const chartData = @json($chartData ?? ['labels' => [], 'amounts' => []]);
new ApexCharts(document.querySelector('#txChart'), {
    chart: { type: 'area', height: 240, toolbar: { show: false } },
    series: [{ name: 'Volume (₹)', data: chartData.amounts }],
    xaxis: { categories: chartData.labels },
    colors: ['#6366f1'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } },
    tooltip: { y: { formatter: v => '₹' + v.toLocaleString() } },
    grid: { borderColor: '#f3f4f6' }
}).render();
</script>
@endpush
