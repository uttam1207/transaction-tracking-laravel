@extends('layouts.app')
@section('title', 'Purchase Order — ' . $purchaseOrder->po_number)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-secondary"><i class="bi bi-cart-check me-2"></i>{{ $purchaseOrder->po_number }}</h1>
            <p class="text-muted mb-0">{{ $purchaseOrder->vendor->name }} — {{ $purchaseOrder->order_date->format('d M Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.procurement.edit', $purchaseOrder) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.procurement.destroy', $purchaseOrder) }}" onsubmit="return confirm('Delete this purchase order?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.procurement.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">PO Number</dt><dd class="col-7 fw-bold font-monospace">{{ $purchaseOrder->po_number }}</dd>
            <dt class="col-5 text-muted">Vendor</dt><dd class="col-7 fw-bold">{{ $purchaseOrder->vendor->name }}</dd>
            <dt class="col-5 text-muted">Order Date</dt><dd class="col-7">{{ $purchaseOrder->order_date->format('d F Y') }}</dd>
            <dt class="col-5 text-muted">Total Amount</dt><dd class="col-7 fw-bold text-success fs-5">₹{{ number_format($purchaseOrder->total_amount, 2) }}</dd>
            <dt class="col-5 text-muted">Status</dt>
            <dd class="col-7">
                <span class="badge bg-{{ $purchaseOrder->status === 'Paid' ? 'success' : ($purchaseOrder->status === 'Received' ? 'info' : ($purchaseOrder->status === 'Sent' ? 'warning' : 'secondary')) }}">
                    {{ $purchaseOrder->status }}
                </span>
            </dd>
            <dt class="col-5 text-muted">Remarks</dt><dd class="col-7">{{ $purchaseOrder->remarks ?? '—' }}</dd>
            <dt class="col-5 text-muted">Created At</dt><dd class="col-7 small text-muted">{{ $purchaseOrder->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
