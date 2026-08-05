@extends('layouts.app')
@section('title', 'Register New Animal')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.animals.index') }}">Animals</a></li>
    <li class="breadcrumb-item active">Register Animal</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Register New Animal</h4>
            <p>Add a new buffalo or calf to the AS Dairy herd register</p>
        </div>
        <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Animals
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass p-4">

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.animals.store') }}" method="POST">
                @csrf

                {{-- Section A: Identity --}}
                <div class="mb-4">
                    <h6 class="form-section-label">A — Animal Identity</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ear Tag Number <span class="text-danger">*</span></label>
                            <input type="text" name="tag_number"
                                class="form-control @error('tag_number') is-invalid @enderror"
                                placeholder="e.g. ASD-BUF-011"
                                value="{{ old('tag_number') }}" required>
                            @error('tag_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Animal Name</label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="e.g. Laxmi"
                                value="{{ old('name') }}">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Breed <span class="text-danger">*</span></label>
                            <select name="breed" class="form-select @error('breed') is-invalid @enderror" required>
                                <option value="">— Select Breed —</option>
                                @foreach($breeds->groupBy('animal_type') as $type => $typeBreeds)
                                    <optgroup label="{{ $type }}">
                                        @foreach($typeBreeds as $b)
                                            <option value="{{ $b->name }}" @selected(old('breed')===$b->name)>{{ $b->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('breed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="mt-1" style="font-size:.72rem;color:#6b7280;">
                                <a href="{{ route('admin.breeds.index') }}" target="_blank" class="text-primary">
                                    <i class="bi bi-plus-circle me-1"></i>Manage breeds
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lactation Number <span class="text-danger">*</span></label>
                            <input type="number" name="lactation_number"
                                class="form-control @error('lactation_number') is-invalid @enderror"
                                value="{{ old('lactation_number', 0) }}" min="0" required>
                            @error('lactation_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <hr class="my-3 opacity-25">

                {{-- Section B: Physical & Status --}}
                <div class="mb-4">
                    <h6 class="form-section-label">B — Physical & Status</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Health Status <span class="text-danger">*</span></label>
                            <select name="health_status" class="form-select @error('health_status') is-invalid @enderror">
                                @foreach(['Healthy','Sick','Under Treatment'] as $h)
                                    <option value="{{ $h }}" @selected(old('health_status','Healthy')===$h)>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('health_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Pregnancy Status <span class="text-danger">*</span></label>
                            <select name="pregnancy_status" class="form-select @error('pregnancy_status') is-invalid @enderror">
                                @foreach(['Open','Inseminated','Pregnant','Dry'] as $p)
                                    <option value="{{ $p }}" @selected(old('pregnancy_status','Open')===$p)>{{ $p }}</option>
                                @endforeach
                            </select>
                            @error('pregnancy_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Current Weight (kg)</label>
                            <input type="number" step="0.1" min="0" name="current_weight"
                                class="form-control @error('current_weight') is-invalid @enderror"
                                placeholder="e.g. 520"
                                value="{{ old('current_weight') }}">
                            @error('current_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Shed Number <span class="text-danger">*</span></label>
                            <input type="text" name="shed_number"
                                class="form-control @error('shed_number') is-invalid @enderror"
                                value="{{ old('shed_number','Shed No. 1') }}" required>
                            @error('shed_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" name="owner_name"
                                class="form-control @error('owner_name') is-invalid @enderror"
                                value="{{ old('owner_name','ASDairy') }}" required>
                            @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <hr class="my-3 opacity-25">

                {{-- Section C: Purchase Details --}}
                <div class="mb-4">
                    <h6 class="form-section-label">C — Purchase Details (Optional)</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" name="dob"
                                class="form-control @error('dob') is-invalid @enderror"
                                value="{{ old('dob') }}">
                            @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Date</label>
                            <input type="date" name="purchase_date"
                                class="form-control @error('purchase_date') is-invalid @enderror"
                                value="{{ old('purchase_date') }}">
                            @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Purchase Cost (&#8377;)</label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" step="0.01" min="0" name="purchase_cost"
                                    class="form-control @error('purchase_cost') is-invalid @enderror"
                                    value="{{ old('purchase_cost', 0) }}">
                                @error('purchase_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.animals.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-check-lg me-1"></i>Register Animal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
