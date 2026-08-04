@extends('layouts.app')

@section('title', 'Record Stock In')

@section('content')
<div class="container py-3" style="max-width: 800px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-secondary me-3">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <h1 class="h3 mb-0 fw-bold text-success"><i class="bi bi-arrow-down-left-circle me-2"></i>Record Stock In</h1>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.stock.in.store') }}" method="POST">
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
                                <option value="{{ $item->id }}" {{ old('inventory_item_id', request('item_id')) == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} (Available: {{ $item->available_quantity }} {{ $item->unit }})
                                </option>
                            @endforeach
                        </select>
                        @error('inventory_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Quantity Received <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" placeholder="e.g. 500" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Source / Purpose</label>
                        <select name="source_purpose" class="form-select">
                            <option value="Purchase">Purchase from Vendor</option>
                            <option value="Harvest">Farm Crop Harvest (Napier/Para Grass)</option>
                            <option value="Return">Returned to Stock</option>
                            <option value="Initial Stock">Opening Inventory</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Vendor / Supplier Name</label>
                        <input type="text" name="issued_to_or_vendor" class="form-control" value="{{ old('issued_to_or_vendor') }}" placeholder="Company or Vendor Name">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold">Remarks / Notes</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Batch number, invoice details, etc.">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.stock.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle me-1"></i> Save Stock In</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
