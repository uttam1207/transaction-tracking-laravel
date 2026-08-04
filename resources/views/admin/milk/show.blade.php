@extends('layouts.app')
@section('title', 'Milk Entry #' . $milkEntry->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-droplet-fill me-2"></i>Milk Entry #{{ $milkEntry->id }}</h1>
            <p class="text-muted mb-0">{{ $milkEntry->date->format('d M Y') }} — {{ $milkEntry->shift }} Shift</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.milk.edit', $milkEntry) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.milk.destroy', $milkEntry) }}" onsubmit="return confirm('Delete this milk entry?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Delete</button>
            </form>
            <a href="{{ route('admin.milk.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 p-4">
        <dl class="row mb-0">
            <dt class="col-5 text-muted">Date</dt>
            <dd class="col-7 fw-bold">{{ $milkEntry->date->format('d F Y') }}</dd>

            <dt class="col-5 text-muted">Shift</dt>
            <dd class="col-7">
                <span class="badge {{ $milkEntry->shift === 'Morning' ? 'bg-primary' : 'bg-info text-dark' }}">{{ $milkEntry->shift }}</span>
            </dd>

            <dt class="col-5 text-muted">Animal / Source</dt>
            <dd class="col-7 fw-bold">
                {{ $milkEntry->animal ? $milkEntry->animal->tag_number . ' (' . $milkEntry->animal->name . ')' : 'Batch Shed Total' }}
            </dd>

            <dt class="col-5 text-muted">Quantity</dt>
            <dd class="col-7 fw-bold text-success fs-5">{{ number_format($milkEntry->quantity_liters, 1) }} Liters</dd>

            <dt class="col-5 text-muted">Fat %</dt>
            <dd class="col-7">{{ number_format($milkEntry->fat_percentage, 1) }} %</dd>

            <dt class="col-5 text-muted">SNF %</dt>
            <dd class="col-7">{{ number_format($milkEntry->snf_percentage, 1) }} %</dd>

            <dt class="col-5 text-muted">Quality Grade</dt>
            <dd class="col-7"><span class="badge bg-success bg-opacity-10 text-success">{{ $milkEntry->quality_rating }}</span></dd>

            <dt class="col-5 text-muted">Rejected Liters</dt>
            <dd class="col-7 text-danger fw-bold">{{ number_format($milkEntry->rejected_liters ?? 0, 1) }} L</dd>

            <dt class="col-5 text-muted">Recorded At</dt>
            <dd class="col-7 small text-muted">{{ $milkEntry->created_at->format('d M Y H:i') }}</dd>
        </dl>
    </div>
</div>
@endsection
