@extends('layouts.app')
@section('title', 'Procurement & Purchase Orders')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-primary"><i class="bi bi-cart-check-fill me-2"></i>Module 13 — Procurement & Vendor Management</h1>
            <p class="text-muted mb-0">Purchase orders (PO), vendor management, quotations, GRN, bills & payments.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Registered Vendors</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['total_vendors'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Total Procurement Value</div>
                <div class="fs-3 fw-bold text-success">₹{{ number_format($summary['total_po_value'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Pending Purchase Orders</div>
                <div class="fs-3 fw-bold text-warning">{{ $summary['pending_pos'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Create Purchase Order</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.procurement.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">PO Number *</label>
                            <input type="text" name="po_number" class="form-control" value="PO-2026-00{{ rand(2, 9) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Vendor *</label>
                            <select name="vendor_id" class="form-select" required>
                                <option value="">-- Select Vendor --</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->category }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Order Date *</label>
                            <input type="date" name="order_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Total Amount (₹) *</label>
                            <input type="number" step="0.01" min="0" name="total_amount" class="form-control" placeholder="e.g. 15000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="Paid">Paid</option>
                                <option value="Sent">Sent</option>
                                <option value="Received">Received</option>
                                <option value="Draft">Draft</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i> Save Purchase Order</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-file-earmark-spreadsheet me-2 text-primary"></i>Purchase Orders Log</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>PO Number</th>
                                <th>Vendor</th>
                                <th>Order Date</th>
                                <th class="text-end">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $o)
                                <tr>
                                    <td><strong class="text-primary">{{ $o->po_number }}</strong></td>
                                    <td>{{ $o->vendor?->name }}</td>
                                    <td>{{ $o->order_date?->format('d-M-Y') }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($o->total_amount, 2) }}</td>
                                    <td><span class="badge bg-success">{{ $o->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No purchase orders logged.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
