@extends('layouts.app')
@section('title', 'Franchise Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-info"><i class="bi bi-shop me-2"></i>Module 12 — Franchise Management</h1>
            <p class="text-muted mb-0">Franchise applications, agreements, investment tracking, milk collection & royalty fees.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Active Franchises</div>
                <div class="fs-3 fw-bold text-info">{{ $summary['total_active'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Total Investment Capital</div>
                <div class="fs-3 fw-bold text-success">₹{{ number_format($summary['total_investment'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Milk Collected via Outlets</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($summary['total_milk_collected'], 0) }} L</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-info"></i>Register Franchise</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.franchise.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Franchise Code *</label>
                            <input type="text" name="franchise_code" class="form-control" placeholder="e.g. ASD-FRAN-02" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Owner Name *</label>
                            <input type="text" name="owner_name" class="form-control" placeholder="e.g. Suresh Verma" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Location *</label>
                            <input type="text" name="location" class="form-control" placeholder="e.g. Damoh Station Road" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Contact Number *</label>
                            <input type="text" name="contact_number" class="form-control" placeholder="+91 98260 00000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Agreement Date *</label>
                            <input type="date" name="agreement_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Investment Amount (₹) *</label>
                            <input type="number" step="0.01" min="0" name="investment_amount" class="form-control" value="500000" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Royalty Percentage (%) *</label>
                            <input type="number" step="0.1" min="0" name="royalty_percentage" class="form-control" value="5.0" required>
                        </div>
                        <button type="submit" class="btn btn-info text-white w-100"><i class="bi bi-save me-1"></i> Register Franchise</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shop-window me-2 text-primary"></i>Franchise Outlets</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Owner</th>
                                <th>Location</th>
                                <th class="text-end">Investment</th>
                                <th class="text-end">Royalty</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($franchises as $f)
                                <tr>
                                    <td><strong class="text-info">{{ $f->franchise_code }}</strong></td>
                                    <td class="fw-bold">{{ $f->owner_name }}</td>
                                    <td>{{ $f->location }}</td>
                                    <td class="text-end fw-bold text-success">₹{{ number_format($f->investment_amount, 0) }}</td>
                                    <td class="text-end">{{ number_format($f->royalty_percentage, 1) }} %</td>
                                    <td><span class="badge bg-success">{{ $f->status }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.franchise.show', $f) }}" class="btn btn-sm btn-outline-warning">View</a>
                                        <a href="{{ route('admin.franchise.edit', $f) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No franchises registered.</td>
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
