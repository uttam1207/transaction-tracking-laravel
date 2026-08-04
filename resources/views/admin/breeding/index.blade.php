@extends('layouts.app')
@section('title', 'Breeding Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-info"><i class="bi bi-heart-pulse-fill me-2"></i>Module 4 — Breeding Management</h1>
            <p class="text-muted mb-0">Heat detection, Artificial Insemination (AI), bull semen inventory & pregnancy checks.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">AI Done (Pending Check)</div>
                <div class="fs-3 fw-bold text-warning">{{ $summary['inseminated'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Confirmed Pregnant</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['pregnant'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Expected Calving This Month</div>
                <div class="fs-3 fw-bold text-info">{{ $summary['expected_calving_month'] }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Repeat Breeders</div>
                <div class="fs-3 fw-bold text-danger">{{ $summary['repeat_breeders'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-info"></i>Log Breeding / AI Event</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.breeding.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Animal *</label>
                            <select name="animal_id" class="form-select" required>
                                <option value="">-- Choose Animal --</option>
                                @foreach($animals as $a)
                                    <option value="{{ $a->id }}">{{ $a->tag_number }} - {{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Heat Detection Date *</label>
                            <input type="date" name="heat_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">AI Date</label>
                            <input type="date" name="ai_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Bull Semen Code / Batch</label>
                            <input type="text" name="bull_semen_code" class="form-control" placeholder="e.g. SEM-MUR-884">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="Heat Detected">Heat Detected</option>
                                <option value="AI Done" selected>AI Done</option>
                                <option value="Confirmed Pregnant">Confirmed Pregnant</option>
                                <option value="Calved">Calved</option>
                                <option value="Repeat Breeder">Repeat Breeder</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-info text-white w-100"><i class="bi bi-save me-1"></i> Save Breeding Record</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Breeding Records History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Animal</th>
                                <th>Heat Date</th>
                                <th>AI Date</th>
                                <th>Semen Code</th>
                                <th>Expected Calving</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $r)
                                <tr>
                                    <td class="fw-bold">{{ $r->animal?->tag_number }} ({{ $r->animal?->name }})</td>
                                    <td>{{ $r->heat_date?->format('d-M-Y') }}</td>
                                    <td>{{ $r->ai_date ? $r->ai_date->format('d-M-Y') : '—' }}</td>
                                    <td><code>{{ $r->bull_semen_code ?? '—' }}</code></td>
                                    <td>{{ $r->expected_calving_date ? $r->expected_calving_date->format('d-M-Y') : '—' }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $r->status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No breeding records found.</td>
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
