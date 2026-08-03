@extends('layouts.app')
@section('title', 'Expense Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Expenses</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Expense Management</h4>
            <p>Record, track and analyse all farm expenses</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat">
                <div class="v">&#8377;{{ number_format($summary['today'], 0) }}</div>
                <div class="l">Today</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#86efac;">&#8377;{{ number_format($summary['this_month'], 0) }}</div>
                <div class="l">This Month</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#fde047;">&#8377;{{ number_format($summary['pending'], 0) }}</div>
                <div class="l">Pending</div>
            </div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat">
                <div class="v" style="color:#93c5fd;">{{ number_format($summary['total_count']) }}</div>
                <div class="l">Total Records</div>
            </div>
        </div>
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-sm btn-primary-grad px-4" style="border-radius:9px;">
            <i class="bi bi-plus-lg me-1"></i>Add Expense
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" action="{{ route('admin.expenses.index') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <div class="position-relative">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.8rem;pointer-events:none;"></i>
                <input type="text" name="search" class="form-control ps-4" placeholder="Search description, vendor…" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select name="category_id" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="payment_status" class="form-select">
                <option value="">All Status</option>
                <option value="paid"    @selected(request('payment_status') === 'paid')>Paid</option>
                <option value="pending" @selected(request('payment_status') === 'pending')>Pending</option>
            </select>
        </div>
        <div class="col-md-1">
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From">
        </div>
        <div class="col-md-1">
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-filter btn-primary flex-fill"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-filter btn-outline-secondary px-3"><i class="bi bi-x-lg"></i></a>
            <div class="dropdown">
                <button class="btn btn-filter btn-outline-success px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-download"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.expenses.export.excel', request()->all()) }}"><i class="bi bi-file-earmark-excel me-2 text-success"></i>Export Excel</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.expenses.export.csv', request()->all()) }}"><i class="bi bi-filetype-csv me-2 text-info"></i>Export CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.expenses.export.pdf', request()->all()) }}"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Export PDF</a></li>
                </ul>
            </div>
        </div>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Table --}}
<div class="card-glass">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Vendor / Payee</th>
                    <th>Recorded By</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>
                        <span class="fw-semibold">{{ $expense->expense_date->format('d M Y') }}</span>
                    </td>
                    <td>
                        <span class="badge rounded-pill" style="background:{{ $expense->category?->color ?? '#6366f1' }}22;color:{{ $expense->category?->color ?? '#6366f1' }};border:1px solid {{ $expense->category?->color ?? '#6366f1' }}44;font-size:.75rem;">
                            <i class="bi {{ $expense->category?->icon ?? 'bi-tag' }} me-1"></i>{{ $expense->category?->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td style="max-width:200px;" class="text-truncate">{{ $expense->description }}</td>
                    <td class="text-end fw-bold">&#8377;{{ number_format($expense->amount, 2) }}</td>
                    <td>
                        <span class="text-muted small">{{ $expense->payment_method_label }}</span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $expense->status_badge }}-subtle text-{{ $expense->status_badge }} border border-{{ $expense->status_badge }}-subtle">
                            {{ ucfirst($expense->payment_status) }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $expense->vendor_payee ?? '—' }}</td>
                    <td class="text-muted small">{{ $expense->recorder?->name ?? '—' }}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-xs btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-xs btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-outline-danger" title="Delete"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-receipt fs-2 d-block mb-2 opacity-30"></i>No expenses found.
                        <a href="{{ route('admin.expenses.create') }}" class="d-block mt-2">Add first expense</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($expenses->count())
            <tfoot>
                <tr class="table-active fw-bold">
                    <td colspan="3">Page Total</td>
                    <td class="text-end">&#8377;{{ number_format($expenses->sum('amount'), 2) }}</td>
                    <td colspan="5"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($expenses->hasPages())
    <div class="px-4 py-3 border-top">
        {{ $expenses->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
