@extends('layouts.app')
@section('title', 'Edit Milk Entry #' . $milkEntry->id)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.milk.index') }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-success"><i class="bi bi-pencil me-2"></i>Edit Milk Entry #{{ $milkEntry->id }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.milk.update', $milkEntry) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ old('date', $milkEntry->date->toDateString()) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Shift <span class="text-danger">*</span></label>
                        <select name="shift" class="form-select" required>
                            <option value="Morning" @selected(old('shift', $milkEntry->shift) === 'Morning')>Morning Shift</option>
                            <option value="Evening" @selected(old('shift', $milkEntry->shift) === 'Evening')>Evening Shift</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Select Animal (Optional)</label>
                        <select name="animal_id" class="form-select">
                            <option value="">-- Batch Entry (Entire Shed) --</option>
                            @foreach($animals as $a)
                                <option value="{{ $a->id }}" @selected(old('animal_id', $milkEntry->animal_id) == $a->id)>
                                    {{ $a->tag_number }}{{ $a->name ? ' — ' . $a->name : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Quantity (Liters) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" min="0.1" name="quantity_liters" class="form-control" value="{{ old('quantity_liters', $milkEntry->quantity_liters) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fat %</label>
                        <input type="number" step="0.1" name="fat_percentage" class="form-control" value="{{ old('fat_percentage', $milkEntry->fat_percentage) }}" min="1" max="15">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">SNF %</label>
                        <input type="number" step="0.1" name="snf_percentage" class="form-control" value="{{ old('snf_percentage', $milkEntry->snf_percentage) }}" min="1" max="15">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Quality Grade</label>
                        <select name="quality_rating" class="form-select">
                            @foreach(['Grade A+', 'Grade A', 'Grade B'] as $g)
                                <option value="{{ $g }}" @selected(old('quality_rating', $milkEntry->quality_rating) === $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Rejected Liters</label>
                        <input type="number" step="0.1" min="0" name="rejected_liters" class="form-control" value="{{ old('rejected_liters', $milkEntry->rejected_liters) }}">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.milk.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Update Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
