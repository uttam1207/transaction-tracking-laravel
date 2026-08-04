@extends('layouts.app')
@section('title', 'Farm Record #' . $farmRecord->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-tree me-2"></i>Farm Record — {{ $farmRecord->plot_name }}</h1>
            <p class="text-muted mb-0">{{ $farmRecord->crop_type }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.farm.edit', $farmRecord) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.farm.destroy', $farmRecord) }}" onsubmit="return confirm('Delete this farm record?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.farm.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Plot Name</dt><dd class="col-7 fw-bold">{{ $farmRecord->plot_name }}</dd>
            <dt class="col-5 text-muted">Crop Type</dt><dd class="col-7">{{ $farmRecord->crop_type }}</dd>
            <dt class="col-5 text-muted">Plantation Date</dt><dd class="col-7">{{ $farmRecord->plantation_date ? $farmRecord->plantation_date->format('d M Y') : '—' }}</dd>
            <dt class="col-5 text-muted">Fertilizer Used</dt><dd class="col-7">{{ $farmRecord->fertilizer_used ?? '—' }}</dd>
            <dt class="col-5 text-muted">Harvest Date</dt><dd class="col-7">{{ $farmRecord->harvest_date ? $farmRecord->harvest_date->format('d M Y') : '—' }}</dd>
            <dt class="col-5 text-muted">Yield</dt><dd class="col-7 fw-bold text-success">{{ $farmRecord->yield_kg ? number_format($farmRecord->yield_kg, 1) . ' kg' : '—' }}</dd>
            <dt class="col-5 text-muted">Diesel Used</dt><dd class="col-7">{{ $farmRecord->diesel_liters ? $farmRecord->diesel_liters . ' L' : '—' }}</dd>
            <dt class="col-5 text-muted">Water Usage</dt><dd class="col-7">{{ $farmRecord->water_usage_liters ? number_format($farmRecord->water_usage_liters) . ' L' : '—' }}</dd>
            <dt class="col-5 text-muted">Recorded At</dt><dd class="col-7 small text-muted">{{ $farmRecord->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
