@extends('layouts.app')
@section('title', 'Compliance & Government Document Center')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-secondary"><i class="bi bi-shield-check me-2"></i>Module 16 — Compliance & Certificates</h1>
            <p class="text-muted mb-0">FSSAI license, Animal Insurance, Vaccination certificates, Bank Loan documents & Audit files.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Active Documents</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['active_docs'] }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Expiring / Action Required (30 Days)</div>
                <div class="fs-3 fw-bold text-warning">{{ $summary['expiring_soon'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-secondary"></i>Log Compliance Document</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.compliance.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Document Title *</label>
                            <input type="text" name="document_title" class="form-control" placeholder="e.g. FSSAI Dairy License" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="FSSAI">FSSAI License</option>
                                <option value="Animal Insurance">Animal Insurance</option>
                                <option value="Vaccination Certificates">Vaccination Certificates</option>
                                <option value="Government Licenses">Government Licenses</option>
                                <option value="Bank Loan Documents">Bank Loan Documents</option>
                                <option value="Land Records">Land Records</option>
                                <option value="Audit Files">Audit Files</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Document Number</label>
                            <input type="text" name="document_number" class="form-control" placeholder="e.g. FSSAI-11826000192">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control" value="{{ now()->addYear()->toDateString() }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="Active">Active</option>
                                <option value="Expiring Soon">Expiring Soon</option>
                                <option value="Expired">Expired</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-save me-1"></i> Save Compliance Record</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-folder-check me-2 text-primary"></i>Compliance Repository</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Number</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $d)
                                <tr>
                                    <td class="fw-bold">{{ $d->document_title }}</td>
                                    <td><span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $d->category }}</span></td>
                                    <td><code>{{ $d->document_number ?? '—' }}</code></td>
                                    <td>{{ $d->expiry_date ? $d->expiry_date->format('d-M-Y') : '—' }}</td>
                                    <td><span class="badge bg-success">{{ $d->status }}</span></td>
                                    <td>
                                        <a href="{{ route('admin.compliance.show', $d) }}" class="btn btn-sm btn-outline-success">View</a>
                                        <a href="{{ route('admin.compliance.edit', $d) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No compliance documents recorded.</td>
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
