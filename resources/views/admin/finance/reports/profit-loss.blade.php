@extends('layouts.app')
@section('title', 'Profit & Loss Statement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.coa.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Profit &amp; Loss</li>
@endsection

@push('styles')
<style>
/* ── Format toggle ── */
.fmt-toggle { display:inline-flex;border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden; }
.fmt-toggle a {
    padding:6px 18px;font-size:.78rem;font-weight:700;text-decoration:none;
    color:#6b7280;background:#fff;transition:all .15s;
}
.fmt-toggle a.active { background:#059669;color:#fff; }

/* ── Shared statement styles ── */
.stmt-wrap {
    background:#fff;border-radius:16px;
    box-shadow:0 2px 12px rgba(0,0,0,.07);
    overflow:hidden;margin-bottom:24px;
}
.stmt-header {
    text-align:center;padding:22px 28px 14px;
    border-bottom:2px solid #059669;
    background:linear-gradient(135deg,#ecfdf5,#d1fae5);
}
.stmt-header .firm-name  { font-size:1.15rem;font-weight:800;color:#1e293b;letter-spacing:.01em; }
.stmt-header .stmt-title { font-size:.95rem;font-weight:700;color:#059669;margin-top:2px; }
.stmt-header .stmt-date  { font-size:.78rem;color:#6b7280;margin-top:3px; }

/* ── Vertical format ── */
.v-section-head {
    padding:10px 24px;font-size:.72rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.07em;
    display:flex;justify-content:space-between;align-items:center;
}
.v-row {
    display:flex;justify-content:space-between;align-items:center;
    padding:8px 24px 8px 36px;border-bottom:1px solid #f8fafc;
    font-size:.83rem;
}
.v-row .acc-code {
    display:inline-block;background:#f0f4ff;color:#4f46e5;
    padding:1px 6px;border-radius:4px;font-size:.68rem;font-weight:700;
    font-family:monospace;margin-right:7px;
}
.v-subtotal {
    display:flex;justify-content:space-between;
    padding:9px 24px;font-size:.83rem;font-weight:700;
    border-top:1.5px solid #e2e8f0;border-bottom:1.5px solid #e2e8f0;
    background:#f8fafc;
}
.v-grandtotal {
    display:flex;justify-content:space-between;
    padding:13px 24px;font-size:.95rem;font-weight:800;
    background:#059669;color:#fff;
}
.v-net-profit {
    display:flex;justify-content:space-between;
    padding:16px 24px;font-size:1rem;font-weight:800;
    border-top:3px double #1e293b;
}
.v-spacer { height:16px; }

/* ── Horizontal (T-Account) format ── */
.h-table-wrap { display:flex;gap:0; }
.h-side { flex:1;min-width:0; }
.h-side + .h-side { border-left:3px solid #059669; }
.h-side-head {
    padding:11px 18px;font-size:.72rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.07em;
    display:flex;justify-content:space-between;
    border-bottom:1.5px solid #a7f3d0;
}
.h-cat-head {
    padding:8px 18px 4px;font-size:.7rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.05em;
    color:#6b7280;background:#f8fafc;
}
.h-row {
    display:flex;justify-content:space-between;align-items:center;
    padding:7px 18px 7px 26px;border-bottom:1px solid #f8fafc;
    font-size:.82rem;
}
.h-row .acc-code {
    display:inline-block;background:#f0f4ff;color:#4f46e5;
    padding:1px 5px;border-radius:4px;font-size:.67rem;font-weight:700;
    font-family:monospace;margin-right:6px;
}
.h-subtotal {
    display:flex;justify-content:space-between;
    padding:8px 18px;font-size:.82rem;font-weight:700;
    border-top:1.5px solid #e2e8f0;background:#f8fafc;
}
.h-net-row {
    display:flex;justify-content:space-between;align-items:center;
    padding:10px 18px;border-top:2px solid #e2e8f0;
    font-size:.85rem;font-weight:800;
}
.h-grandtotal {
    display:flex;justify-content:space-between;
    padding:12px 18px;font-size:.88rem;font-weight:800;
    border-top:2px solid #059669;
}
.h-spacer { height:12px; }
</style>
@endpush

@section('content')

@php
    $netIncome = $data['netIncome'];
    $isProfit  = $netIncome >= 0;
    $margin    = $data['netRevenue'] > 0 ? round(($netIncome / $data['netRevenue']) * 100, 1) : 0;
    $total     = $data['netRevenue'] + $data['netExpenses'];
    $pct       = $total > 0 ? min(100, ($data['netRevenue'] / $total) * 100) : 0;
    $fmt       = request('view', 'vertical');
    $periodLabel = $selectedPeriod ? $selectedPeriod->name : 'All Periods';
    $dateRange   = ($dateFrom && $dateTo) ? "$dateFrom — $dateTo" : ($selectedPeriod ? $selectedPeriod->start_date->format('d M Y') . ' — ' . $selectedPeriod->end_date->format('d M Y') : now()->format('d M Y'));
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Profit &amp; Loss Statement</h4>
            <p>Income and expense summary for the selected period</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            {{-- Format toggle --}}
            <div class="fmt-toggle">
                <a href="?{{ http_build_query(array_merge(request()->except('view'), ['view' => 'vertical'])) }}"
                   class="{{ $fmt === 'vertical' ? 'active' : '' }}">
                    <i class="bi bi-list-ul me-1"></i>Vertical
                </a>
                <a href="?{{ http_build_query(array_merge(request()->except('view'), ['view' => 'horizontal'])) }}"
                   class="{{ $fmt === 'horizontal' ? 'active' : '' }}">
                    <i class="bi bi-layout-split me-1"></i>Horizontal
                </a>
            </div>

            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.balance-sheet') }}" class="btn btn-outline-primary btn-sm">Balance Sheet</a>
            <a href="{{ route('admin.export.profit-loss', 'excel') }}?{{ http_build_query(request()->only(['period_id','date_from','date_to'])) }}"
               class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.export.profit-loss', 'pdf') }}?{{ http_build_query(request()->only(['period_id','date_from','date_to'])) }}"
               class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <button onclick="window.print()" class="btn btn-sm" style="background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #10b981;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Revenue</div>
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">&#8377;{{ number_format($data['netRevenue'], 2) }}</div>
            <div style="font-size:.72rem;color:#10b981;margin-top:3px;font-weight:600;">{{ $data['revenue']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #ef4444;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Expenses</div>
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">&#8377;{{ number_format($data['netExpenses'], 2) }}</div>
            <div style="font-size:.72rem;color:#ef4444;margin-top:3px;font-weight:600;">{{ $data['expenses']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $isProfit ? '#10b981' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Net {{ $isProfit ? 'Profit' : 'Loss' }}</div>
            <div style="font-size:1.45rem;font-weight:800;color:{{ $isProfit ? '#059669' : '#dc2626' }};margin-top:5px;line-height:1.2;">
                {{ $isProfit ? '+' : '' }}&#8377;{{ number_format($netIncome, 2) }}
            </div>
            <div style="font-size:.72rem;color:{{ $isProfit ? '#10b981' : '#ef4444' }};margin-top:3px;font-weight:600;">Revenue − Expenses</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #8b5cf6;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Net Margin</div>
            <div style="font-size:1.45rem;font-weight:800;color:#7c3aed;margin-top:5px;line-height:1.2;">{{ $margin }}%</div>
            <div style="font-size:.72rem;color:#8b5cf6;margin-top:3px;font-weight:600;">of total revenue</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#fff;border-radius:14px;padding:16px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:22px;">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="view" value="{{ $fmt }}">
        <div class="col-md-3">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">Period</label>
            <select name="period_id" class="form-select form-select-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                <option value="">— All Periods —</option>
                @foreach ($periods as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $periodId ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">From Date</label>
            <input type="date" name="date_from" class="form-control form-control-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;" value="{{ $dateFrom }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">To Date</label>
            <input type="date" name="date_to" class="form-control form-control-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;" value="{{ $dateTo }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Generate</button>
            <a href="{{ route('admin.finance.reports.profit-loss') }}?view={{ $fmt }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
        @if ($selectedPeriod)
        <div class="col-auto ms-auto">
            <span style="background:#dbeafe;color:#1d4ed8;padding:5px 14px;border-radius:20px;font-size:.78rem;font-weight:700;">
                <i class="bi bi-calendar3 me-1"></i>{{ $selectedPeriod->name }}
            </span>
        </div>
        @endif
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- VERTICAL FORMAT                                             --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if($fmt === 'vertical')

<div class="stmt-wrap">
    {{-- Header --}}
    <div class="stmt-header">
        <div class="firm-name">AS DAIRY FARM</div>
        <div class="stmt-title">PROFIT &amp; LOSS STATEMENT</div>
        <div class="stmt-date">For the Period: {{ $dateRange }} &nbsp;|&nbsp; {{ $periodLabel }}</div>
    </div>

    {{-- INCOME --}}
    <div class="v-section-head" style="background:#ecfdf5;color:#065f46;">
        <span><i class="bi bi-arrow-up-circle me-1"></i> INCOME</span>
        <span>Amount (&#8377;)</span>
    </div>
    @forelse($data['revenue'] as $row)
    <div class="v-row">
        <span>
            <span class="acc-code">{{ $row->account->code }}</span>
            {{ $row->account->name }}
        </span>
        <span style="color:#059669;font-weight:600;">{{ number_format($row->net, 2) }}</span>
    </div>
    @empty
    <div class="v-row"><span style="color:#9ca3af;">No revenue entries found.</span><span>0.00</span></div>
    @endforelse
    <div class="v-subtotal">
        <span>Total Income (A)</span>
        <span style="color:#059669;">{{ number_format($data['netRevenue'], 2) }}</span>
    </div>

    <div class="v-spacer"></div>

    {{-- EXPENDITURE --}}
    <div class="v-section-head" style="background:#fff1f2;color:#991b1b;">
        <span><i class="bi bi-arrow-down-circle me-1"></i> EXPENDITURE</span>
        <span>Amount (&#8377;)</span>
    </div>
    @forelse($data['expenses'] as $row)
    <div class="v-row">
        <span>
            <span class="acc-code">{{ $row->account->code }}</span>
            {{ $row->account->name }}
        </span>
        <span style="color:#dc2626;font-weight:600;">{{ number_format($row->net, 2) }}</span>
    </div>
    @empty
    <div class="v-row"><span style="color:#9ca3af;">No expense entries found.</span><span>0.00</span></div>
    @endforelse
    <div class="v-subtotal">
        <span>Total Expenditure (B)</span>
        <span style="color:#dc2626;">{{ number_format($data['netExpenses'], 2) }}</span>
    </div>

    <div class="v-spacer"></div>

    {{-- NET PROFIT / LOSS --}}
    <div class="v-net-profit"
         style="background:{{ $isProfit ? 'linear-gradient(135deg,#ecfdf5,#d1fae5)' : 'linear-gradient(135deg,#fff1f2,#fee2e2)' }};">
        <span style="color:{{ $isProfit ? '#065f46' : '#991b1b' }};">
            <i class="bi bi-{{ $isProfit ? 'graph-up-arrow' : 'graph-down-arrow' }} me-2"></i>
            NET {{ $isProfit ? 'PROFIT' : 'LOSS' }} (A &minus; B)
        </span>
        <span style="font-size:1.15rem;color:{{ $isProfit ? '#059669' : '#dc2626' }};">
            {{ $isProfit ? '' : '(' }}&#8377;{{ number_format(abs($netIncome), 2) }}{{ $isProfit ? '' : ')' }}
        </span>
    </div>

    {{-- Revenue bar --}}
    <div style="padding:16px 24px 20px;">
        <div style="display:flex;height:8px;border-radius:20px;overflow:hidden;background:#e5e7eb;">
            <div style="width:{{ $pct }}%;background:#10b981;"></div>
            <div style="width:{{ 100 - $pct }}%;background:#ef4444;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:.75rem;font-weight:600;">
            <span style="color:#059669;"><i class="bi bi-arrow-up-circle me-1"></i>Income: &#8377;{{ number_format($data['netRevenue'], 2) }}</span>
            <span style="color:#6b7280;">Margin: {{ $margin }}%</span>
            <span style="color:#dc2626;"><i class="bi bi-arrow-down-circle me-1"></i>Expenditure: &#8377;{{ number_format($data['netExpenses'], 2) }}</span>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- HORIZONTAL FORMAT                                           --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@else

<div class="stmt-wrap">
    <div class="stmt-header">
        <div class="firm-name">AS DAIRY FARM</div>
        <div class="stmt-title">PROFIT &amp; LOSS STATEMENT (HORIZONTAL FORMAT)</div>
        <div class="stmt-date">For the Period: {{ $dateRange }} &nbsp;|&nbsp; {{ $periodLabel }}</div>
    </div>

    <div class="h-table-wrap">
        {{-- LEFT SIDE: Expenditure (Dr side) --}}
        <div class="h-side">
            <div class="h-side-head" style="background:#fff1f2;color:#991b1b;">
                <span>Dr &nbsp;|&nbsp; EXPENDITURE</span>
                <span>Amount (&#8377;)</span>
            </div>

            <div class="h-cat-head"><i class="bi bi-arrow-down-circle me-1"></i>Expenses</div>
            @forelse($data['expenses'] as $row)
            <div class="h-row">
                <span>
                    <span class="acc-code">{{ $row->account->code }}</span>
                    {{ $row->account->name }}
                </span>
                <span style="color:#dc2626;font-weight:600;">{{ number_format($row->net, 2) }}</span>
            </div>
            @empty
            <div class="h-row"><span style="color:#9ca3af;">No expense entries.</span><span>0.00</span></div>
            @endforelse

            @if($isProfit)
            <div class="h-net-row" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);">
                <span style="color:#065f46;"><i class="bi bi-graph-up-arrow me-1"></i>Net Profit transferred to Capital</span>
                <span style="color:#059669;">{{ number_format($netIncome, 2) }}</span>
            </div>
            @endif

            <div class="h-grandtotal" style="background:#f8fafc;">
                <span>Total (Expenditure {{ $isProfit ? '+ Net Profit' : '' }})</span>
                <span style="color:#1e293b;">{{ number_format($data['netRevenue'], 2) }}</span>
            </div>
        </div>

        {{-- RIGHT SIDE: Income (Cr side) --}}
        <div class="h-side">
            <div class="h-side-head" style="background:#ecfdf5;color:#065f46;">
                <span>Cr &nbsp;|&nbsp; INCOME</span>
                <span>Amount (&#8377;)</span>
            </div>

            <div class="h-cat-head"><i class="bi bi-arrow-up-circle me-1"></i>Revenue</div>
            @forelse($data['revenue'] as $row)
            <div class="h-row">
                <span>
                    <span class="acc-code">{{ $row->account->code }}</span>
                    {{ $row->account->name }}
                </span>
                <span style="color:#059669;font-weight:600;">{{ number_format($row->net, 2) }}</span>
            </div>
            @empty
            <div class="h-row"><span style="color:#9ca3af;">No revenue entries.</span><span>0.00</span></div>
            @endforelse

            @if(!$isProfit)
            <div class="h-net-row" style="background:linear-gradient(135deg,#fff1f2,#fee2e2);">
                <span style="color:#991b1b;"><i class="bi bi-graph-down-arrow me-1"></i>Net Loss (transferred to Capital)</span>
                <span style="color:#dc2626;">{{ number_format(abs($netIncome), 2) }}</span>
            </div>
            @endif

            <div class="h-grandtotal" style="background:#f8fafc;">
                <span>Total (Income {{ !$isProfit ? '+ Net Loss' : '' }})</span>
                <span style="color:#1e293b;">{{ number_format($data['netRevenue'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Net result bar --}}
    <div style="padding:14px 24px 18px;border-top:1px solid #e2e8f0;background:#f8fafc;">
        <div style="display:flex;height:7px;border-radius:20px;overflow:hidden;background:#e5e7eb;">
            <div style="width:{{ $pct }}%;background:#10b981;"></div>
            <div style="width:{{ 100 - $pct }}%;background:#ef4444;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:.75rem;font-weight:600;">
            <span style="color:#059669;"><i class="bi bi-arrow-up-circle me-1"></i>Income: &#8377;{{ number_format($data['netRevenue'], 2) }}</span>
            <span style="color:{{ $isProfit ? '#059669' : '#dc2626' }};font-weight:800;">
                Net {{ $isProfit ? 'Profit' : 'Loss' }}: &#8377;{{ number_format(abs($netIncome), 2) }} &nbsp;|&nbsp; Margin: {{ $margin }}%
            </span>
            <span style="color:#dc2626;"><i class="bi bi-arrow-down-circle me-1"></i>Expenditure: &#8377;{{ number_format($data['netExpenses'], 2) }}</span>
        </div>
    </div>
</div>
@endif

@endsection