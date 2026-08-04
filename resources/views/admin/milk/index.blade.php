@extends('layouts.app')
@section('title', 'Milk Production & Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-droplet-fill me-2"></i>Module 3 — Milk Management</h1>
            <p class="text-muted mb-0">Daily Morning & Evening milk production, Fat %, SNF %, quality testing, and rejection tracking.</p>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center bg-success bg-opacity-10">
                <div class="text-muted small">Total Production Today</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($todayTotal, 1) }} Liters</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Morning Shift</div>
                <div class="fs-4 fw-bold text-primary">{{ number_format($morningTotal, 1) }} L</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Evening Shift</div>
                <div class="fs-4 fw-bold text-info">{{ number_format($eveningTotal, 1) }} L</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Average Fat %</div>
                <div class="fs-4 fw-bold text-warning">{{ number_format($avgFat, 1) }} %</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Rejected Milk</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($rejectedTotal, 1) }} Liters</div>
            </div>
        </div>
    </div>

    <!-- Quick Entry & Production History -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-success"></i>Daily Milk Entry</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.milk.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Shift</label>
                            <select name="shift" class="form-select">
                                <option value="Morning">Morning Shift</option>
                                <option value="Evening">Evening Shift</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Animal (Optional)</label>
                            <select name="animal_id" class="form-select">
                                <option value="">-- Batch Entry (Entire Shed) --</option>
                                @foreach($animals as $a)
                                    <option value="{{ $a->id }}">{{ $a->tag_number }} - {{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Quantity (Liters) *</label>
                            <input type="number" step="0.1" name="quantity_liters" class="form-control" placeholder="e.g. 75.5" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Fat %</label>
                                <input type="number" step="0.1" name="fat_percentage" class="form-control" value="7.8">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">SNF %</label>
                                <input type="number" step="0.1" name="snf_percentage" class="form-control" value="9.0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Quality Grade</label>
                            <select name="quality_rating" class="form-select">
                                <option value="Grade A+">Grade A+ (Premium Fat > 8%)</option>
                                <option value="Grade A" selected>Grade A (Standard Fat 7-8%)</option>
                                <option value="Grade B">Grade B (Sub-standard)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-save me-1"></i> Save Milk Entry</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Today's Production Records</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Shift</th>
                                <th>Animal / Source</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Fat %</th>
                                <th class="text-end">SNF %</th>
                                <th>Quality</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entries as $e)
                                <tr>
                                    <td>
                                        <span class="badge {{ $e->shift === 'Morning' ? 'bg-primary' : 'bg-info text-dark' }}">
                                            {{ $e->shift }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">{{ $e->animal ? $e->animal->tag_number . ' (' . $e->animal->name . ')' : 'Batch Shed Total' }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($e->quantity_liters, 1) }} L</td>
                                    <td class="text-end">{{ number_format($e->fat_percentage, 1) }} %</td>
                                    <td class="text-end">{{ number_format($e->snf_percentage, 1) }} %</td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success">{{ $e->quality_rating }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No milk entries recorded for today.</td>
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
