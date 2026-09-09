@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.coa.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Balance Sheet</li>
@endsection

@section('content')

@php
    $difference = round(abs($data['totalAssets'] - ($data['totalLiabilities'] + $data['totalEquity'])), 2);
    $isBalanced = $difference === 0.0;
    $totalLE    = $data['totalLiabilities'] + $data['totalEquity'];
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Balance Sheet</h4>
            <p>Assets, liabilities &amp; equity at a point in time</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @if ($data['totalAssets'] > 0 || $data['totalLiabilities'] > 0)
            <span style="display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:20px;font-size:.78rem;font-weight:700;
                background:{{ $isBalanced ? '#d1fae5' : '#fee2e2' }};color:{{ $isBalanced ? '#065f46' : '#991b1b' }};">
                <i class="bi bi-{{ $isBalanced ? 'check-circle-fill' : 'exclamation-triangle-fill' }}"></i>
                {{ $isBalanced ? 'Balanced' : 'Unbalanced' }}
            </span>
            @endif
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-success btn-sm">P&amp;L</a>
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
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ number_format($data['totalAssets'], 2) }}</div>
            <div style="font-size:.72rem;color:#3b82f6;margin-top:3px;font-weight:600;">{{ $data['assets']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #ef4444;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Liabilities</div>
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ number_format($data['totalLiabilities'], 2) }}</div>
            <div style="font-size:.72rem;color:#ef4444;margin-top:3px;font-weight:600;">{{ $data['liabilities']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #f59e0b;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Equity</div>
            <div style="font-size:1.45rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ number_format($data['totalEquity'], 2) }}</div>
            <div style="font-size:.72rem;color:#f59e0b;margin-top:3px;font-weight:600;">{{ $data['equity']->count() }} accounts</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $isBalanced ? '#10b981' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">L + E Total</div>
            <div style="font-size:1.45rem;font-weight:800;color:{{ $isBalanced ? '#065f46' : '#991b1b' }};margin-top:5px;line-height:1.2;">{{ number_format($totalLE, 2) }}</div>
            <div style="font-size:.72rem;color:{{ $isBalanced ? '#10b981' : '#ef4444' }};margin-top:3px;font-weight:600;">
                {{ $isBalanced ? 'Assets = L + E ✓' : 'Diff: ' . number_format($difference, 2) }}
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#fff;border-radius:14px;padding:16px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:22px;">
    <form method="GET" class="row g-2 align-items-end">
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
            <a href="{{ route('admin.finance.reports.balance-sheet') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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
    {{-- Assets --}}
    <div class="col-md-6">
        <div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;height:100%;">
            <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px;">
                <span style="background:#dbeafe;color:#1d4ed8;width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;">
                    <i class="bi bi-bank"></i>
                </span>
                <div>
                    <div style="font-weight:700;font-size:.92rem;color:#1e293b;">Assets</div>
                    <div style="font-size:.72rem;color:#9ca3af;">What the business owns</div>
                </div>
                <span style="margin-left:auto;background:#eff6ff;color:#1d4ed8;padding:4px 14px;border-radius:20px;font-size:.78rem;font-weight:700;">
                    ₹{{ number_format($data['totalAssets'], 2) }}
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
                    @forelse ($data['assets'] as $row)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:10px 22px;border:none;">
                            <span style="display:inline-block;background:#f0f4ff;color:#4f46e5;padding:1px 7px;border-radius:5px;font-size:.7rem;font-weight:700;font-family:monospace;margin-right:7px;">{{ $row->account->code }}</span>
                            <span style="color:#374151;">{{ $row->account->name }}</span>
                        </td>
                        <td style="padding:10px 22px;text-align:right;font-weight:600;color:#1d4ed8;border:none;">{{ number_format($row->net, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" style="text-align:center;color:#9ca3af;padding:32px 22px;border:none;">No asset entries found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#eff6ff;border-top:2px solid #bfdbfe;">
                        <td style="padding:12px 22px;font-weight:700;color:#1d4ed8;border:none;">Total Assets</td>
                        <td style="padding:12px 22px;text-align:right;font-weight:800;color:#1d4ed8;font-size:.95rem;border:none;">{{ number_format($data['totalAssets'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Liabilities + Equity --}}
    <div class="col-md-6 d-flex flex-column gap-3">
        {{-- Liabilities --}}
        <div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;">
            <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px;">
                <span style="background:#fee2e2;color:#dc2626;width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;">
                    <i class="bi bi-credit-card"></i>
                </span>
                <div>
                    <div style="font-weight:700;font-size:.92rem;color:#1e293b;">Liabilities</div>
                    <div style="font-size:.72rem;color:#9ca3af;">What the business owes</div>
                </div>
                <span style="margin-left:auto;background:#fff1f2;color:#dc2626;padding:4px 14px;border-radius:20px;font-size:.78rem;font-weight:700;">
                    ₹{{ number_format($data['totalLiabilities'], 2) }}
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
                    @forelse ($data['liabilities'] as $row)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:10px 22px;border:none;">
                            <span style="display:inline-block;background:#f0f4ff;color:#4f46e5;padding:1px 7px;border-radius:5px;font-size:.7rem;font-weight:700;font-family:monospace;margin-right:7px;">{{ $row->account->code }}</span>
                            <span style="color:#374151;">{{ $row->account->name }}</span>
                        </td>
                        <td style="padding:10px 22px;text-align:right;font-weight:600;color:#dc2626;border:none;">{{ number_format($row->net, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" style="text-align:center;color:#9ca3af;padding:22px;border:none;">No liability entries found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#fff1f2;border-top:2px solid #fecaca;">
                        <td style="padding:11px 22px;font-weight:700;color:#dc2626;border:none;">Total Liabilities</td>
                        <td style="padding:11px 22px;text-align:right;font-weight:800;color:#dc2626;border:none;">{{ number_format($data['totalLiabilities'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Equity --}}
        <div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;">
            <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px;">
                <span style="background:#fef3c7;color:#d97706;width:36px;height:36px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;">
                    <i class="bi bi-pie-chart"></i>
                </span>
                <div>
                    <div style="font-weight:700;font-size:.92rem;color:#1e293b;">Equity</div>
                    <div style="font-size:.72rem;color:#9ca3af;">Owner's interest in the business</div>
                </div>
                <span style="margin-left:auto;background:#fffbeb;color:#d97706;padding:4px 14px;border-radius:20px;font-size:.78rem;font-weight:700;">
                    ₹{{ number_format($data['totalEquity'], 2) }}
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
                    @forelse ($data['equity'] as $row)
                    <tr style="border-bottom:1px solid #f8fafc;">
                        <td style="padding:10px 22px;border:none;">
                            <span style="display:inline-block;background:#f0f4ff;color:#4f46e5;padding:1px 7px;border-radius:5px;font-size:.7rem;font-weight:700;font-family:monospace;margin-right:7px;">{{ $row->account->code }}</span>
                            <span style="color:#374151;">{{ $row->account->name }}</span>
                        </td>
                        <td style="padding:10px 22px;text-align:right;font-weight:600;color:#d97706;border:none;">{{ number_format($row->net, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="2" style="text-align:center;color:#9ca3af;padding:22px;border:none;">No equity entries found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background:#fffbeb;border-top:2px solid #fde68a;">
                        <td style="padding:11px 22px;font-weight:700;color:#d97706;border:none;">Total Equity</td>
                        <td style="padding:11px 22px;text-align:right;font-weight:800;color:#d97706;border:none;">{{ number_format($data['totalEquity'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Balance total --}}
        <div style="border-radius:14px;padding:18px 22px;border:2px solid {{ $isBalanced ? '#6ee7b7' : '#fca5a5' }};
            background:{{ $isBalanced ? 'linear-gradient(135deg,#ecfdf5,#d1fae5)' : 'linear-gradient(135deg,#fff1f2,#fee2e2)' }};">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-weight:700;font-size:.88rem;color:{{ $isBalanced ? '#065f46' : '#991b1b' }};">
                        <i class="bi bi-{{ $isBalanced ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-1"></i>
                        Total Liabilities + Equity
                    </div>
                    <div style="font-size:.75rem;color:{{ $isBalanced ? '#059669' : '#dc2626' }};margin-top:3px;">
                        {{ $isBalanced ? 'Perfectly balanced — Assets = Liabilities + Equity' : 'Difference of ' . number_format($difference, 2) }}
                    </div>
                </div>
                <div style="font-size:1.5rem;font-weight:900;color:{{ $isBalanced ? '#065f46' : '#991b1b' }};">
                    {{ number_format($totalLE, 2) }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection