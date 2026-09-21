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
    // Split expenses: sub_type 'cost_of_sales' = COGS, everything else = Operating Expenses
    $cogs         = $data['expenses']->filter(fn($r) => ($r->account->sub_type ?? '') === 'cost_of_sales');
    $opex         = $data['expenses']->filter(fn($r) => ($r->account->sub_type ?? '') !== 'cost_of_sales');
    $netCogs      = $cogs->sum('net');
    $netOpex      = $opex->sum('net');

    // Split revenue: billed (sales / other_income) vs unbilled (unbilled_sales = account 4050)
    $billedRevData   = $data['billedRevenue']   ?? $data['revenue']->filter(fn($r) => ($r->account->sub_type ?? '') !== 'unbilled_sales');
    $unbilledRevData = $data['unbilledRevenue'] ?? $data['revenue']->filter(fn($r) => ($r->account->sub_type ?? '') === 'unbilled_sales');
    $netBilledRev    = $billedRevData->sum('net');
    $netUnbilledRev  = $unbilledRevData->sum('net');

    $grossProfit  = $data['netRevenue'] - $netCogs;
    $isGrossProfit= $grossProfit >= 0;
    $netIncome    = $data['netIncome'];
    $isProfit     = $netIncome >= 0;
    $grossMargin  = $data['netRevenue'] > 0 ? round(($grossProfit / $data['netRevenue']) * 100, 1) : 0;
    $margin       = $data['netRevenue'] > 0 ? round(($netIncome / $data['netRevenue']) * 100, 1) : 0;
    $total        = $data['netRevenue'] + $data['netExpenses'];
    $pct          = $total > 0 ? min(100, ($data['netRevenue'] / $total) * 100) : 0;
    $fmt          = request('view', 'vertical');
    $periodLabel  = $selectedPeriod ? $selectedPeriod->name : 'All Periods';
    $dateRange    = ($dateFrom && $dateTo) ? "$dateFrom — $dateTo" : ($selectedPeriod ? $selectedPeriod->start_date->format('d M Y') . ' — ' . $selectedPeriod->end_date->format('d M Y') : now()->format('d M Y'));
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
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $isGrossProfit ? '#0891b2' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Gross Profit</div>
            <div style="font-size:1.45rem;font-weight:800;color:{{ $isGrossProfit ? '#0e7490' : '#dc2626' }};margin-top:5px;line-height:1.2;">
                &#8377;{{ number_format($grossProfit, 2) }}
            </div>
            <div style="font-size:.72rem;color:#6b7280;margin-top:3px;font-weight:600;">Gross Margin: {{ $grossMargin }}%</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $isProfit ? '#10b981' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Net {{ $isProfit ? 'Profit' : 'Loss' }}</div>
            <div style="font-size:1.45rem;font-weight:800;color:{{ $isProfit ? '#059669' : '#dc2626' }};margin-top:5px;line-height:1.2;">
                {{ $isProfit ? '+' : '' }}&#8377;{{ number_format($netIncome, 2) }}
            </div>
            <div style="font-size:.72rem;color:{{ $isProfit ? '#10b981' : '#ef4444' }};margin-top:3px;font-weight:600;">Revenue − All Expenses</div>
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

    {{-- ── A) REVENUE ──────────────────────────────── --}}
    <div class="v-section-head" style="background:#ecfdf5;color:#065f46;">
        <span><i class="bi bi-arrow-up-circle me-1"></i> REVENUE (INCOME)</span>
        <span>Amount (&#8377;)</span>
    </div>

    {{-- A1: Billed Revenue --}}
    <div style="padding:6px 24px 3px 28px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#059669;background:#f0fdf4;display:flex;justify-content:space-between;">
        <span><span style="display:inline-block;background:#d1fae5;color:#065f46;padding:1px 8px;border-radius:4px;font-size:.68rem;font-weight:700;margin-right:6px;">BILLED</span>Invoiced Sales (Paid / Pending / Partial)</span>
        <span>{{ number_format($netBilledRev, 2) }}</span>
    </div>
    @forelse($billedRevData as $row)
    <div class="v-row" style="padding-left:44px;">
        <span>
            <span class="acc-code">{{ $row->account->code }}</span>
            {{ $row->account->name }}
        </span>
        <span style="color:#059669;font-weight:600;">{{ number_format($row->net, 2) }}</span>
    </div>
    @empty
    <div class="v-row" style="padding-left:44px;"><span style="color:#9ca3af;">No billed revenue entries.</span><span>0.00</span></div>
    @endforelse

    {{-- A2: Unbilled Revenue (always shown so operators can see 0 if JEs are missing) --}}
    <div style="padding:6px 24px 3px 28px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#d97706;background:#fffbeb;display:flex;justify-content:space-between;">
        <span><span style="display:inline-block;background:#fef3c7;color:#92400e;padding:1px 8px;border-radius:4px;font-size:.68rem;font-weight:700;margin-right:6px;">UNBILLED</span>Accrued / Not Yet Invoiced</span>
        <span>{{ number_format($netUnbilledRev, 2) }}</span>
    </div>
    @forelse($unbilledRevData as $row)
    <div class="v-row" style="padding-left:44px;">
        <span>
            <span class="acc-code">{{ $row->account->code }}</span>
            {{ $row->account->name }}
        </span>
        <span style="color:#d97706;font-weight:600;">{{ number_format($row->net, 2) }}</span>
    </div>
    @empty
    <div class="v-row" style="padding-left:44px;"><span style="color:#9ca3af;">No unbilled revenue entries — run <strong>Sync Sales JEs</strong> if invoices exist.</span><span>0.00</span></div>
    @endforelse

    <div class="v-subtotal">
        <span>Total Revenue (A)</span>
        <span style="color:#059669;">{{ number_format($data['netRevenue'], 2) }}</span>
    </div>

    <div class="v-spacer"></div>

    {{-- ── B) COST OF GOODS SOLD ───────────────────── --}}
    <div class="v-section-head" style="background:#fff7ed;color:#92400e;">
        <span><i class="bi bi-box-seam me-1"></i> COST OF GOODS SOLD (COGS)</span>
        <span>Amount (&#8377;)</span>
    </div>
    @forelse($cogs as $row)
    <div class="v-row">
        <span>
            <span class="acc-code">{{ $row->account->code }}</span>
            {{ $row->account->name }}
        </span>
        <span style="color:#d97706;font-weight:600;">{{ number_format($row->net, 2) }}</span>
    </div>
    @empty
    <div class="v-row"><span style="color:#9ca3af;">No COGS entries (sub_type: cost_of_sales).</span><span>0.00</span></div>
    @endforelse
    <div class="v-subtotal">
        <span>Total COGS (B)</span>
        <span style="color:#d97706;">{{ number_format($netCogs, 2) }}</span>
    </div>

    <div class="v-spacer" style="height:0;"></div>

    {{-- ── GROSS PROFIT ────────────────────────────── --}}
    <div class="v-net-profit" style="background:{{ $isGrossProfit ? 'linear-gradient(135deg,#f0f9ff,#e0f2fe)' : 'linear-gradient(135deg,#fff1f2,#fee2e2)' }};border-top:2px solid {{ $isGrossProfit ? '#0891b2' : '#ef4444' }};">
        <span style="color:{{ $isGrossProfit ? '#0c4a6e' : '#991b1b' }};font-size:.88rem;">
            <i class="bi bi-calculator me-1"></i>GROSS PROFIT (A &minus; B)
        </span>
        <span style="font-size:1rem;color:{{ $isGrossProfit ? '#0891b2' : '#dc2626' }};">
            &#8377;{{ number_format($grossProfit, 2) }}
            <small style="font-size:.68rem;font-weight:600;opacity:.75;margin-left:6px;">{{ $grossMargin }}% margin</small>
        </span>
    </div>

    <div class="v-spacer"></div>

    {{-- ── C) OPERATING EXPENSES ───────────────────── --}}
    <div class="v-section-head" style="background:#fff1f2;color:#991b1b;">
        <span><i class="bi bi-arrow-down-circle me-1"></i> OPERATING EXPENSES</span>
        <span>Amount (&#8377;)</span>
    </div>
    @forelse($opex as $row)
    <div class="v-row">
        <span>
            <span class="acc-code">{{ $row->account->code }}</span>
            {{ $row->account->name }}
        </span>
        <span style="color:#dc2626;font-weight:600;">{{ number_format($row->net, 2) }}</span>
    </div>
    @empty
    <div class="v-row"><span style="color:#9ca3af;">No operating expense entries.</span><span>0.00</span></div>
    @endforelse
    <div class="v-subtotal">
        <span>Total Operating Expenses (C)</span>
        <span style="color:#dc2626;">{{ number_format($netOpex, 2) }}</span>
    </div>

    <div class="v-spacer"></div>

    {{-- ── NET PROFIT / LOSS ───────────────────────── --}}
    <div class="v-net-profit"
         style="background:{{ $isProfit ? 'linear-gradient(135deg,#ecfdf5,#d1fae5)' : 'linear-gradient(135deg,#fff1f2,#fee2e2)' }};">
        <span style="color:{{ $isProfit ? '#065f46' : '#991b1b' }};">
            <i class="bi bi-{{ $isProfit ? 'graph-up-arrow' : 'graph-down-arrow' }} me-2"></i>
            NET {{ $isProfit ? 'PROFIT' : 'LOSS' }} (Gross Profit &minus; C)
        </span>
        <span style="font-size:1.15rem;color:{{ $isProfit ? '#059669' : '#dc2626' }};">
            {{ $isProfit ? '' : '(' }}&#8377;{{ number_format(abs($netIncome), 2) }}{{ $isProfit ? '' : ')' }}
            <small style="font-size:.68rem;font-weight:600;opacity:.75;margin-left:6px;">{{ $margin }}% net margin</small>
        </span>
    </div>

    {{-- Revenue bar --}}
    <div style="padding:16px 24px 20px;">
        <div style="display:flex;height:8px;border-radius:20px;overflow:hidden;background:#e5e7eb;">
            <div style="width:{{ $pct }}%;background:#10b981;"></div>
            <div style="width:{{ 100 - $pct }}%;background:#ef4444;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:.75rem;font-weight:600;">
            <span style="color:#059669;"><i class="bi bi-arrow-up-circle me-1"></i>Revenue: &#8377;{{ number_format($data['netRevenue'], 2) }}</span>
            <span style="color:#0891b2;">Gross Profit: &#8377;{{ number_format($grossProfit, 2) }}</span>
            <span style="color:{{ $isProfit ? '#059669' : '#dc2626' }};">Net {{ $isProfit ? 'Profit' : 'Loss' }}: &#8377;{{ number_format(abs($netIncome), 2) }}</span>
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

            <div class="h-cat-head" style="background:#fff7ed;color:#92400e;"><i class="bi bi-box-seam me-1"></i>Cost of Goods Sold</div>
            @forelse($cogs as $row)
            <div class="h-row">
                <span><span class="acc-code">{{ $row->account->code }}</span>{{ $row->account->name }}</span>
                <span style="color:#d97706;font-weight:600;">{{ number_format($row->net, 2) }}</span>
            </div>
            @empty
            <div class="h-row"><span style="color:#9ca3af;">No COGS entries.</span><span>0.00</span></div>
            @endforelse
            <div class="h-subtotal" style="background:#fffbeb;">
                <span style="color:#92400e;">Total COGS</span>
                <span style="color:#d97706;">{{ number_format($netCogs, 2) }}</span>
            </div>

            <div class="h-cat-head"><i class="bi bi-arrow-down-circle me-1"></i>Operating Expenses</div>
            @forelse($opex as $row)
            <div class="h-row">
                <span><span class="acc-code">{{ $row->account->code }}</span>{{ $row->account->name }}</span>
                <span style="color:#dc2626;font-weight:600;">{{ number_format($row->net, 2) }}</span>
            </div>
            @empty
            <div class="h-row"><span style="color:#9ca3af;">No operating expense entries.</span><span>0.00</span></div>
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

            <div class="h-cat-head"><i class="bi bi-arrow-up-circle me-1"></i>Revenue — Billed</div>
            @forelse($billedRevData as $row)
            <div class="h-row">
                <span><span class="acc-code">{{ $row->account->code }}</span>{{ $row->account->name }}</span>
                <span style="color:#059669;font-weight:600;">{{ number_format($row->net, 2) }}</span>
            </div>
            @empty
            <div class="h-row"><span style="color:#9ca3af;">No billed revenue entries.</span><span>0.00</span></div>
            @endforelse
            <div class="h-cat-head" style="background:#fffbeb;color:#92400e;"><i class="bi bi-hourglass-split me-1"></i>Revenue — Unbilled</div>
            @forelse($unbilledRevData as $row)
            <div class="h-row" style="background:#fffde7;">
                <span><span class="acc-code">{{ $row->account->code }}</span>{{ $row->account->name }}</span>
                <span style="color:#d97706;font-weight:600;">{{ number_format($row->net, 2) }}</span>
            </div>
            @empty
            <div class="h-row" style="background:#fffde7;"><span style="color:#9ca3af;">No unbilled entries.</span><span>0.00</span></div>
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

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MANAGEMENT SUMMARY: Billed vs Unbilled Sales Breakdown      --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@php
    $totalSales    = ($billedSales ?? 0) + ($unbilledSales ?? 0);
    $mgmtNetProfit = $totalSales - ($totalPurchase ?? 0);
    $isNetProfit   = $mgmtNetProfit >= 0;
@endphp

<div class="stmt-wrap" style="margin-top:28px;">
    <div class="stmt-header" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-bottom-color:#2563eb;">
        <div class="firm-name">MANAGEMENT SUMMARY</div>
        <div class="stmt-title" style="color:#1d4ed8;">Sales Breakdown — Billed &amp; Unbilled</div>
        <div class="stmt-date">{{ $periodLabel }} &nbsp;|&nbsp; {{ $dateRange }}</div>
    </div>

    {{-- SALES section --}}
    <div class="v-section-head" style="background:#eff6ff;color:#1e40af;">
        <span><i class="bi bi-receipt me-1"></i> SALES</span>
        <span>Amount (&#8377;)</span>
    </div>

    <div class="v-row">
        <span>
            <span style="display:inline-block;background:#d1fae5;color:#065f46;padding:1px 8px;border-radius:4px;font-size:.7rem;font-weight:700;margin-right:8px;">BILLED</span>
            Invoiced Sales
            <small style="color:#6b7280;font-size:.72rem;"> — invoice issued (Paid + Pending + Partial)</small>
        </span>
        <span style="color:#059669;font-weight:700;">{{ number_format($billedSales ?? 0, 2) }}</span>
    </div>

    <div class="v-row">
        <span>
            <span style="display:inline-block;background:#fef3c7;color:#92400e;padding:1px 8px;border-radius:4px;font-size:.7rem;font-weight:700;margin-right:8px;">UNBILLED</span>
            Accrued / Unbilled Sales
            <small style="color:#6b7280;font-size:.72rem;"> — goods delivered, invoice not yet raised</small>
        </span>
        <span style="color:#d97706;font-weight:700;">{{ number_format($unbilledSales ?? 0, 2) }}</span>
    </div>

    <div class="v-subtotal">
        <span>Total Sales (A)</span>
        <span style="color:#1d4ed8;">{{ number_format($totalSales, 2) }}</span>
    </div>

    <div class="v-spacer"></div>

    {{-- PURCHASE section --}}
    <div class="v-section-head" style="background:#fff1f2;color:#991b1b;">
        <span><i class="bi bi-cart me-1"></i> PURCHASE</span>
        <span>Amount (&#8377;)</span>
    </div>

    <div class="v-row">
        <span>Total Purchase</span>
        <span style="color:#dc2626;font-weight:700;">{{ number_format($totalPurchase ?? 0, 2) }}</span>
    </div>

    <div class="v-subtotal">
        <span>Total Purchase (B)</span>
        <span style="color:#dc2626;">{{ number_format($totalPurchase ?? 0, 2) }}</span>
    </div>

    <div class="v-spacer"></div>

    {{-- NET PROFIT --}}
    <div class="v-net-profit"
         style="background:{{ $isNetProfit ? 'linear-gradient(135deg,#ecfdf5,#d1fae5)' : 'linear-gradient(135deg,#fff1f2,#fee2e2)' }};">
        <span style="color:{{ $isNetProfit ? '#065f46' : '#991b1b' }};">
            <i class="bi bi-{{ $isNetProfit ? 'graph-up-arrow' : 'graph-down-arrow' }} me-2"></i>
            NET {{ $isNetProfit ? 'PROFIT' : 'LOSS' }} (A &minus; B)
        </span>
        <span style="font-size:1.15rem;color:{{ $isNetProfit ? '#059669' : '#dc2626' }};">
            {{ $isNetProfit ? '' : '(' }}&#8377;{{ number_format(abs($mgmtNetProfit), 2) }}{{ $isNetProfit ? '' : ')' }}
        </span>
    </div>

    <div style="padding:12px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:.78rem;color:#6b7280;">
        <i class="bi bi-info-circle me-1"></i>
        <strong>Billed</strong> = invoice issued (Paid / Pending / Partial) &nbsp;&middot;&nbsp;
        <strong>Unbilled</strong> = goods delivered, invoice not yet raised &nbsp;&middot;&nbsp;
        <strong>Net Profit</strong> = Total Sales &minus; Total Purchase
    </div>
</div>

@endsection