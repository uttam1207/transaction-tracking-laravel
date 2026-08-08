@extends('layouts.app')
@section('title', 'Expense #' . $expense->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Expense #{{ $expense->id }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Expense #{{ $expense->id }}</h4>
            <p>{{ $expense->expense_date->format('d M Y') }} &mdash; {{ $expense->category?->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenses.edit', $expense) }}" class="btn btn-sm btn-outline-warning px-4">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger px-4">
                    <i class="bi bi-trash3 me-1"></i>Delete
                </button>
            </form>
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-glass p-4">
            <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">Expense Details</h6>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Date</dt>
                <dd class="col-sm-8 fw-semibold">{{ $expense->expense_date->format('d F Y') }}</dd>

                <dt class="col-sm-4 text-muted">Category</dt>
                <dd class="col-sm-8">
                    <span class="badge rounded-pill px-3 py-1" style="background:{{ $expense->category?->color ?? '#6366f1' }}22;color:{{ $expense->category?->color ?? '#6366f1' }};border:1px solid {{ $expense->category?->color ?? '#6366f1' }}44;">
                        <i class="bi {{ $expense->category?->icon ?? 'bi-tag' }} me-1"></i>{{ $expense->category?->name ?? 'N/A' }}
                    </span>
                </dd>

                <dt class="col-sm-4 text-muted">Description</dt>
                <dd class="col-sm-8">{{ $expense->description }}</dd>

                <dt class="col-sm-4 text-muted">Amount</dt>
                <dd class="col-sm-8 fw-bold fs-5">&#8377;{{ number_format($expense->amount, 2) }}</dd>

                <dt class="col-sm-4 text-muted">Vendor / Payee</dt>
                <dd class="col-sm-8">{{ $expense->vendor_payee ?? '—' }}</dd>
            </dl>

            <hr class="my-3 opacity-25">
            <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">Payment Details</h6>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Method</dt>
                <dd class="col-sm-8">{{ $expense->payment_method_label }}</dd>

                <dt class="col-sm-4 text-muted">Status</dt>
                <dd class="col-sm-8">
                    <span class="badge bg-{{ $expense->status_badge }}-subtle text-{{ $expense->status_badge }} border border-{{ $expense->status_badge }}-subtle px-3">
                        {{ ucfirst($expense->payment_status) }}
                    </span>
                </dd>

                <dt class="col-sm-4 text-muted">Reference No.</dt>
                <dd class="col-sm-8 font-monospace">{{ $expense->reference_number ?? '—' }}</dd>
            </dl>

            @if($expense->remarks)
            <hr class="my-3 opacity-25">
            <h6 class="fw-semibold text-muted mb-2" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">Remarks</h6>
            <p class="mb-0 text-muted">{{ $expense->remarks }}</p>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Bill / Receipt --}}
        <div class="card-glass p-4 mb-4">
            <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">Bill / Receipt</h6>
            @if($expense->bill_path)
                @php $ext = pathinfo($expense->bill_path, PATHINFO_EXTENSION); @endphp
                @if(in_array(strtolower($ext), ['jpg','jpeg','png']))
                    <img src="{{ asset('uploads/' . $expense->bill_path) }}" alt="Bill" class="img-fluid rounded mb-2" style="max-height:200px;object-fit:contain;">
                @endif
                <a href="{{ asset('uploads/' . $expense->bill_path) }}" target="_blank" class="btn btn-sm btn-outline-info w-100">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i>Download Bill
                </a>
            @else
                <div class="text-center text-muted py-3">
                    <i class="bi bi-file-earmark-x fs-2 opacity-30 d-block mb-2"></i>
                    No bill attached
                </div>
            @endif
        </div>

        {{-- Record Info --}}
        <div class="card-glass p-4">
            <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">Record Info</h6>
            <dl class="row mb-0 small">
                <dt class="col-6 text-muted">Recorded By</dt>
                <dd class="col-6">{{ $expense->recorder?->name ?? '—' }}</dd>

                <dt class="col-6 text-muted">Entry Time</dt>
                <dd class="col-6">{{ $expense->created_at->format('d M Y H:i') }}</dd>

                <dt class="col-6 text-muted">Last Updated</dt>
                <dd class="col-6">{{ $expense->updated_at->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>

@endsection
