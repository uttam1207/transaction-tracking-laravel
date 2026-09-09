@extends('layouts.app')
@section('title', 'Trial Balance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.coa.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Trial Balance</li>
@endsection

@section('content')

@php $diff = abs($totalDebit - $totalCredit); @endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Trial Balance</h4>
            <p>Debit and credit balances for all accounts</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-success btn-sm">P&amp;L</a>
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
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #3b82f6;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Debits</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ number_format($totalDebit, 2) }}</div>
            <div style="font-size:.72rem;color:#3b82f6;margin-top:3px;font-weight:600;">Dr side</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #8b5cf6;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Credits</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ number_format($totalCredit, 2) }}</div>
            <div style="font-size:.72rem;color:#8b5cf6;margin-top:3px;font-weight:600;">Cr side</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #64748b;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Accounts</div>
            <div style="font-size:1.4rem;font-weight:800;color:#1e293b;margin-top:5px;line-height:1.2;">{{ $rows->count() }}</div>
            <div style="font-size:.72rem;color:#64748b;margin-top:3px;font-weight:600;">with activity</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $balanced ? '#10b981' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Status</div>
            <div style="font-size:1rem;font-weight:800;color:{{ $balanced ? '#059669' : '#dc2626' }};margin-top:6px;">
                <i class="bi bi-{{ $balanced ? 'check-circle-fill' : 'exclamation-triangle-fill' }} me-1"></i>
                {{ $balanced ? 'Balanced' : 'Unbalanced' }}
            </div>
            <div style="font-size:.72rem;color:{{ $balanced ? '#10b981' : '#ef4444' }};margin-top:3px;font-weight:600;">
                {{ $balanced ? 'Dr = Cr ✓' : 'Diff: ' . number_format($diff, 2) }}
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#fff;border-radius:14px;padding:16px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:22px;">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">Financial Period</label>
            <select name="period_id" class="form-select form-select-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                <option value="">— All Periods —</option>
                @foreach ($periods as $p)
                    <option value="{{ $p->id }}" {{ $p->id == request('period_id') ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Generate</button>
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
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

<div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;">
    <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-weight:700;font-size:.92rem;color:#1e293b;">Account Balances</div>
        <div style="font-size:.78rem;color:#6b7280;">{{ $rows->count() }} accounts</div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:.83rem;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Code</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Account Name</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Type</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Debit (₹)</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Credit (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                @php
                    $typeStyles = [
                        'asset'     => 'background:#dbeafe;color:#1d4ed8;',
                        'liability' => 'background:#fee2e2;color:#dc2626;',
                        'equity'    => 'background:#fef3c7;color:#d97706;',
                        'revenue'   => 'background:#d1fae5;color:#059669;',
                        'expense'   => 'background:#fce7f3;color:#be185d;',
                    ];
                    $ts = $typeStyles[$row->account->type] ?? 'background:#f3f4f6;color:#374151;';
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:10px 22px;border:none;">
                        <span style="background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:5px;font-size:.7rem;font-weight:700;font-family:monospace;">{{ $row->account->code }}</span>
                    </td>
                    <td style="padding:10px 22px;font-weight:500;color:#374151;border:none;">{{ $row->account->name }}</td>
                    <td style="padding:10px 22px;border:none;">
                        <span style="padding:2px 10px;border-radius:20px;font-size:.71rem;font-weight:700;{{ $ts }}">
                            {{ ucfirst($row->account->type) }}
                        </span>
                    </td>
                    <td style="padding:10px 22px;text-align:right;font-weight:600;color:#1d4ed8;border:none;">
                        {{ $row->total_debit > 0 ? number_format($row->total_debit, 2) : '—' }}
                    </td>
                    <td style="padding:10px 22px;text-align:right;font-weight:600;color:#7c3aed;border:none;">
                        {{ $row->total_credit > 0 ? number_format($row->total_credit, 2) : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#9ca3af;padding:44px;border:none;">
                        <i class="bi bi-journal-x" style="font-size:2.2rem;display:block;margin-bottom:10px;color:#d1d5db;"></i>
                        No posted journal entries found.
                        @if (!$selectedPeriod)<div style="font-size:.8rem;margin-top:4px;">Select a period or ensure entries are posted.</div>@endif
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
            <tfoot>
                <tr style="background:#1e293b;">
                    <td colspan="3" style="padding:13px 22px;text-align:right;font-weight:700;color:#94a3b8;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;border:none;">Totals</td>
                    <td style="padding:13px 22px;text-align:right;font-weight:800;color:#60a5fa;font-size:.95rem;border:none;">{{ number_format($totalDebit, 2) }}</td>
                    <td style="padding:13px 22px;text-align:right;font-weight:800;color:#a78bfa;font-size:.95rem;border:none;">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection