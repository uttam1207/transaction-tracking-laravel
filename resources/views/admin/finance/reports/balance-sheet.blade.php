@extends('layouts.app')
@section('title', 'Balance Sheet')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.accounts.index') }}">Finance</a></li>
    <li class="breadcrumb-item">Reports</li>
    <li class="breadcrumb-item active">Balance Sheet</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Balance Sheet</h4>
            <p>Assets, liabilities, and equity at a point in time</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-success btn-sm">P&amp;L</a>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card-glass mb-3 px-4 py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">As of Period</label>
            <select name="period_id" class="form-select">
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
            <span class="badge bg-info text-dark fs-6">As of {{ $selectedPeriod->name }}</span>
        </div>
        @endif
    </form>
</div>

{{-- Balance Check --}}
@php
    $difference = round(abs($data['totalAssets'] - ($data['totalLiabilities'] + $data['totalEquity'])), 2);
    $isBalanced = $difference === 0.0;
@endphp
@if ($data['totalAssets'] > 0 || $data['totalLiabilities'] > 0)
<div class="alert {{ $isBalanced ? 'alert-success' : 'alert-danger' }} mb-3 py-2">
    <i class="bi bi-{{ $isBalanced ? 'check-circle' : 'exclamation-triangle' }} me-1"></i>
    @if ($isBalanced)
        Balance Sheet is <strong>balanced</strong> — Assets = Liabilities + Equity.
    @else
        Balance Sheet is <strong>unbalanced</strong> — difference of {{ number_format($difference, 2) }}.
    @endif
</div>
@endif

<div class="row g-4">
    {{-- Assets (left side) --}}
    <div class="col-md-6">
        <div class="card-glass h-100">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-bank me-1"></i> Assets
            </div>
            <div class="table-responsive">
                <table class="modern-table table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['assets'] as $row)
                        <tr>
                            <td>
                                <span class="text-muted small">{{ $row->account->code }}</span>
                                {{ $row->account->name }}
                            </td>
                            <td class="text-end fw-semibold">{{ number_format($row->net, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No asset entries.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-primary fw-bold">
                        <tr>
                            <td>Total Assets</td>
                            <td class="text-end">{{ number_format($data['totalAssets'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Liabilities + Equity (right side) --}}
    <div class="col-md-6">
        <div class="card-glass mb-3">
            <div class="card-header bg-danger text-white fw-semibold">
                <i class="bi bi-credit-card me-1"></i> Liabilities
            </div>
            <div class="table-responsive">
                <table class="modern-table table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['liabilities'] as $row)
                        <tr>
                            <td>
                                <span class="text-muted small">{{ $row->account->code }}</span>
                                {{ $row->account->name }}
                            </td>
                            <td class="text-end fw-semibold text-danger">{{ number_format($row->net, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No liability entries.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-danger fw-bold">
                        <tr>
                            <td>Total Liabilities</td>
                            <td class="text-end">{{ number_format($data['totalLiabilities'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-glass mb-3">
            <div class="card-header bg-warning text-dark fw-semibold">
                <i class="bi bi-pie-chart me-1"></i> Equity
            </div>
            <div class="table-responsive">
                <table class="modern-table table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data['equity'] as $row)
                        <tr>
                            <td>
                                <span class="text-muted small">{{ $row->account->code }}</span>
                                {{ $row->account->name }}
                            </td>
                            <td class="text-end fw-semibold text-warning">{{ number_format($row->net, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No equity entries.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-warning fw-bold">
                        <tr>
                            <td>Total Equity</td>
                            <td class="text-end">{{ number_format($data['totalEquity'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Right side total --}}
        <div class="card-glass bg-dark text-white">
            <div class="card-body py-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold">Total Liabilities + Equity</span>
                <span class="fw-bold fs-5">
                    {{ number_format($data['totalLiabilities'] + $data['totalEquity'], 2) }}
                </span>
            </div>
        </div>
    </div>
</div>

@endsection
