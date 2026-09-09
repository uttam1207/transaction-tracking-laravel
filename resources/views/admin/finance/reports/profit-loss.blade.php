@extends('layouts.app')
@section('title', 'Profit & Loss Statement')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.coa.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Profit &amp; Loss</li>
@endsection

@section('content')

@php
    $netIncome = $data['netIncome'];
    $isProfit  = $netIncome >= 0;
    $margin    = $data['netRevenue'] > 0 ? round(($netIncome / $data['netRevenue']) * 100, 1) : 0;
    $total     = $data['netRevenue'] + $data['netExpenses'];
    $pct       = $total > 0 ? min(100, ($data['netRevenue'] / $total) * 100) : 0;
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Profit &amp; Loss Statement</h4>
            <p>Income and expense summary for the selected period</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.balance-sheet') }}" class="btn btn-outline-primary btn-sm">Balance Sheet</a>
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
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ number_format($data['netRevenue'], 2) }}</div>
            <div style="font-size:.72rem;color:#10b981;margin-top:3px;font-weight:600;">{{ $data['revenue']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #ef4444;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Expenses</div>
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ number_format($data['netExpenses'], 2) }}</div>
            <div style="font-size:.72rem;color:#ef4444;margin-top:3px;font-weight:600;">{{ $data['expenses']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $isProfit ? '#10b981' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Net {{ $isProfit ? 'Profit' : 'Loss' }}</div>
            <div style="font-size:1.45rem;font-weight:800;color:{{ $isProfit ? '#059669' : '#dc2626' }};margin-top:5px;line-height:1.2;">
                {{ $isProfit ? '+' : '' }}{{ number_format($netIncome, 2) }}
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
            <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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

<div class="row g-4">
    {{-- Revenue --}}
    <div class="col-md-6">
        <div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;height:100%;">
            <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px;">
                <span style="background:#d1fae5;color:#059669;width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;">
                    <i class="bi bi-arrow-up-circle"></i>
                </span>
                <div>
                    <div style="font-weight:700;font-size:.92rem;color:#1e293b;">Revenue</div>
                    <div style="font-size:.72rem;color:#9ca3af;">Income-generating accounts</div>
                </div>
                <span style="margin-left:auto;background:#ecfdf5;color:#059669;padding:4px 14px;border-radius:20px;font-size:.78rem;font-weight:700;">
                    ₹{{ number_format($data['netRevenue'], 2) }}
                </span>
            </div>
            <table class="table table-sm mb-0" style="font-size:.83rem;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Account</th>
                        <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['revenue'] as $row)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:10px 22px;border:none;">
                            <span style="display:inline-block;background:#f0f4ff;color:#4f46e5;padding:1px 7px;border-radius:5px;font-size:.7rem;font-weight:700;font-family:monospace;margin-right:7px;">{{ $row->account->code }}</span>
                            <span style="color:#374151;">{{ $row->account->name }}</span>
                        </td>
                        <td style="padding:10px 22px;text-align:right;font-weight:600;color:#059669;border:none;">{{ number_format($row->net, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" style="text-align:center;color:#9ca3af;padding:32px 22px;border:none;">No revenue entries found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#ecfdf5;border-top:2px solid #6ee7b7;">
                        <td style="padding:12px 22px;font-weight:700;color:#065f46;border:none;">Total Revenue</td>
                        <td style="padding:12px 22px;text-align:right;font-weight:800;color:#065f46;font-size:.95rem;border:none;">{{ number_format($data['netRevenue'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="col-md-6">
        <div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;height:100%;">
            <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px;">
                <span style="background:#fee2e2;color:#dc2626;width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;">
                    <i class="bi bi-arrow-down-circle"></i>
                </span>
                <div>
                    <div style="font-weight:700;font-size:.92rem;color:#1e293b;">Expenses</div>
                    <div style="font-size:.72rem;color:#9ca3af;">Cost and expense accounts</div>
                </div>
                <span style="margin-left:auto;background:#fff1f2;color:#dc2626;padding:4px 14px;border-radius:20px;font-size:.78rem;font-weight:700;">
                    ₹{{ number_format($data['netExpenses'], 2) }}
                </span>
            </div>
            <table class="table table-sm mb-0" style="font-size:.83rem;">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Account</th>
                        <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data['expenses'] as $row)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:10px 22px;border:none;">
                            <span style="display:inline-block;background:#f0f4ff;color:#4f46e5;padding:1px 7px;border-radius:5px;font-size:.7rem;font-weight:700;font-family:monospace;margin-right:7px;">{{ $row->account->code }}</span>
                            <span style="color:#374151;">{{ $row->account->name }}</span>
                        </td>
                        <td style="padding:10px 22px;text-align:right;font-weight:600;color:#dc2626;border:none;">{{ number_format($row->net, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" style="text-align:center;color:#9ca3af;padding:32px 22px;border:none;">No expense entries found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#fff1f2;border-top:2px solid #fca5a5;">
                        <td style="padding:12px 22px;font-weight:700;color:#991b1b;border:none;">Total Expenses</td>
                        <td style="padding:12px 22px;text-align:right;font-weight:800;color:#991b1b;font-size:.95rem;border:none;">{{ number_format($data['netExpenses'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Net Income Panel --}}
<div style="margin-top:22px;border-radius:14px;padding:24px 28px;border:2px solid {{ $isProfit ? '#6ee7b7' : '#fca5a5' }};
    background:{{ $isProfit ? 'linear-gradient(135deg,#ecfdf5,#d1fae5)' : 'linear-gradient(135deg,#fff1f2,#fee2e2)' }};">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <div style="display:flex;align-items:center;gap:14px;">
                <span style="background:{{ $isProfit ? '#059669' : '#dc2626' }};color:#fff;width:48px;height:48px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                    <i class="bi bi-{{ $isProfit ? 'graph-up-arrow' : 'graph-down-arrow' }}"></i>
                </span>
                <div>
                    <div style="font-size:1.05rem;font-weight:700;color:{{ $isProfit ? '#065f46' : '#991b1b' }};">Net {{ $isProfit ? 'Profit' : 'Loss' }}</div>
                    <div style="font-size:.8rem;color:{{ $isProfit ? '#059669' : '#dc2626' }};margin-top:2px;">Revenue − Expenses &nbsp;·&nbsp; Margin: <strong>{{ $margin }}%</strong></div>
                </div>
            </div>
            <div style="margin-top:18px;">
                <div style="display:flex;height:10px;border-radius:20px;overflow:hidden;background:#e5e7eb;">
                    <div style="width:{{ $pct }}%;background:#10b981;"></div>
                    <div style="width:{{ 100 - $pct }}%;background:#ef4444;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:.75rem;font-weight:600;">
                    <span style="color:#059669;"><i class="bi bi-arrow-up-circle me-1"></i>Revenue: {{ number_format($data['netRevenue'], 2) }}</span>
                    <span style="color:#dc2626;"><i class="bi bi-arrow-down-circle me-1"></i>Expenses: {{ number_format($data['netExpenses'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-5 text-md-end">
            <div style="font-size:2.4rem;font-weight:900;color:{{ $isProfit ? '#065f46' : '#991b1b' }};line-height:1.1;">
                {{ $isProfit ? '+' : '' }}{{ number_format($netIncome, 2) }}
            </div>
            <div style="font-size:.82rem;font-weight:600;color:{{ $isProfit ? '#059669' : '#dc2626' }};margin-top:4px;">
                {{ $isProfit ? 'Profitable period' : 'Loss period' }}
            </div>
        </div>
    </div>
</div>

@endsection