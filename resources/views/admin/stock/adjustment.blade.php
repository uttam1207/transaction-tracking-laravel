@extends('layouts.app')

@section('title', 'Record Stock Adjustment')

@section('content')
<div class="container py-3" style="max-width: 800px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="h3 mb-0 fw-bold text-warning"><i class="bi bi-sliders me-2"></i>Stock Adjustment</h1>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.stock.adjustment.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', now()->toDateString()) }}" required>
                        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Inventory Item <span class="text-danger">*</span></label>
                        <select name="inventory_item_id" class="form-select @error('inventory_item_id') is-invalid @enderror" required>
                            <option value="">-- Select Item --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('inventory_item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} (Available: {{ $item->available_quantity }} {{ $item->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('inventory_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                            <option value="Increase">Increase Stock (+)</option>
                            <option value="Decrease">Decrease Stock (-)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Adjustment Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" placeholder="Quantity to adjust" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Reason for Adjustment <span class="text-danger">*</span></label>
                        <select name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                            <option value="Physical Stock Verification Audit">Physical Stock Verification Audit</option>
                            <option value="Damaged / Spilled Fodder">Damaged / Spilled Fodder</option>
                            <option value="Expired Medicine / Chemicals">Expired Medicine / Chemicals</option>
                            <option value="Correction of Entry Error">Correction of Entry Error</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Remarks / Explanatory Notes</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Provide context for audit records...">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-warning text-dark px-4"><i class="bi bi-check-circle me-1"></i> Save Stock Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
