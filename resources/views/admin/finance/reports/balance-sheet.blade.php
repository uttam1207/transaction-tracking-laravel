@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.coa.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Balance Sheet</li>
@endsection

@push('styles')
<style>
.fmt-toggle { display:inline-flex;border:1.5px solid #e2e8f0;border-radius:10px;overflow:hidden; }
.fmt-toggle a {
    padding:6px 18px;font-size:.78rem;font-weight:700;text-decoration:none;
    color:#6b7280;background:#fff;transition:all .15s;
}
.fmt-toggle a.active { background:#1d4ed8;color:#fff; }

/* ── Statement Shell ── */
.bs-shell {
    background:#fff;border-radius:14px;
    box-shadow:0 2px 12px rgba(0,0,0,.08);
    overflow:hidden;margin-bottom:28px;
}
.bs-co-header {
    text-align:center;padding:20px 28px 16px;
    border-bottom:1px solid #e5e7eb;
}
.bs-co-header .co-name  { font-size:1.15rem;font-weight:800;color:#1e293b; }
.bs-co-header .co-title { font-size:.95rem;font-weight:600;color:#374151;margin-top:3px; }
.bs-co-header .co-sub   { font-size:.78rem;color:#6b7280;margin-top:2px; }

/* ── Horizontal T-Table ── */
.bs-h-table { display:flex;border-top:1px solid #e5e7eb; }
.bs-h-col   { flex:1;min-width:0; }
.bs-h-col + .bs-h-col { border-left:2px solid #d1d5db; }

.bs-col-head {
    padding:10px 18px;font-size:.78rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.07em;color:#1e293b;
    background:#f3f4f6;border-bottom:2px solid #d1d5db;
}

/* Section headers (LIABILITIES, EQUITIES, CURRENT ASSETS…) */
.bs-sec-head {
    padding:11px 18px 5px;font-size:.76rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.05em;color:#1e293b;
    margin-top:6px;
}

/* Sub-section headers (• CURRENT LIABILITIES) */
.bs-sub-head {
    padding:6px 18px 4px 26px;font-size:.72rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.04em;color:#374151;
    display:flex;align-items:center;gap:6px;
}
.bs-sub-head::before { content:'•';color:#1d4ed8;font-size:.85rem; }

/* Account rows */
.bs-row {
    display:flex;justify-content:space-between;align-items:center;
    padding:5px 18px 5px 34px;font-size:.81rem;color:#374151;
    border-bottom:1px solid #f3f4f6;
}
.bs-row:last-child { border-bottom:none; }
.bs-row .amt { font-weight:500;min-width:90px;text-align:right;white-space:nowrap; }

/* Sub-total rows (TOTAL CURRENT LIABILITIES, TOTAL CURRENT ASSETS…) */
.bs-subtotal {
    display:flex;justify-content:space-between;align-items:center;
    padding:6px 18px 6px 34px;font-size:.8rem;font-weight:700;
    border-top:1px solid #d1d5db;border-bottom:1px solid #d1d5db;
    background:#f9fafb;margin-bottom:4px;
}
.bs-subtotal .amt { min-width:90px;text-align:right; }

/* Section total (TOTAL LIABILITIES, TOTAL EQUITIES) */
.bs-sec-total {
    display:flex;justify-content:space-between;align-items:center;
    padding:8px 18px;font-size:.82rem;font-weight:700;
    border-top:2px solid #6b7280;border-bottom:1px solid #d1d5db;
    background:#f9fafb;
}
.bs-sec-total .amt { min-width:90px;text-align:right; }

/* Footer grand total row */
.bs-footer {
    display:flex;border-top:2px solid #1e293b;
}
.bs-footer-cell {
    flex:1;padding:12px 18px;font-size:.88rem;font-weight:800;color:#1e293b;
    display:flex;justify-content:space-between;align-items:center;
    background:#f3f4f6;
}
.bs-footer-cell + .bs-footer-cell { border-left:2px solid #d1d5db; }
.bs-footer-cell .amt { min-width:110px;text-align:right; }

/* ── Vertical format ── */
.bs-v-wrap { padding:0; }
.bs-v-row  { display:flex;justify-content:space-between;align-items:center;
    padding:5px 24px 5px 40px;font-size:.81rem;color:#374151;border-bottom:1px solid #f3f4f6; }
.bs-v-row .amt { min-width:100px;text-align:right;font-weight:500; }
.bs-v-sec-head { padding:11px 24px 5px;font-size:.76rem;font-weight:800;
    text-transform:uppercase;letter-spacing:.05em;color:#1e293b; }
.bs-v-sub-head { padding:6px 24px 4px 32px;font-size:.72rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.04em;color:#374151; }
.bs-v-subtotal { display:flex;justify-content:space-between;padding:6px 24px 6px 40px;
    font-size:.8rem;font-weight:700;border-top:1px solid #d1d5db;border-bottom:1px solid #d1d5db;
    background:#f9fafb; }
.bs-v-subtotal .amt { min-width:100px;text-align:right; }
.bs-v-sec-total { display:flex;justify-content:space-between;padding:8px 24px;
    font-size:.82rem;font-weight:700;border-top:2px solid #6b7280;border-bottom:1px solid #d1d5db;
    background:#f9fafb; }
.bs-v-sec-total .amt { min-width:100px;text-align:right; }
.bs-v-grand { display:flex;justify-content:space-between;padding:12px 24px;
    font-size:.9rem;font-weight:800;color:#fff;background:#1d4ed8; }
.bs-v-grand .amt { min-width:100px;text-align:right; }
.bs-v-spacer { height:14px; }
.bs-v-divider { height:2px;background:#e5e7eb;margin:8px 0; }

@media print {
    .page-hero, .row.g-3.mb-4, .fmt-toggle, .btn, form { display:none!important; }
    .bs-shell { box-shadow:none;border:1px solid #ccc; }
}
</style>
@endpush

@section('content')

@php
    $difference = round(abs($data['totalAssets'] - ($data['totalLiabilities'] + $data['totalEquity'])), 2);
    $isBalanced = $difference === 0.0;
    $totalLE    = $data['totalLiabilities'] + $data['totalEquity'];
    $fmt        = request('view', 'horizontal'); // default to horizontal to match PDF

    // Group assets by sub_type, fallback to code-based inference
    $assetGroups = $data['assets']->groupBy(function($r) {
        $sub  = strtolower(trim($r->account->sub_type ?? ''));
        $code = (int) preg_replace('/\D/', '', $r->account->code);
        if ($sub) {
            if (str_contains($sub, 'current') || str_contains($sub, 'receiv') || str_contains($sub, 'bank') || str_contains($sub, 'cash') || str_contains($sub, 'inventory') || str_contains($sub, 'prepaid'))
                return 'Current Assets';
            if (str_contains($sub, 'fixed') || str_contains($sub, 'property') || str_contains($sub, 'plant') || str_contains($sub, 'equip') || str_contains($sub, 'tangible'))
                return 'Fixed Assets';
            return 'Other Assets';
        }
        // Code-based: 1000–1499 → Current, 1500–1999 → Fixed, rest → Other
        if ($code >= 1000 && $code < 1500) return 'Current Assets';
        if ($code >= 1500 && $code < 2000) return 'Fixed Assets';
        return 'Other Assets';
    });
    // Canonical order
    $assetGroupOrder = ['Current Assets' => 0, 'Fixed Assets' => 1, 'Other Assets' => 2];
    $assetGroups = $assetGroups->sortBy(fn($g, $k) => $assetGroupOrder[$k] ?? 99);

    // Group liabilities
    $liabGroups = $data['liabilities']->groupBy(function($r) {
        $sub  = strtolower(trim($r->account->sub_type ?? ''));
        $code = (int) preg_replace('/\D/', '', $r->account->code);
        if ($sub) {
            if (str_contains($sub, 'long') || str_contains($sub, 'term') || str_contains($sub, 'non_current') || str_contains($sub, 'noncurrent'))
                return 'Long-Term Liabilities';
        }
        return 'Current Liabilities'; // default
    });

    $periodLabel = $selectedPeriod ? $selectedPeriod->name : 'All Periods';
    $asOfDate    = $selectedPeriod ? $selectedPeriod->end_date->format('d/m/Y') : now()->format('d/m/Y');
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Balance Sheet</h4>
            <p>Assets, liabilities &amp; equity {{ $selectedPeriod ? 'for ' . $selectedPeriod->name : 'all time' }}</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @if ($data['totalAssets'] > 0 || $data['totalLiabilities'] > 0)
            <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:20px;font-size:.78rem;font-weight:700;
                background:{{ $isBalanced ? '#d1fae5' : '#fee2e2' }};color:{{ $isBalanced ? '#065f46' : '#991b1b' }};">
                <i class="bi bi-{{ $isBalanced ? 'check-circle-fill' : 'exclamation-triangle-fill' }}"></i>
                {{ $isBalanced ? 'Balanced' : 'Unbalanced' }}
            </span>
            @endif
            <div class="fmt-toggle">
                <a href="?{{ http_build_query(array_merge(request()->all(), ['view' => 'horizontal'])) }}"
                   class="{{ $fmt === 'horizontal' ? 'active' : '' }}">
                    <i class="bi bi-layout-split me-1"></i>Horizontal
                </a>
                <a href="?{{ http_build_query(array_merge(request()->all(), ['view' => 'vertical'])) }}"
                   class="{{ $fmt === 'vertical' ? 'active' : '' }}">
                    <i class="bi bi-list-ul me-1"></i>Vertical
                </a>
            </div>
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-success btn-sm">P&amp;L</a>
            <a href="{{ route('admin.export.balance-sheet', 'excel') }}" class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.export.balance-sheet', 'pdf') }}" class="btn btn-sm btn-outline-light px-3">
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
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #3b82f6;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Assets</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;">&#8377;{{ number_format($data['totalAssets'], 2) }}</div>
            <div style="font-size:.72rem;color:#3b82f6;margin-top:3px;font-weight:600;">{{ $data['assets']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #ef4444;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Liabilities</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;">&#8377;{{ number_format($data['totalLiabilities'], 2) }}</div>
            <div style="font-size:.72rem;color:#ef4444;margin-top:3px;font-weight:600;">{{ $data['liabilities']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #f59e0b;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Equity</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;">&#8377;{{ number_format($data['totalEquity'], 2) }}</div>
            <div style="font-size:.72rem;color:#f59e0b;margin-top:3px;font-weight:600;">{{ $data['equity']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $isBalanced ? '#10b981' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">L + E Total</div>
            <div style="font-size:1.4rem;font-weight:800;color:{{ $isBalanced ? '#065f46' : '#991b1b' }};margin-top:5px;">&#8377;{{ number_format($totalLE, 2) }}</div>
            <div style="font-size:.72rem;color:{{ $isBalanced ? '#10b981' : '#ef4444' }};margin-top:3px;font-weight:600;">
                {{ $isBalanced ? 'Assets = L + E ✓' : 'Diff: ' . number_format($difference, 2) }}
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#fff;border-radius:14px;padding:16px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:22px;">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="view" value="{{ $fmt }}">
        <div class="col-md-4">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">As of Period</label>
            <select name="period_id" class="form-select form-select-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                <option value="">— All Time —</option>
                @foreach ($periods as $p)
                    <option value="{{ $p->id }}" {{ $p->id == $periodId ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Generate</button>
            <a href="{{ route('admin.finance.reports.balance-sheet') }}?view={{ $fmt }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- HORIZONTAL FORMAT (matches PDF exactly)                          --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@if($fmt === 'horizontal')

<div class="bs-shell">
    {{-- Company header --}}
    <div class="bs-co-header">
        <div class="co-name">AS DAIRY</div>
        <div class="co-title">Horizontal Balance Sheet</div>
        <div class="co-sub">Basis: Accrual &nbsp;&nbsp;|&nbsp;&nbsp; As of {{ $asOfDate }} &nbsp;&nbsp;|&nbsp;&nbsp; Period: {{ $periodLabel }}</div>
    </div>

    {{-- Two-column table --}}
    <div class="bs-h-table">

        {{-- ── LEFT: Liabilities & Equities ── --}}
        <div class="bs-h-col">
            <div class="bs-col-head">Liabilities &amp; Equities</div>

            {{-- LIABILITIES section --}}
            <div class="bs-sec-head">LIABILITIES</div>

            @forelse($liabGroups as $groupName => $rows)
            {{-- Sub-section header (e.g. CURRENT LIABILITIES) --}}
            <div class="bs-sub-head">{{ strtoupper($groupName) }}</div>

            @foreach($rows as $row)
            <div class="bs-row">
                <span>{{ $row->account->name }}</span>
                <span class="amt">{{ number_format($row->net, 2) }}</span>
            </div>
            @endforeach

            <div class="bs-subtotal">
                <span>TOTAL {{ strtoupper($groupName) }}</span>
                <span class="amt">{{ number_format($rows->sum('net'), 2) }}</span>
            </div>
            @empty
            <div class="bs-row"><span style="color:#9ca3af;">No liabilities recorded.</span><span class="amt">0.00</span></div>
            @endforelse

            @if($data['totalLiabilities'] > 0)
            <div class="bs-sec-total">
                <span>TOTAL LIABILITIES</span>
                <span class="amt">{{ number_format($data['totalLiabilities'], 2) }}</span>
            </div>
            @endif

            <div style="height:16px;"></div>

            {{-- EQUITIES section --}}
            <div class="bs-sec-head">EQUITIES</div>

            @forelse($data['equity'] as $row)
            <div class="bs-row">
                <span>{{ $row->account->name }}</span>
                <span class="amt">{{ number_format($row->net, 2) }}</span>
            </div>
            @empty
            <div class="bs-row"><span style="color:#9ca3af;">No equity accounts.</span><span class="amt">0.00</span></div>
            @endforelse

            @if(($data['netIncome'] ?? 0) != 0)
            <div class="bs-row" style="background:#f0fdf4;">
                <span>Current Year Earnings</span>
                <span class="amt" style="color:{{ ($data['netIncome'] ?? 0) >= 0 ? '#059669' : '#dc2626' }};">
                    {{ number_format($data['netIncome'] ?? 0, 2) }}
                </span>
            </div>
            @endif

            <div class="bs-sec-total">
                <span>TOTAL EQUITIES</span>
                <span class="amt">{{ number_format($data['totalEquity'], 2) }}</span>
            </div>

            <div style="height:20px;"></div>
        </div>

        {{-- ── RIGHT: Assets ── --}}
        <div class="bs-h-col">
            <div class="bs-col-head">Assets</div>

            @forelse($assetGroups as $groupName => $rows)
            {{-- Asset section header (CURRENT ASSETS, FIXED ASSETS, OTHER ASSETS) --}}
            <div class="bs-sec-head">{{ strtoupper($groupName) }}</div>

            @foreach($rows as $row)
            <div class="bs-row">
                <span>{{ $row->account->name }}</span>
                <span class="amt">{{ number_format($row->net, 2) }}</span>
            </div>
            @endforeach

            <div class="bs-subtotal">
                <span>TOTAL {{ strtoupper($groupName) }}</span>
                <span class="amt">{{ number_format($rows->sum('net'), 2) }}</span>
            </div>

            <div style="height:12px;"></div>
            @empty
            <div class="bs-row"><span style="color:#9ca3af;">No assets recorded.</span><span class="amt">0.00</span></div>
            @endforelse

            <div style="height:4px;"></div>
        </div>
    </div>

    {{-- Grand total footer --}}
    <div class="bs-footer">
        <div class="bs-footer-cell">
            <span>TOTAL LIABILITIES &amp; EQUITIES</span>
            <span class="amt">{{ number_format($totalLE, 2) }}</span>
        </div>
        <div class="bs-footer-cell">
            <span>TOTAL ASSETS</span>
            <span class="amt" style="color:{{ $isBalanced ? '#1e293b' : '#dc2626' }};">{{ number_format($data['totalAssets'], 2) }}</span>
        </div>
    </div>

    @if(!$isBalanced)
    <div style="background:#fef2f2;color:#991b1b;padding:10px 18px;font-size:.8rem;font-weight:600;border-top:1px solid #fca5a5;">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Imbalance of &#8377;{{ number_format($difference, 2) }} — check your journal entries.
    </div>
    @endif

    <div style="padding:10px 18px;font-size:.74rem;color:#6b7280;border-top:1px solid #e5e7eb;text-align:center;">
        **Amount is displayed in your base currency INR
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════ --}}
{{-- VERTICAL FORMAT                                                   --}}
{{-- ════════════════════════════════════════════════════════════════ --}}
@else

<div class="bs-shell">
    <div class="bs-co-header">
        <div class="co-name">AS DAIRY</div>
        <div class="co-title">Balance Sheet</div>
        <div class="co-sub">Basis: Accrual &nbsp;&nbsp;|&nbsp;&nbsp; As of {{ $asOfDate }} &nbsp;&nbsp;|&nbsp;&nbsp; Period: {{ $periodLabel }}</div>
    </div>

    {{-- LIABILITIES & EQUITIES block --}}
    <div style="padding:4px 0 0;">
        <div style="padding:10px 24px 4px;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;
            color:#fff;background:#374151;">
            LIABILITIES &amp; EQUITIES
            <span style="float:right;font-weight:400;">Amount (&#8377;)</span>
        </div>

        {{-- Liabilities --}}
        <div class="bs-v-sec-head">LIABILITIES</div>
        @forelse($liabGroups as $groupName => $rows)
        <div class="bs-v-sub-head">• {{ strtoupper($groupName) }}</div>
        @foreach($rows as $row)
        <div class="bs-v-row">
            <span>{{ $row->account->name }}</span>
            <span class="amt">{{ number_format($row->net, 2) }}</span>
        </div>
        @endforeach
        <div class="bs-v-subtotal">
            <span>TOTAL {{ strtoupper($groupName) }}</span>
            <span class="amt">{{ number_format($rows->sum('net'), 2) }}</span>
        </div>
        @empty
        <div class="bs-v-row"><span style="color:#9ca3af;">No liabilities.</span><span class="amt">0.00</span></div>
        @endforelse
        <div class="bs-v-sec-total">
            <span>TOTAL LIABILITIES</span>
            <span class="amt">{{ number_format($data['totalLiabilities'], 2) }}</span>
        </div>

        <div class="bs-v-spacer"></div>

        {{-- Equities --}}
        <div class="bs-v-sec-head">EQUITIES</div>
        @forelse($data['equity'] as $row)
        <div class="bs-v-row">
            <span>{{ $row->account->name }}</span>
            <span class="amt">{{ number_format($row->net, 2) }}</span>
        </div>
        @empty
        <div class="bs-v-row"><span style="color:#9ca3af;">No equity accounts.</span><span class="amt">0.00</span></div>
        @endforelse
        @if(($data['netIncome'] ?? 0) != 0)
        <div class="bs-v-row" style="background:#f0fdf4;">
            <span>Current Year Earnings</span>
            <span class="amt" style="color:{{ ($data['netIncome'] ?? 0) >= 0 ? '#059669' : '#dc2626' }};">
                {{ number_format($data['netIncome'] ?? 0, 2) }}
            </span>
        </div>
        @endif
        <div class="bs-v-sec-total">
            <span>TOTAL EQUITIES</span>
            <span class="amt">{{ number_format($data['totalEquity'], 2) }}</span>
        </div>

        <div class="bs-v-grand">
            <span>TOTAL LIABILITIES &amp; EQUITIES</span>
            <span class="amt">{{ number_format($totalLE, 2) }}</span>
        </div>
    </div>

    <div style="height:6px;background:#e5e7eb;"></div>

    {{-- ASSETS block --}}
    <div style="padding:4px 0 0;">
        <div style="padding:10px 24px 4px;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;
            color:#fff;background:#374151;">
            ASSETS
            <span style="float:right;font-weight:400;">Amount (&#8377;)</span>
        </div>

        @forelse($assetGroups as $groupName => $rows)
        <div class="bs-v-sec-head">{{ strtoupper($groupName) }}</div>
        @foreach($rows as $row)
        <div class="bs-v-row">
            <span>{{ $row->account->name }}</span>
            <span class="amt">{{ number_format($row->net, 2) }}</span>
        </div>
        @endforeach
        <div class="bs-v-subtotal">
            <span>TOTAL {{ strtoupper($groupName) }}</span>
            <span class="amt">{{ number_format($rows->sum('net'), 2) }}</span>
        </div>
        <div class="bs-v-spacer"></div>
        @empty
        <div class="bs-v-row"><span style="color:#9ca3af;">No assets.</span><span class="amt">0.00</span></div>
        @endforelse

        <div class="bs-v-grand">
            <span>TOTAL ASSETS</span>
            <span class="amt">{{ number_format($data['totalAssets'], 2) }}</span>
        </div>
    </div>

    @if(!$isBalanced)
    <div style="background:#fef2f2;color:#991b1b;padding:10px 18px;font-size:.8rem;font-weight:600;border-top:1px solid #fca5a5;">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Imbalance of &#8377;{{ number_format($difference, 2) }}.
    </div>
    @endif

    <div style="padding:10px 18px;font-size:.74rem;color:#6b7280;border-top:1px solid #e5e7eb;text-align:center;">
        **Amount is displayed in your base currency INR
    </div>
</div>
@endif

@endsection