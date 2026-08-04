@extends('layouts.app')
@section('title', 'Farm & Land Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-tree-fill me-2"></i>Module 7 — Farm & Land Management</h1>
            <p class="text-muted mb-0">Land records, Napier grass plantation, crop yield, fertilizer, diesel & water consumption.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Total Crop Yield</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($summary['total_yield'], 0) }} kg</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Diesel Used</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($summary['total_diesel'], 1) }} L</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Water Usage</div>
                <div class="fs-3 fw-bold text-info">{{ number_format($summary['total_water'], 0) }} L</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Active Farm Plots</div>
                <div class="fs-3 fw-bold text-primary">{{ $summary['active_plots'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Log Farm Harvest / Input</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.farm.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Plot Name *</label>
                            <input type="text" name="plot_name" class="form-control" value="Plot A - Anoo Village" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Crop Type *</label>
                            <input type="text" name="crop_type" class="form-control" value="Napier Grass" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Harvest Date</label>
                            <input type="date" name="harvest_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Yield (kg)</label>
                            <input type="number" step="0.1" name="yield_kg" class="form-control" placeholder="e.g. 2500">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Diesel Consumed (Liters)</label>
                            <input type="number" step="0.1" name="diesel_liters" class="form-control" placeholder="e.g. 15">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Water Usage (Liters)</label>
                            <input type="number" step="1" name="water_usage_liters" class="form-control" placeholder="e.g. 5000">
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-save me-1"></i> Save Farm Log</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Farm Activity Logs</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Plot Name</th>
                                <th>Crop</th>
                                <th class="text-end">Yield (kg)</th>
                                <th class="text-end">Diesel (L)</th>
                                <th class="text-end">Water (L)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $f)
                                <tr>
                                    <td class="fw-bold">{{ $f->plot_name }}</td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success">{{ $f->crop_type }}</span></td>
                                    <td class="text-end fw-bold text-success">{{ number_format($f->yield_kg, 1) }} kg</td>
                                    <td class="text-end">{{ number_format($f->diesel_liters, 1) }} L</td>
                                    <td class="text-end">{{ number_format($f->water_usage_liters, 0) }} L</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No farm records logged yet.</td>
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
