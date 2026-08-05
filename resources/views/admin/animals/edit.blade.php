@extends('layouts.app')
@section('title', 'Edit Animal — ' . $animal->tag_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.animals.index') }}">Animals</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.animals.show', $animal) }}">{{ $animal->tag_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Animal</h4>
            <p>{{ $animal->tag_number }} &mdash; {{ $animal->name ?? 'Unnamed' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.animals.show', $animal) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card-glass overflow-hidden">

            @php
                $breedLower = strtolower($animal->breed ?? '');
                $editGrad = str_contains($breedLower,'buffalo') ? 'linear-gradient(135deg,#2563eb,#4f46e5)'
                    : (str_contains($breedLower,'gir')||str_contains($breedLower,'cow')||str_contains($breedLower,'holstein')
                        ? 'linear-gradient(135deg,#059669,#0d9488)' : 'linear-gradient(135deg,#d97706,#ea580c)');
            @endphp

            <div style="background:{{ $editGrad }};padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-tag-fill" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Animal</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $animal->tag_number }}{{ $animal->name ? ' — '.$animal->name : '' }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $animal->breed }} &mdash; {{ $animal->health_status }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.animals.update', $animal) }}" method="POST">
                    @csrf @method('PUT')

                    {{-- Section A: Identity --}}
                    <div class="mb-4">
                        <h6 class="form-section-label">A — Animal Identity</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ear Tag Number <span class="text-danger">*</span></label>
                                <input type="text" name="tag_number"
                                    class="form-control @error('tag_number') is-invalid @enderror"
                                    value="{{ old('tag_number', $animal->tag_number) }}" required>
                                @error('tag_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Animal Name</label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="e.g. Laxmi"
                                    value="{{ old('name', $animal->name) }}">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Breed <span class="text-danger">*</span></label>
                                <select name="breed" class="form-select @error('breed') is-invalid @enderror" required>
                                    <option value="">— Select Breed —</option>
                                    @foreach($breeds->groupBy('animal_type') as $type => $typeBreeds)
                                        <optgroup label="{{ $type }}">
                                            @foreach($typeBreeds as $b)
                                                <option value="{{ $b->name }}" @selected(old('breed', $animal->breed)===$b->name)>{{ $b->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('breed')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="mt-1" style="font-size:.72rem;color:#6b7280;">
                                    <a href="{{ route('admin.breeds.index') }}" target="_blank" class="text-primary">
                                        <i class="bi bi-pencil me-1"></i>Manage breeds
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lactation Number <span class="text-danger">*</span></label>
                                <input type="number" name="lactation_number"
                                    class="form-control @error('lactation_number') is-invalid @enderror"
                                    value="{{ old('lactation_number', $animal->lactation_number) }}" min="0" required>
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
                                        <option value="{{ $h }}" @selected(old('health_status',$animal->health_status)===$h)>{{ $h }}</option>
                                    @endforeach
                                </select>
                                @error('health_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pregnancy Status <span class="text-danger">*</span></label>
                                <select name="pregnancy_status" class="form-select @error('pregnancy_status') is-invalid @enderror">
                                    @foreach(['Open','Inseminated','Pregnant','Dry'] as $p)
                                        <option value="{{ $p }}" @selected(old('pregnancy_status',$animal->pregnancy_status)===$p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                                @error('pregnancy_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Current Weight (kg)</label>
                                <input type="number" step="0.1" min="0" name="current_weight"
                                    class="form-control @error('current_weight') is-invalid @enderror"
                                    value="{{ old('current_weight', $animal->current_weight) }}">
                                @error('current_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Shed Number <span class="text-danger">*</span></label>
                                <input type="text" name="shed_number"
                                    class="form-control @error('shed_number') is-invalid @enderror"
                                    value="{{ old('shed_number', $animal->shed_number) }}" required>
                                @error('shed_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Owner Name <span class="text-danger">*</span></label>
                                <input type="text" name="owner_name"
                                    class="form-control @error('owner_name') is-invalid @enderror"
                                    value="{{ old('owner_name', $animal->owner_name) }}" required>
                                @error('owner_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Record Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach(['Active','Sold','Deceased'] as $s)
                                        <option value="{{ $s }}" @selected(old('status',$animal->status)===$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                    value="{{ old('dob', $animal->dob?->toDateString()) }}">
                                @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Purchase Date</label>
                                <input type="date" name="purchase_date"
                                    class="form-control @error('purchase_date') is-invalid @enderror"
                                    value="{{ old('purchase_date', $animal->purchase_date?->toDateString()) }}">
                                @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Purchase Cost (&#8377;)</label>
                                <div class="input-group">
                                    <span class="input-group-text">&#8377;</span>
                                    <input type="number" step="0.01" min="0" name="purchase_cost"
                                        class="form-control @error('purchase_cost') is-invalid @enderror"
                                        value="{{ old('purchase_cost', $animal->purchase_cost) }}">
                                    @error('purchase_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.animals.show', $animal) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Animal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
