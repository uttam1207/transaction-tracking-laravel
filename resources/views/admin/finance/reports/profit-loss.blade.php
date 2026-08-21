@extends('layouts.app')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Profit &amp; Loss Statement</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profit &amp; Loss</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.finance.reports.trial-balance') }}" class="btn btn-outline-secondary btn-sm">Trial Balance</a>
            <a href="{{ route('admin.finance.reports.balance-sheet') }}" class="btn btn-outline-primary btn-sm">Balance Sheet</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Period</label>
                    <select name="period_id" class="form-select form-select-sm">
                        <option value="">— All Periods —</option>
                        @foreach ($periods as $p)
                            <option value="{{ $p->id }}" {{ $p->id == $periodId ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-auto">
                    <button class="btn btn-success btn-sm">Generate</button>
                    <a href="{{ route('admin.finance.reports.profit-loss') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
                @if ($selectedPeriod)
                <div class="col-auto ms-auto">
                    <span class="badge bg-info text-dark fs-6">{{ $selectedPeriod->name }}</span>
                </div>
                @endif
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Revenue --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white fw-semibold">
                    <i class="bi bi-arrow-up-circle me-1"></i> Revenue
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['revenue'] as $row)
                            <tr>
                                <td>
                                    <span class="text-muted small">{{ $row->account->code }}</span>
                                    {{ $row->account->name }}
                                </td>
                                <td class="text-end text-success fw-semibold">{{ number_format($row->net, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No revenue entries.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-success fw-bold">
                            <tr>
                                <td>Total Revenue</td>
                                <td class="text-end">{{ number_format($data['netRevenue'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Expenses --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-danger text-white fw-semibold">
                    <i class="bi bi-arrow-down-circle me-1"></i> Expenses
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account</th>
                                <th class="text-end">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['expenses'] as $row)
                            <tr>
                                <td>
                                    <span class="text-muted small">{{ $row->account->code }}</span>
                                    {{ $row->account->name }}
                                </td>
                                <td class="text-end text-danger fw-semibold">{{ number_format($row->net, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No expense entries.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-danger fw-bold">
                            <tr>
                                <td>Total Expenses</td>
                                <td class="text-end">{{ number_format($data['netExpenses'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Net Income --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-bold">Net Income</h5>
                    <div class="text-muted small">Revenue − Expenses</div>
                </div>
                <div class="col-md-6 text-end">
                    @php $netIncome = $data['netIncome']; @endphp
                    <span class="fs-3 fw-bold {{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $netIncome >= 0 ? '+' : '' }}{{ number_format($netIncome, 2) }}
                    </span>
                    <div class="small text-muted">{{ $netIncome >= 0 ? 'Profit' : 'Loss' }}</div>
                </div>
            </div>
            <div class="progress mt-3" style="height: 8px;">
                @php
                    $total = $data['netRevenue'] + $data['netExpenses'];
                    $pct   = $total > 0 ? min(100, ($data['netRevenue'] / $total) * 100) : 0;
                @endphp
                <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
                <div class="progress-bar bg-danger" style="width: {{ 100 - $pct }}%"></div>
            </div>
            <div class="d-flex justify-content-between small text-muted mt-1">
                <span>Revenue: {{ number_format($data['netRevenue'], 2) }}</span>
                <span>Expenses: {{ number_format($data['netExpenses'], 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
