@extends('layouts.app')
@section('title', 'Machinery & Equipment Maintenance')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-warning"><i class="bi bi-tools me-2"></i>Module 15 — Maintenance Tracking</h1>
            <p class="text-muted mb-0">Milking machines, generators, pumps, tractors, service history & breakdown logs.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Total Maintenance Expenditure</div>
                <div class="fs-3 fw-bold text-danger">₹{{ number_format($summary['total_cost'], 0) }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Services Due Soon (14 Days)</div>
                <div class="fs-3 fw-bold text-warning">{{ $summary['due_soon'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-warning"></i>Log Machine Maintenance</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.maintenance.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Machine Name *</label>
                            <input type="text" name="machine_name" class="form-control" placeholder="e.g. Milking Machine / Tractor" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Maintenance Type *</label>
                            <select name="maintenance_type" class="form-select" required>
                                <option value="Preventive Schedule">Preventive Schedule</option>
                                <option value="Service History">Service History</option>
                                <option value="Breakdown Log">Breakdown Log</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Service Date *</label>
                            <input type="date" name="service_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cost (₹)</label>
                            <input type="number" step="0.01" min="0" name="cost" class="form-control" placeholder="e.g. 1200">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Serviced By</label>
                            <input type="text" name="serviced_by" class="form-control" placeholder="e.g. Technician Name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Next Service Due Date</label>
                            <input type="date" name="next_service_due" class="form-control" value="{{ now()->addDays(90)->toDateString() }}">
                        </div>
                        <button type="submit" class="btn btn-warning text-dark w-100"><i class="bi bi-save me-1"></i> Save Maintenance Log</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Service History & Maintenance Logs</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Machine</th>
                                <th>Type</th>
                                <th>Service Date</th>
                                <th class="text-end">Cost</th>
                                <th>Next Service Due</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $l)
                                <tr>
                                    <td class="fw-bold">{{ $l->machine_name }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $l->maintenance_type }}</span></td>
                                    <td>{{ $l->service_date?->format('d-M-Y') }}</td>
                                    <td class="text-end fw-bold text-danger">₹{{ number_format($l->cost, 2) }}</td>
                                    <td>{{ $l->next_service_due ? $l->next_service_due->format('d-M-Y') : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No maintenance logs recorded.</td>
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
