@extends('layouts.app')
@section('title', 'Health & Veterinary Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-danger"><i class="bi bi-hospital-fill me-2"></i>Module 5 — Health Management</h1>
            <p class="text-muted mb-0">Vaccination schedules, medicine usage, vet doctor visits, body temperature & treatment history.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Sick Animals</div>
                <div class="fs-3 fw-bold text-danger">{{ $summary['sick_animals'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Under Treatment</div>
                <div class="fs-3 fw-bold text-warning">{{ $summary['under_treatment'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small">Vaccinations This Month</div>
                <div class="fs-3 fw-bold text-success">{{ $summary['recent_vaccinations'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-danger"></i>Log Health Event</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.health.store') }}" method="POST">
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
                            <label class="form-label small fw-bold">Record Type *</label>
                            <select name="record_type" class="form-select" required>
                                <option value="Vaccination">Vaccination</option>
                                <option value="Deworming">Deworming</option>
                                <option value="Treatment">Treatment</option>
                                <option value="Doctor Visit">Doctor Visit</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Date *</label>
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Disease Symptoms / Notes</label>
                            <input type="text" name="disease_symptoms" class="form-control" placeholder="e.g. Fever, FMD Vaccine">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Treatment Given / Medicine</label>
                            <input type="text" name="treatment_given" class="form-control" placeholder="e.g. Albendazole 100ml">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Veterinary Doctor Name</label>
                            <input type="text" name="vet_doctor_name" class="form-control" placeholder="e.g. Dr. Verma">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Cost (₹)</label>
                            <input type="number" step="0.01" min="0" name="cost" class="form-control" placeholder="e.g. 250">
                        </div>
                        <button type="submit" class="btn btn-danger w-100"><i class="bi bi-save me-1"></i> Save Health Log</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-journal-medical me-2 text-primary"></i>Health History</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Animal</th>
                                <th>Type</th>
                                <th>Symptoms / Vaccine</th>
                                <th>Doctor</th>
                                <th class="text-end">Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $h)
                                <tr>
                                    <td>{{ $h->date?->format('d-M-Y') }}</td>
                                    <td class="fw-bold">{{ $h->animal?->tag_number }}</td>
                                    <td><span class="badge bg-danger bg-opacity-10 text-danger">{{ $h->record_type }}</span></td>
                                    <td>{{ $h->disease_symptoms ?? $h->treatment_given }}</td>
                                    <td>{{ $h->vet_doctor_name ?? '—' }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($h->cost, 2) }}</td>
                                    <td>
                                        <a href="{{ route('admin.health.show', $h) }}" class="btn btn-sm btn-outline-danger">View</a>
                                        <a href="{{ route('admin.health.edit', $h) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No health records logged yet.</td>
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
