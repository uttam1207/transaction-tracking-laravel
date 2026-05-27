@extends('layouts.app')

@section('title', 'Transaction Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Transaction Report</h4>
        <p class="text-muted mb-0">Comprehensive transaction analytics</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.transactions.export.csv') }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export CSV
        </a>
        <a href="{{ route('admin.reports.pdf', 'transactions') }}" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.reports.transactions') }}" class="row g-2">
            <div class="col-md-2">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach(['pending', 'success', 'failed', 'cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="currency" class="form-select">
                    <option value="">All Currencies</option>
                    @foreach(['USD', 'EUR', 'GBP'] as $c)
                        <option value="{{ $c }}" @selected(request('currency') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Generate</button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-primary">{{ number_format($stats['total_count'] ?? 0) }}</div>
            <div class="text-muted">Total Transactions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success">
                ${{ number_format($stats['total_amount'] ?? 0, 2) }}
            </div>
            <div class="text-muted">Total Volume</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-danger">{{ number_format($stats['flagged_count'] ?? 0) }}</div>
            <div class="text-muted">Flagged</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-warning">
                ${{ number_format($stats['total_fees'] ?? 0, 2) }}
            </div>
            <div class="text-muted">Total Fees</div>
        </div>
    </div>
</div>

{{-- Chart --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">Transaction Volume by Date</div>
    <div class="card-body">
        <div id="txChart" style="height: 250px;"></div>
    </div>
</div>

{{-- Breakdown by Status --}}
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">By Status</div>
            <div class="card-body">
                @foreach($byStatus ?? [] as $s)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ ucfirst($s->status) }}</span>
                    <strong>{{ number_format($s->count) }}</strong>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent fw-semibold">By Payment Method</div>
            <div class="card-body">
                @foreach($byMethod ?? [] as $m)
                <div class="d-flex justify-content-between mb-2">
                    <span>{{ ucwords(str_replace('_', ' ', $m->payment_method)) }}</span>
                    <strong>{{ number_format($m->count) }}</strong>
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
    chart: { type: 'area', height: 250, toolbar: { show: false } },
    series: [{ name: 'Volume ($)', data: chartData.amounts }],
    xaxis: { categories: chartData.labels },
    colors: ['#6366f1'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
    tooltip: { y: { formatter: v => '$' + v.toLocaleString() } }
}).render();
</script>
@endpush
