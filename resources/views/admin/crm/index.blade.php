@extends('layouts.app')
@section('title', 'CRM & Customer Relationships')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-primary"><i class="bi bi-people-fill me-2"></i>Module 11 — CRM & Lead Management</h1>
            <p class="text-muted mb-0">Milk Buyers, Animal Buyers, Franchise Leads, Investors, Govt Officials & Vet Doctors.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Milk Buyers</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['total_buyers'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Franchise Leads</div>
                <div class="fs-3 fw-bold text-warning">{{ $summary['franchise_leads'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Investors</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['investors'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Total Business Value</div>
                <div class="fs-3 fw-bold text-success">₹{{ number_format($summary['total_business'], 0) }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Customer / Contact</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.crm.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Full Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Ramesh Patel" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="Milk Buyer">Milk Buyer</option>
                                <option value="Animal Buyer">Animal Buyer</option>
                                <option value="Franchise Lead">Franchise Lead</option>
                                <option value="Investor">Investor</option>
                                <option value="Government Official">Government Official</option>
                                <option value="Veterinary Doctor">Veterinary Doctor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="Active Customer">Active Customer</option>
                                <option value="Lead">Lead</option>
                                <option value="Contacted">Contacted</option>
                                <option value="Partner">Partner</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save me-1"></i> Save Contact</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-primary"></i>CRM Directory</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Phone</th>
                                <th>Business Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $c)
                                <tr>
                                    <td class="fw-bold">{{ $c->name }}</td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $c->category }}</span></td>
                                    <td>{{ $c->phone ?? '—' }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format($c->total_business_value, 0) }}</td>
                                    <td><span class="badge bg-success">{{ $c->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No CRM contacts registered.</td>
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
