@extends('layouts.app')
@section('title', 'Health Record #' . $healthRecord->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-danger"><i class="bi bi-heart-pulse me-2"></i>Health Record #{{ $healthRecord->id }}</h1>
            <p class="text-muted mb-0">{{ $healthRecord->animal->tag_number }} — {{ $healthRecord->record_type }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.health.edit', $healthRecord) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.health.destroy', $healthRecord) }}" onsubmit="return confirm('Delete this health record?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.health.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Animal</dt>
            <dd class="col-7 fw-bold text-primary">
                <a href="{{ route('admin.animals.show', $healthRecord->animal) }}">
                    {{ $healthRecord->animal->tag_number }}{{ $healthRecord->animal->name ? ' (' . $healthRecord->animal->name . ')' : '' }}
                </a>
            </dd>

            <dt class="col-5 text-muted">Record Type</dt>
            <dd class="col-7"><span class="badge bg-danger bg-opacity-10 text-danger border">{{ $healthRecord->record_type }}</span></dd>

            <dt class="col-5 text-muted">Date</dt>
            <dd class="col-7 fw-bold">{{ $healthRecord->date->format('d F Y') }}</dd>

            <dt class="col-5 text-muted">Disease / Symptoms</dt>
            <dd class="col-7">{{ $healthRecord->disease_symptoms ?? '—' }}</dd>

            <dt class="col-5 text-muted">Treatment Given</dt>
            <dd class="col-7">{{ $healthRecord->treatment_given ?? '—' }}</dd>

            <dt class="col-5 text-muted">Medicine Used</dt>
            <dd class="col-7">{{ $healthRecord->medicine_used ?? '—' }}</dd>

            <dt class="col-5 text-muted">Vet / Doctor</dt>
            <dd class="col-7">{{ $healthRecord->vet_doctor_name ?? '—' }}</dd>

            <dt class="col-5 text-muted">Body Temperature</dt>
            <dd class="col-7">{{ $healthRecord->body_temp ? $healthRecord->body_temp . ' °F' : '—' }}</dd>

            <dt class="col-5 text-muted">Cost</dt>
            <dd class="col-7 fw-bold text-danger">{{ $healthRecord->cost ? '₹' . number_format($healthRecord->cost, 2) : '—' }}</dd>

            <dt class="col-5 text-muted">Status</dt>
            <dd class="col-7">{{ $healthRecord->status ?? '—' }}</dd>

            <dt class="col-5 text-muted">Recorded At</dt>
            <dd class="col-7 small text-muted">{{ $healthRecord->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
