@extends('layouts.app')
@section('title', 'Franchise — ' . $franchise->franchise_code)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-warning"><i class="bi bi-shop me-2"></i>{{ $franchise->franchise_code }}</h1>
            <p class="text-muted mb-0">{{ $franchise->owner_name }} — {{ $franchise->location }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.franchise.edit', $franchise) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.franchise.destroy', $franchise) }}" onsubmit="return confirm('Delete this franchise record?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.franchise.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Franchise Code</dt><dd class="col-7 fw-bold font-monospace">{{ $franchise->franchise_code }}</dd>
            <dt class="col-5 text-muted">Owner Name</dt><dd class="col-7 fw-bold">{{ $franchise->owner_name }}</dd>
            <dt class="col-5 text-muted">Location</dt><dd class="col-7">{{ $franchise->location }}</dd>
            <dt class="col-5 text-muted">Contact</dt><dd class="col-7">{{ $franchise->contact_number }}</dd>
            <dt class="col-5 text-muted">Agreement Date</dt><dd class="col-7">{{ $franchise->agreement_date->format('d M Y') }}</dd>
            <dt class="col-5 text-muted">Investment</dt><dd class="col-7 fw-bold text-success">₹{{ number_format($franchise->investment_amount, 2) }}</dd>
            <dt class="col-5 text-muted">Royalty %</dt><dd class="col-7">{{ $franchise->royalty_percentage }} %</dd>
            <dt class="col-5 text-muted">Milk Collected</dt><dd class="col-7">{{ $franchise->milk_collected_liters ? number_format($franchise->milk_collected_liters, 1) . ' L' : '—' }}</dd>
            <dt class="col-5 text-muted">Status</dt><dd class="col-7"><span class="badge bg-{{ $franchise->status === 'Active' ? 'success' : 'secondary' }}">{{ $franchise->status }}</span></dd>
            <dt class="col-5 text-muted">Recorded At</dt><dd class="col-7 small text-muted">{{ $franchise->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
