@extends('layouts.app')
@section('title', 'Feed Deduction History')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.feed.calculator') }}">Feed Calculator</a></li>
    <li class="breadcrumb-item active">Deduction History</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Feed Deduction History</h4>
            <p>Daily auto-deduction log — feed items deducted from inventory stock</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.feed.calculator') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back to Calculator
            </a>
        </div>
    </div>
</div>

{{-- Daily Summary Bar --}}
@if($dailySummary->count())
<div class="card-glass mb-4">
    <div class="px-4 py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="bi bi-bar-chart me-2 text-primary"></i>Last 30 Days — Deduction Summary</h6>
    </div>
    <div class="p-3" style="overflow-x:auto;">
        <div class="d-flex gap-2" style="min-width:max-content;">
            @foreach($dailySummary as $day)
            @php
                $isToday = \Carbon\Carbon::parse($day->date)->isToday();
            @endphp
            <div style="text-align:center;min-width:72px;">
                <div style="font-size:.68rem;color:var(--text-muted);margin-bottom:4px;">
                    {{ \Carbon\Carbon::parse($day->date)->format('d M') }}
                </div>
                <div style="
                    background:{{ $isToday ? 'var(--primary)' : 'rgba(var(--primary-rgb),.15)' }};
                    color:{{ $isToday ? '#fff' : 'var(--primary)' }};
                    border-radius:10px;
                    padding:8px 6px;
                    font-size:.75rem;
                    font-weight:700;
                    line-height:1.3;
                ">
                    {{ $day->item_count }}<br>
                    <span style="font-size:.65rem;font-weight:500;">items</span>
                </div>
                <div style="font-size:.65rem;color:var(--text-muted);margin-top:3px;">
                    {{ number_format($day->total_qty, 0) }} kg
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="card-glass mb-4">
    <form method="GET" action="{{ route('admin.feed.deductions') }}" class="p-3">
        <div class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label" style="font-size:.78rem;font-weight:600;">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-sm"
                    value="{{ request('date_from') }}">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label" style="font-size:.78rem;font-weight:600;">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-sm"
                    value="{{ request('date_to') }}">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label" style="font-size:.78rem;font-weight:600;">Feed / Item Name</label>
                <input type="text" name="item" class="form-control form-control-sm" placeholder="e.g. Green Fodder"
                    value="{{ request('item') }}">
            </div>
            <div class="col-sm-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary px-4 flex-fill">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @if(request()->hasAny(['date_from','date_to','item']))
                    <a href="{{ route('admin.feed.deductions') }}" class="btn btn-sm btn-outline-secondary px-3">
                        <i class="bi bi-x-lg"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Deduction Records --}}
<div class="card-glass">
    @if($movements->count() === 0)
        <div class="text-center py-5" style="color:var(--text-muted);">
            <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3;"></i>
            <div class="mt-3" style="font-size:.9rem;">No deduction records found.</div>
            @if(request()->hasAny(['date_from','date_to','item']))
                <a href="{{ route('admin.feed.deductions') }}" class="btn btn-sm btn-outline-secondary mt-3">Clear Filters</a>
            @endif
        </div>
    @else
    @php
        $grouped = $movements->getCollection()->groupBy(fn($m) => \Carbon\Carbon::parse($m->date)->toDateString());
    @endphp

    @foreach($grouped as $date => $rows)
    @php
        $parsedDate = \Carbon\Carbon::parse($date);
        $isToday    = $parsedDate->isToday();
        $isYesterday= $parsedDate->isYesterday();
        $label      = $isToday ? 'Today' : ($isYesterday ? 'Yesterday' : $parsedDate->format('d M Y'));
        $totalQty   = $rows->sum('quantity');
    @endphp
    <div class="border-bottom" style="background:{{ $isToday ? 'rgba(var(--primary-rgb),.04)' : 'transparent' }};">
        {{-- Date Header --}}
        <div class="d-flex align-items-center justify-content-between px-4 py-2"
            style="background:rgba(0,0,0,.02);border-bottom:1px solid rgba(0,0,0,.05);">
            <div class="d-flex align-items-center gap-2">
                <span style="font-size:.85rem;font-weight:700;color:var(--text);">
                    <i class="bi bi-calendar3 me-2" style="color:var(--primary);"></i>{{ $label }}
                    @if(!$isToday && !$isYesterday)
                        <span style="font-size:.72rem;color:var(--text-muted);font-weight:400;margin-left:4px;">
                            {{ $parsedDate->format('l') }}
                        </span>
                    @endif
                </span>
                @if($isToday)
                    <span class="spill spill-primary" style="font-size:.65rem;">Today</span>
                @endif
            </div>
            <div style="font-size:.78rem;color:var(--text-muted);">
                <i class="bi bi-boxes me-1"></i>{{ $rows->count() }} items &nbsp;·&nbsp;
                <i class="bi bi-arrow-down-circle me-1 text-danger"></i>
                <strong style="color:var(--text);">{{ number_format($totalQty, 1) }} kg</strong> total
            </div>
        </div>

        {{-- Items Table --}}
        <div class="table-responsive">
            <table class="table modern-table mb-0">
                <thead>
                    <tr>
                        <th>Inventory Item</th>
                        <th class="text-end">Qty Deducted</th>
                        <th>Remarks</th>
                        <th>Recorded By</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $m)
                    @php
                        // Parse required vs deducted from remarks
                        preg_match('/Required:\s*([\d.]+)\s*(\w+),\s*Deducted:\s*([\d.]+)/', $m->remarks ?? '', $rm);
                        $required  = $rm[1] ?? null;
                        $deducted  = $rm[3] ?? null;
                        $unit      = $rm[2] ?? $m->inventoryItem?->unit ?? 'kg';
                        $isPartial = $required && $deducted && (float)$deducted < (float)$required;
                    @endphp
                    <tr>
                        <td>
                            <span class="fw-semibold" style="font-size:.86rem;">
                                {{ $m->inventoryItem?->name ?? '—' }}
                            </span>
                            @if($m->inventoryItem?->category)
                                <div style="font-size:.72rem;color:var(--text-muted);">{{ $m->inventoryItem->category }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <span style="font-size:.9rem;font-weight:700;color:{{ $isPartial ? '#d97706' : '#059669' }};">
                                {{ number_format($m->quantity, 1) }} {{ $unit }}
                            </span>
                            @if($required && $isPartial)
                                <div style="font-size:.7rem;color:#d97706;">
                                    of {{ number_format($required, 1) }} {{ $unit }} needed
                                </div>
                            @endif
                        </td>
                        <td style="font-size:.78rem;color:var(--text-muted);max-width:220px;">
                            {{ $m->remarks ?? '—' }}
                        </td>
                        <td style="font-size:.8rem;">
                            {{ $m->recorder?->name ?? 'System' }}
                        </td>
                        <td class="text-center">
                            @if($isPartial)
                                <span class="spill spill-warning" style="font-size:.7rem;">Partial</span>
                            @else
                                <span class="spill spill-success" style="font-size:.7rem;"><i class="bi bi-check2"></i> Done</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    {{-- Pagination --}}
    @if($movements->hasPages())
    <div class="px-4 py-3 d-flex justify-content-center">
        {{ $movements->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
