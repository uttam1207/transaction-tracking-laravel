@extends('layouts.app')
@section('title', 'Animal — ' . $animal->tag_number)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-primary"><i class="bi bi-card-checklist me-2"></i>{{ $animal->tag_number }} — {{ $animal->name ?? 'Unnamed' }}</h1>
            <p class="text-muted mb-0">{{ $animal->breed }} &mdash; Shed: {{ $animal->shed_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.animals.edit', $animal) }}" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
            <form method="POST" action="{{ route('admin.animals.destroy', $animal) }}" onsubmit="return confirm('Remove this animal from the register?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Animal Details</h6>
                <dl class="row mb-0">
                    <dt class="col-6 text-muted">Tag Number</dt><dd class="col-6 fw-bold text-primary">{{ $animal->tag_number }}</dd>
                    <dt class="col-6 text-muted">Name</dt><dd class="col-6">{{ $animal->name ?? '—' }}</dd>
                    <dt class="col-6 text-muted">Breed</dt><dd class="col-6">{{ $animal->breed }}</dd>
                    <dt class="col-6 text-muted">Date of Birth</dt><dd class="col-6">{{ $animal->dob ? $animal->dob->format('d M Y') : '—' }}</dd>
                    <dt class="col-6 text-muted">Lactation No.</dt><dd class="col-6">{{ $animal->lactation_number }}</dd>
                    <dt class="col-6 text-muted">Current Weight</dt><dd class="col-6">{{ $animal->current_weight ? $animal->current_weight . ' kg' : '—' }}</dd>
                    <dt class="col-6 text-muted">Shed</dt><dd class="col-6">{{ $animal->shed_number }}</dd>
                    <dt class="col-6 text-muted">Health Status</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $animal->health_status === 'Healthy' ? 'success' : ($animal->health_status === 'Sick' ? 'danger' : 'warning') }}">{{ $animal->health_status }}</span>
                    </dd>
                    <dt class="col-6 text-muted">Pregnancy Status</dt>
                    <dd class="col-6"><span class="badge bg-info text-dark">{{ $animal->pregnancy_status }}</span></dd>
                    <dt class="col-6 text-muted">Status</dt>
                    <dd class="col-6"><span class="badge bg-{{ $animal->status === 'Active' ? 'success' : 'secondary' }}">{{ $animal->status }}</span></dd>
                </dl>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h6 class="fw-bold text-muted mb-3 text-uppercase" style="font-size:.75rem;letter-spacing:.08em;">Purchase Info</h6>
                <dl class="row mb-0">
                    <dt class="col-6 text-muted">Purchase Date</dt><dd class="col-6">{{ $animal->purchase_date ? $animal->purchase_date->format('d M Y') : '—' }}</dd>
                    <dt class="col-6 text-muted">Purchase Cost</dt><dd class="col-6">{{ $animal->purchase_cost ? '₹' . number_format($animal->purchase_cost, 2) : '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-2 text-success"></i>Log Action</h6>
                <form action="{{ route('admin.animals.actions.store', $animal) }}" method="POST">
                    @csrf
                    <div class="row g-2">
                        <div class="col-md-4">
                            <select name="action_type" class="form-select form-select-sm" required>
                                <option value="">Action Type</option>
                                @foreach(['Vaccination','Deworming','Heat Detection','AI','Pregnancy Check','Calving','Dry Off','Sale','Death'] as $act)
                                    <option value="{{ $act }}">{{ $act }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="action_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="cost" step="0.01" min="0" class="form-control form-control-sm" placeholder="Cost ₹">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes">
                        </div>
                    </div>
                    <button class="btn btn-success btn-sm mt-2 px-4"><i class="bi bi-save me-1"></i>Save Action</button>
                </form>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Action History</h6>
                @forelse($animal->actions->sortByDesc('action_date') as $action)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <span class="badge bg-primary me-2">{{ $action->action_type }}</span>
                            {{ $action->action_date->format('d M Y') }}
                            @if($action->notes)<small class="text-muted ms-2">— {{ $action->notes }}</small>@endif
                        </div>
                        @if($action->cost)<span class="text-success fw-bold small">₹{{ number_format($action->cost, 0) }}</span>@endif
                    </div>
                @empty
                    <p class="text-muted mb-0 small">No actions logged yet.</p>
                @endforelse
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-heart-pulse me-2 text-danger"></i>Recent Health Records</h6>
                @forelse($animal->healthRecords->sortByDesc('date')->take(5) as $h)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div>
                            <span class="badge bg-danger bg-opacity-10 text-danger me-2">{{ $h->record_type }}</span>
                            {{ $h->date->format('d M Y') }}
                            @if($h->disease_symptoms)<small class="text-muted ms-1">— {{ $h->disease_symptoms }}</small>@endif
                        </div>
                        @if($h->cost)<span class="text-danger fw-bold small">₹{{ number_format($h->cost, 0) }}</span>@endif
                    </div>
                @empty
                    <p class="text-muted mb-0 small">No health records.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
