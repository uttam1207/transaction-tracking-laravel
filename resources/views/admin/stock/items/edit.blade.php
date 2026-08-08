@extends('layouts.app')
@section('title', 'Edit Stock Item — ' . $item->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.stock-items.index') }}">Items</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Stock Item</h4>
            <p>{{ $item->name }} &mdash; {{ $item->category }}</p>
        </div>
        <a href="{{ route('admin.stock-items.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#0d9488,#0891b2);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-box-seam-fill" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Inventory Item</div>
                        <div style="font-size:1.05rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ $item->name }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $item->category }}{{ $item->item_type ? ' — ' . $item->item_type : '' }} &mdash; {{ $item->unit }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.stock-items.update', $item) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="form-section-label">A &mdash; Item Identity</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $item->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">&#8212; Select Category &#8212;</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->name }}" @selected(old('category', $item->category)===$cat->name)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Item Type <small class="text-muted fw-normal">(optional)</small></label>
                                <select name="item_type" class="form-select @error('item_type') is-invalid @enderror">
                                    <option value="">&#8212; None &#8212;</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t->name }}" @selected(old('item_type', $item->item_type)===$t->name)>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 opacity-25">

                    <div class="mb-4">
                        <h6 class="form-section-label">B &mdash; Stock Settings</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                                <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror"
                                    value="{{ old('unit', $item->unit) }}" list="unit-list" required>
                                <datalist id="unit-list">
                                    <option value="kg">
                                    <option value="liters">
                                    <option value="bags">
                                    <option value="bottles">
                                    <option value="pcs">
                                    <option value="boxes">
                                    <option value="tons">
                                    <option value="vials">
                                    <option value="strips">
                                </datalist>
                                @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Minimum Stock (Reorder Level) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="min_stock"
                                    class="form-control @error('min_stock') is-invalid @enderror"
                                    value="{{ old('min_stock', $item->min_stock) }}" required>
                                @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expiry Date <small class="text-muted fw-normal">(medicines/vaccines)</small></label>
                                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                                    value="{{ old('expiry_date', $item->expiry_date?->toDateString()) }}">
                                @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($item->is_expired)
                                    <div style="font-size:.72rem;color:#ef4444;margin-top:4px;"><i class="bi bi-exclamation-triangle me-1"></i>This item has EXPIRED!</div>
                                @elseif($item->is_expiring_soon)
                                    <div style="font-size:.72rem;color:#f59e0b;margin-top:4px;"><i class="bi bi-clock me-1"></i>Expires in {{ $item->days_to_expiry }} days</div>
                                @endif
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Description <small class="text-muted fw-normal">(optional)</small></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="2">{{ old('description', $item->description) }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                        @checked(old('is_active', $item->is_active))>
                                    <label class="form-check-label fw-semibold" for="is_active">Active</label>
                                </div>
                                <div style="font-size:.72rem;color:#9ca3af;margin-top:4px;">Inactive items are hidden from stock in/out forms</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.stock-items.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Item
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection