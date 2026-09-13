@extends('layouts.app')
@section('title', 'General Ledger')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.coa.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">General Ledger</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>General Ledger</h4>
            <p>Detailed transaction history by account</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-success btn-sm">P&amp;L</a>
            <a href="{{ route('admin.finance.reports.balance-sheet') }}" class="btn btn-outline-primary btn-sm">Balance Sheet</a>
            <a href="{{ route('admin.export.general-ledger', 'excel') }}?{{ http_build_query(request()->only(['account_id','date_from','date_to'])) }}"
               class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.export.general-ledger', 'pdf') }}?{{ http_build_query(request()->only(['account_id','date_from','date_to'])) }}"
               class="btn btn-sm btn-outline-light px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            @if ($selectedAccount ?? false)
            <button onclick="window.print()" class="btn btn-sm" style="background:#f1f5f9;color:#374151;border:1.5px solid #e2e8f0;">
                <i class="bi bi-printer me-1"></i>Print
            </button>
            @endif
        </div>
    </div>
</div>

{{-- Filters --}}
<div style="background:#fff;border-radius:14px;padding:16px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:22px;">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1 fw-semibold" style="color:#374151;">Account <span class="text-danger">*</span></label>
            <select name="account_id" class="form-select form-select-sm" style="border-radius:9px;border:1.5px solid #e5e7eb;" required>
                <option value="">— Select Account —</option>
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ $acc->id == $accountId ? 'selected' : '' }}>
                        {{ $acc->code }} — {{ $acc->name }}
                    </option>
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
            <button class="btn btn-primary-grad btn-sm px-4">View Ledger</button>
            <a href="{{ route('admin.finance.reports.general-ledger') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

@if ($selectedAccount)

@php
    $typeColorMap = [
        'asset'     => ['bg'=>'#dbeafe','color'=>'#1d4ed8','border'=>'#3b82f6'],
        'liability' => ['bg'=>'#fee2e2','color'=>'#dc2626','border'=>'#ef4444'],
        'equity'    => ['bg'=>'#fef3c7','color'=>'#d97706','border'=>'#f59e0b'],
        'revenue'   => ['bg'=>'#d1fae5','color'=>'#059669','border'=>'#10b981'],
        'expense'   => ['bg'=>'#fce7f3','color'=>'#be185d','border'=>'#ec4899'],
    ];
    $tc         = $typeColorMap[$selectedAccount->type] ?? ['bg'=>'#f3f4f6','color'=>'#374151','border'=>'#9ca3af'];
    $closingBal = $data['closing_balance'];
    $totalRows  = count($data['rows']);
    $totalDr    = collect($data['rows'])->sum('debit');
    $totalCr    = collect($data['rows'])->sum('credit');
@endphp

{{-- Account Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div style="background:#fff;border-radius:14px;padding:18px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $tc['border'] }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Account</div>
            <div style="font-size:1rem;font-weight:800;color:#1e293b;margin-top:6px;line-height:1.3;">{{ $selectedAccount->name }}</div>
            <div style="margin-top:8px;display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
                <span style="background:{{ $tc['bg'] }};color:{{ $tc['color'] }};padding:2px 10px;border-radius:20px;font-size:.71rem;font-weight:700;">{{ ucfirst($selectedAccount->type) }}</span>
                <span style="background:#f0f4ff;color:#4f46e5;padding:2px 8px;border-radius:5px;font-size:.7rem;font-weight:700;font-family:monospace;">{{ $selectedAccount->code }}</span>
                @if ($selectedAccount->sub_type)
                <span style="color:#9ca3af;font-size:.72rem;">{{ $selectedAccount->sub_type }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #3b82f6;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Debits</div>
            <div style="font-size:1.2rem;font-weight:800;color:#1d4ed8;margin-top:5px;">{{ number_format($totalDr, 2) }}</div>
            <div style="font-size:.72rem;color:#3b82f6;margin-top:2px;font-weight:600;">Dr</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #7c3aed;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Total Credits</div>
            <div style="font-size:1.2rem;font-weight:800;color:#7c3aed;margin-top:5px;">{{ number_format($totalCr, 2) }}</div>
            <div style="font-size:.72rem;color:#7c3aed;margin-top:2px;font-weight:600;">Cr</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid #64748b;">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Transactions</div>
            <div style="font-size:1.2rem;font-weight:800;color:#1e293b;margin-top:5px;">{{ $totalRows }}</div>
            <div style="font-size:.72rem;color:#64748b;margin-top:2px;font-weight:600;">entries</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div style="background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid {{ $closingBal >= 0 ? '#10b981' : '#ef4444' }};">
            <div style="font-size:.72rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;">Closing Balance</div>
            <div style="font-size:1.1rem;font-weight:800;color:{{ $closingBal >= 0 ? '#059669' : '#dc2626' }};margin-top:5px;line-height:1.2;">
                {{ number_format(abs($closingBal), 2) }}
            </div>
            <div style="font-size:.72rem;color:{{ $closingBal >= 0 ? '#10b981' : '#ef4444' }};margin-top:2px;font-weight:700;">{{ $closingBal >= 0 ? 'Dr' : 'Cr' }}</div>
        </div>
    </div>
</div>

{{-- Ledger Table --}}
<div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);overflow:hidden;">
    <div style="padding:16px 22px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-weight:700;font-size:.92rem;color:#1e293b;">Transaction History</div>
        <div style="font-size:.78rem;color:#6b7280;">{{ $totalRows }} entries</div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm mb-0" style="font-size:.83rem;">
            <thead style="background:#f8fafc;">
                <tr>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Date</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Entry #</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;border:none;">Description</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Debit (₹)</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Credit (₹)</th>
                    <th style="padding:9px 22px;color:#6b7280;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.4px;text-align:right;border:none;">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $row)
                <tr style="border-bottom:1px solid #f8fafc;">
                    <td style="padding:10px 22px;color:#6b7280;border:none;white-space:nowrap;">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td style="padding:10px 22px;border:none;">
                        <a href="{{ route('admin.finance.journal.show', $row['entry']->id) }}"
                           style="color:#4f46e5;font-weight:600;text-decoration:none;font-family:monospace;font-size:.8rem;">
                            {{ $row['entry_number'] }}
                        </a>
                    </td>
                    <td style="padding:10px 22px;color:#374151;border:none;">{{ Str::limit($row['description'] ?? '—', 60) }}</td>
                    <td style="padding:10px 22px;text-align:right;color:#1d4ed8;font-weight:600;border:none;">
                        {{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '—' }}
                    </td>
                    <td style="padding:10px 22px;text-align:right;color:#7c3aed;font-weight:600;border:none;">
                        {{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '—' }}
                    </td>
                    <td style="padding:10px 22px;text-align:right;border:none;">
                        <span style="font-weight:700;color:{{ $row['balance'] >= 0 ? '#059669' : '#dc2626' }};">
                            {{ number_format(abs($row['balance']), 2) }}
                        </span>
                        <span style="font-size:.68rem;font-weight:700;color:{{ $row['balance'] >= 0 ? '#10b981' : '#ef4444' }};margin-left:2px;">
                            {{ $row['balance'] >= 0 ? 'Dr' : 'Cr' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#9ca3af;padding:44px;border:none;">
                        <i class="bi bi-journal-x" style="font-size:2.2rem;display:block;margin-bottom:10px;color:#d1d5db;"></i>
                        No posted transactions found for this account.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if (!empty($data['rows']))
            <tfoot>
                <tr style="background:#1e293b;">
                    <td colspan="3" style="padding:13px 22px;text-align:right;font-weight:700;color:#94a3b8;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;border:none;">Closing Balance</td>
                    <td style="padding:13px 22px;text-align:right;font-weight:800;color:#60a5fa;font-size:.92rem;border:none;">{{ number_format($totalDr, 2) }}</td>
                    <td style="padding:13px 22px;text-align:right;font-weight:800;color:#a78bfa;font-size:.92rem;border:none;">{{ number_format($totalCr, 2) }}</td>
                    <td style="padding:13px 22px;text-align:right;border:none;">
                        <span style="font-weight:900;color:{{ $closingBal >= 0 ? '#34d399' : '#f87171' }};font-size:.95rem;">
                            {{ number_format(abs($closingBal), 2) }}
                        </span>
                        <span style="font-size:.72rem;font-weight:700;color:{{ $closingBal >= 0 ? '#34d399' : '#f87171' }};margin-left:3px;">
                            {{ $closingBal >= 0 ? 'Dr' : 'Cr' }}
                        </span>
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@elseif ($accountId)
<div style="background:#fff;border-radius:14px;padding:20px 22px;box-shadow:0 1px 8px rgba(0,0,0,.07);">
    <div style="background:#fef3c7;border:1.5px solid #fde68a;border-radius:10px;padding:14px 18px;color:#92400e;font-weight:600;">
        <i class="bi bi-exclamation-triangle me-1"></i> Account not found.
    </div>
</div>
@else
<div style="background:#fff;border-radius:14px;box-shadow:0 1px 8px rgba(0,0,0,.07);text-align:center;padding:64px 20px;">
    <i class="bi bi-journal-bookmark" style="font-size:3rem;color:#cbd5e1;display:block;margin-bottom:14px;"></i>
    <div style="font-weight:700;color:#64748b;font-size:1rem;">Select an account above</div>
    <div style="color:#94a3b8;font-size:.85rem;margin-top:5px;">Choose an account to view its full transaction history and running balance.</div>
</div>
@endif

@endsection