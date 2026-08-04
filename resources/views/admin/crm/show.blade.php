@extends('layouts.app')
@section('title', 'Customer — ' . $crmCustomer->name)

@section('content')
<div class="container py-3" style="max-width:800px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-primary"><i class="bi bi-person-lines-fill me-2"></i>{{ $crmCustomer->name }}</h1>
            <p class="text-muted mb-0">{{ $crmCustomer->category }} — {{ $crmCustomer->status }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.crm.edit', $crmCustomer) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.crm.destroy', $crmCustomer) }}" onsubmit="return confirm('Delete this customer?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.crm.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Contact Details</h6>
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Name</dt><dd class="col-7 fw-bold">{{ $crmCustomer->name }}</dd>
                    <dt class="col-5 text-muted">Category</dt><dd class="col-7"><span class="badge bg-primary bg-opacity-10 text-primary border">{{ $crmCustomer->category }}</span></dd>
                    <dt class="col-5 text-muted">Status</dt><dd class="col-7"><span class="badge bg-{{ $crmCustomer->status === 'Active Customer' ? 'success' : ($crmCustomer->status === 'Inactive' ? 'secondary' : 'warning') }}">{{ $crmCustomer->status }}</span></dd>
                    <dt class="col-5 text-muted">Phone</dt><dd class="col-7">{{ $crmCustomer->phone ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Email</dt><dd class="col-7">{{ $crmCustomer->email ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Address</dt><dd class="col-7">{{ $crmCustomer->address ?? '—' }}</dd>
                    <dt class="col-5 text-muted">Total Business</dt><dd class="col-7 fw-bold text-success">{{ $crmCustomer->total_business_value ? '₹' . number_format($crmCustomer->total_business_value, 2) : '—' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Sales Orders</h6>
                @forelse($crmCustomer->salesOrders->sortByDesc('sale_date')->take(5) as $order)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <div>
                            <span class="small fw-bold">{{ $order->invoice_number }}</span><br>
                            <span class="small text-muted">{{ $order->item_type }} — {{ $order->sale_date->format('d M Y') }}</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-success">₹{{ number_format($order->total_amount, 2) }}</span><br>
                            <span class="badge bg-{{ $order->payment_status === 'Paid' ? 'success' : 'warning' }} small">{{ $order->payment_status }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No sales orders yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
