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
    </div>
</div>

{{-- Filters --}}
<div class="card-glass mb-3 px-4 py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small mb-1">Account <span class="text-danger">*</span></label>
            <select name="account_id" class="form-select" required>
                <option value="">— Select Account —</option>
                @foreach ($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ $acc->id == $accountId ? 'selected' : '' }}>
                        {{ $acc->code }} — {{ $acc->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">From Date</label>
            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">To Date</label>
            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
        </div>
        <div class="col-auto">
            <button class="btn btn-primary-grad btn-sm px-4">View Ledger</button>
            <a href="{{ route('admin.finance.reports.general-ledger') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        </div>
    </form>
</div>

@if ($selectedAccount)
{{-- Account Info --}}
<div class="card-glass mb-3">
    <div class="card-body py-2 d-flex align-items-center gap-3">
        <span class="badge bg-{{ $selectedAccount->type_color }} fs-6">{{ ucfirst($selectedAccount->type) }}</span>
        <strong>{{ $selectedAccount->code }} — {{ $selectedAccount->name }}</strong>
        @if ($selectedAccount->sub_type)
            <span class="text-muted small">{{ $selectedAccount->sub_type }}</span>
        @endif
        <span class="ms-auto fw-bold">
            Closing Balance: <span class="text-{{ $data['closing_balance'] >= 0 ? 'success' : 'danger' }}">
                {{ number_format(abs($data['closing_balance']), 2) }}
                {{ $data['closing_balance'] >= 0 ? 'Dr' : 'Cr' }}
            </span>
        </span>
    </div>
</div>

{{-- Ledger Table --}}
<div class="card-glass">
    <div class="table-responsive">
        <table class="modern-table table table-sm mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Entry #</th>
                    <th>Description</th>
                    <th class="text-end">Debit (₹)</th>
                    <th class="text-end">Credit (₹)</th>
                    <th class="text-end">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['rows'] as $row)
                <tr>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($row['date'])->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('admin.finance.journal.show', $row['entry']->id) }}" class="text-decoration-none small">
                            {{ $row['entry_number'] }}
                        </a>
                    </td>
                    <td class="small">{{ Str::limit($row['description'] ?? '—', 60) }}</td>
                    <td class="text-end">{{ $row['debit'] > 0 ? number_format($row['debit'], 2) : '' }}</td>
                    <td class="text-end">{{ $row['credit'] > 0 ? number_format($row['credit'], 2) : '' }}</td>
                    <td class="text-end fw-semibold {{ $row['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format(abs($row['balance']), 2) }}
                        {{ $row['balance'] >= 0 ? 'Dr' : 'Cr' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No posted transactions found for this account.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if (!empty($data['rows']))
            <tfoot class="fw-bold">
                <tr>
                    <td colspan="3" class="text-end">Closing Balance</td>
                    <td colspan="2"></td>
                    <td class="text-end {{ $data['closing_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format(abs($data['closing_balance']), 2) }}
                        {{ $data['closing_balance'] >= 0 ? ' Dr' : ' Cr' }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@elseif ($accountId)
<div class="alert alert-warning">Account not found.</div>
@else
<div class="card-glass">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-journal-bookmark fs-1 d-block mb-2"></i>
        Select an account above to view its transaction history.
    </div>
</div>
@endif

@endsection
