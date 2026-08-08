@extends('layouts.app')
@section('title', 'Add Milk Entry')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.milk.index') }}">Milk</a>
    </li>
    <li class="breadcrumb-item active">Add Entry</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Daily Milk Entry</h4>
            <p>Record morning or evening shift milk production</p>
        </div>
        <a href="{{ route('admin.milk.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Milk
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#059669,#0d9488);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-droplet-fill" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">New Entry</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">Milk Production Record</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">Log morning or evening shift production</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.milk.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Entry Details</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                                <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                    value="{{ old('date', now()->toDateString()) }}" required>
                                @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Shift <span class="text-danger">*</span></label>
                                <select name="shift" class="form-select @error('shift') is-invalid @enderror">
                                    <option value="Morning" @selected(old('shift') === 'Morning')>Morning Shift</option>
                                    <option value="Evening" @selected(old('shift') === 'Evening')>Evening Shift</option>
                                </select>
                                @error('shift')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Animal <small class="text-muted fw-normal">(leave blank for batch / full shed)</small></label>
                                <select name="animal_id" class="form-select @error('animal_id') is-invalid @enderror">
                                    <option value="">— Batch Entry (Entire Shed) —</option>
                                    @foreach ($animals as $a)
                                        <option value="{{ $a->id }}" @selected(old('animal_id') == $a->id)>{{ $a->tag_number }} — {{ $a->name }}</option>
                                    @endforeach
                                </select>
                                @error('animal_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Quantity (Liters) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0.1" name="quantity_liters"
                                    class="form-control @error('quantity_liters') is-invalid @enderror"
                                    placeholder="e.g. 75.5" value="{{ old('quantity_liters') }}" required>
                                @error('quantity_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B — Quality Parameters</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Fat %</label>
                                <input type="number" step="0.01" name="fat_percentage"
                                    class="form-control @error('fat_percentage') is-invalid @enderror"
                                    placeholder="e.g. 7.8" value="{{ old('fat_percentage', 7.8) }}">
                                @error('fat_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SNF %</label>
                                <input type="number" step="0.01" name="snf_percentage"
                                    class="form-control @error('snf_percentage') is-invalid @enderror"
                                    placeholder="e.g. 9.0" value="{{ old('snf_percentage', 9.0) }}">
                                @error('snf_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">CLR Value</label>
                                <input type="number" step="0.01" name="clr_value"
                                    class="form-control @error('clr_value') is-invalid @enderror"
                                    placeholder="e.g. 28.5" value="{{ old('clr_value') }}">
                                @error('clr_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quality Grade</label>
                                <select name="quality_rating" class="form-select @error('quality_rating') is-invalid @enderror">
                                    <option value="Grade A+" @selected(old('quality_rating') === 'Grade A+')>Grade A+ (Premium Fat &gt;8%)</option>
                                    <option value="Grade A" @selected(old('quality_rating', 'Grade A') === 'Grade A')>Grade A (Standard 7–8%)</option>
                                    <option value="Grade B" @selected(old('quality_rating') === 'Grade B')>Grade B (Sub-standard)</option>
                                </select>
                                @error('quality_rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rejected Liters</label>
                                <input type="number" step="0.1" min="0" name="rejected_liters"
                                    class="form-control @error('rejected_liters') is-invalid @enderror"
                                    value="{{ old('rejected_liters', 0) }}">
                                @error('rejected_liters')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.milk.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Save Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
