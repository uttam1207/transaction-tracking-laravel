@extends('layouts.app')
@section('title', 'Edit Animal — ' . $animal->tag_number)

@section('content')
<div class="container py-3" style="max-width:800px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.animals.show', $animal) }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="h3 mb-0 fw-bold text-primary"><i class="bi bi-pencil me-2"></i>Edit — {{ $animal->tag_number }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.animals.update', $animal) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ear Tag Number <span class="text-danger">*</span></label>
                        <input type="text" name="tag_number" class="form-control @error('tag_number') is-invalid @enderror" value="{{ old('tag_number', $animal->tag_number) }}" required>
                        @error('tag_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Animal Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $animal->name) }}" placeholder="e.g. Laxmi">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Breed <span class="text-danger">*</span></label>
                        <select name="breed" class="form-select" required>
                            @foreach(['Murrah','Bhadawari','Jafarabadi','Nili-Ravi','Crossbred'] as $b)
                                <option value="{{ $b }}" @selected(old('breed', $animal->breed) === $b)>{{ $b }} Buffalo</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Lactation Number <span class="text-danger">*</span></label>
                        <input type="number" name="lactation_number" class="form-control" value="{{ old('lactation_number', $animal->lactation_number) }}" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="{{ old('dob', $animal->dob?->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Purchase Date</label>
                        <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', $animal->purchase_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Purchase Cost (₹)</label>
                        <input type="number" step="0.01" min="0" name="purchase_cost" class="form-control" value="{{ old('purchase_cost', $animal->purchase_cost) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Current Weight (kg)</label>
                        <input type="number" step="0.1" min="0" name="current_weight" class="form-control" value="{{ old('current_weight', $animal->current_weight) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Health Status <span class="text-danger">*</span></label>
                        <select name="health_status" class="form-select" required>
                            @foreach(['Healthy','Sick','Under Treatment'] as $h)
                                <option value="{{ $h }}" @selected(old('health_status', $animal->health_status) === $h)>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Pregnancy Status <span class="text-danger">*</span></label>
                        <select name="pregnancy_status" class="form-select" required>
                            @foreach(['Open','Inseminated','Pregnant','Dry'] as $p)
                                <option value="{{ $p }}" @selected(old('pregnancy_status', $animal->pregnancy_status) === $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Shed Number <span class="text-danger">*</span></label>
                        <input type="text" name="shed_number" class="form-control" value="{{ old('shed_number', $animal->shed_number) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['Active','Sold','Deceased'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $animal->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.animals.show', $animal) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update Animal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
