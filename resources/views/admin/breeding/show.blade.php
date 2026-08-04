@extends('layouts.app')
@section('title', 'Breeding Record #' . $breedingRecord->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-info"><i class="bi bi-diagram-3 me-2"></i>Breeding Record #{{ $breedingRecord->id }}</h1>
            <p class="text-muted mb-0">{{ $breedingRecord->animal->tag_number }} — {{ $breedingRecord->status }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.breeding.edit', $breedingRecord) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.breeding.destroy', $breedingRecord) }}" onsubmit="return confirm('Delete this breeding record?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.breeding.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Animal Tag</dt>
            <dd class="col-7 fw-bold text-primary">{{ $breedingRecord->animal->tag_number }}{{ $breedingRecord->animal->name ? ' (' . $breedingRecord->animal->name . ')' : '' }}</dd>

            <dt class="col-5 text-muted">Status</dt>
            <dd class="col-7"><span class="badge bg-info text-dark">{{ $breedingRecord->status }}</span></dd>

            <dt class="col-5 text-muted">Heat Detection Date</dt>
            <dd class="col-7">{{ $breedingRecord->heat_date->format('d M Y') }}</dd>

            <dt class="col-5 text-muted">AI Date</dt>
            <dd class="col-7">{{ $breedingRecord->ai_date ? $breedingRecord->ai_date->format('d M Y') : '—' }}</dd>

            <dt class="col-5 text-muted">Bull Semen Code</dt>
            <dd class="col-7 font-monospace">{{ $breedingRecord->bull_semen_code ?? '—' }}</dd>

            <dt class="col-5 text-muted">Pregnancy Check</dt>
            <dd class="col-7">{{ $breedingRecord->pregnancy_check_date ? $breedingRecord->pregnancy_check_date->format('d M Y') : '—' }}</dd>

            <dt class="col-5 text-muted">Pregnant</dt>
            <dd class="col-7">
                @if($breedingRecord->is_pregnant === null) <span class="text-muted">—</span>
                @elseif($breedingRecord->is_pregnant) <span class="badge bg-success">Yes</span>
                @else <span class="badge bg-secondary">No</span>
                @endif
            </dd>

            <dt class="col-5 text-muted">Expected Calving</dt>
            <dd class="col-7">{{ $breedingRecord->expected_calving_date ? $breedingRecord->expected_calving_date->format('d M Y') : '—' }}</dd>

            <dt class="col-5 text-muted">Actual Calving</dt>
            <dd class="col-7">{{ $breedingRecord->actual_calving_date ? $breedingRecord->actual_calving_date->format('d M Y') : '—' }}</dd>

            <dt class="col-5 text-muted">Calf Tag Number</dt>
            <dd class="col-7 font-monospace">{{ $breedingRecord->calf_tag_number ?? '—' }}</dd>

            <dt class="col-5 text-muted">Recorded At</dt>
            <dd class="col-7 small text-muted">{{ $breedingRecord->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
