@extends('layouts.app')
@section('title', 'Sales & Invoicing')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-cash-coin me-2"></i>Module 14 — Sales & Invoicing</h1>
            <p class="text-muted mb-0">Milk Sales, Animal Sales, Feed Sales, Online Orders, Invoices & Outstanding Payments.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Total Sales Revenue</div>
                <div class="fs-3 fw-bold text-success">₹{{ number_format($summary['total_sales'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Milk Sales</div>
                <div class="fs-3 fw-bold text-primary">₹{{ number_format($summary['milk_sales'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Animal Sales</div>
                <div class="fs-3 fw-bold text-info">₹{{ number_format($summary['animal_sales'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Pending Outstanding</div>
                <div class="fs-3 fw-bold text-danger">₹{{ number_format($summary['pending_payment'], 0) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Create Sales Invoice</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sales.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Invoice Number *</label>
                            <input type="text" name="invoice_number" class="form-control" value="INV-2026-00{{ rand(10, 99) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Customer</label>
                            <select name="crm_customer_id" class="form-select">
                                <option value="">-- Direct Retail Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->category }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Sale Item Type *</label>
                            <select name="item_type" class="form-select" required>
                                <option value="Milk Sales">Milk Sales</option>
                                <option value="Animal Sales">Animal Sales</option>
                                <option value="Feed Sales">Feed Sales</option>
                                <option value="Dung Sales">Dung Sales / Organic Compost</option>
                                <option value="Franchise Royalty">Franchise Royalty</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Sale Date *</label>
                            <input type="date" name="sale_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Quantity *</label>
                                <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" placeholder="e.g. 100" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Rate (₹) *</label>
                                <input type="number" step="0.01" min="0.01" name="rate" class="form-control" placeholder="e.g. 60" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Payment Status *</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="Paid">Paid</option>
                                <option value="Pending">Pending</option>
                                <option value="Partial">Partial</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-save me-1"></i> Save Sales Invoice</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Sales Invoices Log</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Item Type</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $s)
                                <tr>
                                    <td><strong class="text-success">{{ $s->invoice_number }}</strong></td>
                                    <td>{{ $s->customer?->name ?? 'Retail Customer' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $s->item_type }}</span></td>
                                    <td class="text-end">{{ number_format($s->quantity, 1) }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($s->total_amount, 2) }}</td>
                                    <td><span class="badge bg-success">{{ $s->payment_status }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.sales.show', $s) }}" class="btn btn-sm btn-outline-success">View</a>
                                        <a href="{{ route('admin.sales.edit', $s) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No sales invoices recorded.</td>
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
