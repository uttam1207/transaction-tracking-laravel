@extends('layouts.app')
@section('title', 'Trial Balance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.accounts.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Trial Balance</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Trial Balance</h4>
            <p>Debit and credit balances for all accounts</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-success btn-sm">P&amp;L</a>
            <a href="{{ route('admin.finance.reports.balance-sheet') }}" class="btn btn-outline-primary btn-sm">Balance Sheet</a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-glass mb-3 px-4 py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">Financial Period</label>
            <select name="period_id" class="form-select">
                <option value="">— All Periods —</option>
                @foreach ($periods as $p)
                    <option value="{{ $p->id }}" {{ $p->id == request('period_id') ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">Generate</button>
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
        @if ($selectedPeriod)
        <div class="col-auto ms-auto">
            <span class="badge bg-info text-dark fs-6">{{ $selectedPeriod->name }}</span>
        </div>
        @endif
    </form>
</div>

{{-- Balance indicator --}}
@if ($rows->isNotEmpty())
<div class="alert {{ $balanced ? 'alert-success' : 'alert-danger' }} mb-3 py-2">
    <i class="bi bi-{{ $balanced ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
    @if ($balanced)
        Trial Balance is <strong>balanced</strong> — Total Debits equal Total Credits.
    @else
        Trial Balance is <strong>unbalanced</strong> — difference of {{ number_format(abs($totalDebit - $totalCredit), 2) }}.
    @endif
</div>
@endif

<div class="card-glass">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Account Balances</span>
        <span class="text-muted small">{{ $rows->count() }} accounts</span>
    </div>
    <div class="table-responsive">
        <table class="modern-table table table-sm mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Account Name</th>
                    <th>Type</th>
                    <th class="text-end">Debit (₹)</th>
                    <th class="text-end">Credit (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                <tr>
                    <td class="text-muted small">{{ $row->account->code }}</td>
                    <td>{{ $row->account->name }}</td>
                    <td>
                        <span class="badge bg-{{ $row->account->type_color }}">
                            {{ ucfirst($row->account->type) }}
                        </span>
                    </td>
                    <td class="text-end">{{ $row->total_debit > 0 ? number_format($row->total_debit, 2) : '' }}</td>
                    <td class="text-end">{{ $row->total_credit > 0 ? number_format($row->total_credit, 2) : '' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        No posted journal entries found.
                        @if (!$selectedPeriod)
                            Select a period or ensure entries are posted.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
            <tfoot class="table-dark fw-bold">
                <tr>
                    <td colspan="3" class="text-end">Totals</td>
                    <td class="text-end">{{ number_format($totalDebit, 2) }}</td>
                    <td class="text-end">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
