@extends('layouts.app')
@section('title', 'Invoice — ' . $salesOrder->invoice_number)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-receipt me-2"></i>{{ $salesOrder->invoice_number }}</h1>
            <p class="text-muted mb-0">{{ $salesOrder->item_type }} — {{ $salesOrder->sale_date->format('d M Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.edit', $salesOrder) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.sales.destroy', $salesOrder) }}" onsubmit="return confirm('Delete this sales order?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Invoice Number</dt><dd class="col-7 fw-bold font-monospace">{{ $salesOrder->invoice_number }}</dd>
            <dt class="col-5 text-muted">Customer</dt><dd class="col-7">{{ $salesOrder->customer?->name ?? '— Walk-in —' }}</dd>
            <dt class="col-5 text-muted">Item Type</dt><dd class="col-7"><span class="badge bg-primary bg-opacity-10 text-primary border">{{ $salesOrder->item_type }}</span></dd>
            <dt class="col-5 text-muted">Sale Date</dt><dd class="col-7">{{ $salesOrder->sale_date->format('d F Y') }}</dd>
            <dt class="col-5 text-muted">Quantity</dt><dd class="col-7">{{ number_format($salesOrder->quantity, 2) }}</dd>
            <dt class="col-5 text-muted">Rate</dt><dd class="col-7">₹{{ number_format($salesOrder->rate, 2) }}</dd>
            <dt class="col-5 text-muted">Total Amount</dt><dd class="col-7 fw-bold text-success fs-5">₹{{ number_format($salesOrder->total_amount, 2) }}</dd>
            <dt class="col-5 text-muted">Payment Status</dt>
            <dd class="col-7">
                <span class="badge bg-{{ $salesOrder->payment_status === 'Paid' ? 'success' : ($salesOrder->payment_status === 'Partial' ? 'warning' : 'danger') }}">
                    {{ $salesOrder->payment_status }}
                </span>
            </dd>
            <dt class="col-5 text-muted">Created At</dt><dd class="col-7 small text-muted">{{ $salesOrder->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
