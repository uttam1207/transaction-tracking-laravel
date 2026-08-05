@extends('layouts.app')
@section('title', 'Add Stock Item')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stock.index') }}">Stock</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.stock-items.index') }}">Items</a></li>
    <li class="breadcrumb-item active">Add New</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Add New Stock Item</h4>
            <p>Create a new inventory item with category, type and reorder threshold</p>
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
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">New Inventory Item</div>
                        <div style="font-size:1.05rem;font-weight:800;color:#fff;letter-spacing:-.01em;">Stock Item Details</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.stock-items.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <h6 class="form-section-label">A &mdash; Item Identity</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                    placeholder="e.g. Maize Silage, Oxytetracycline 20%, Milking Machine"
                                    value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    <option value="">&#8212; Select Category &#8212;</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->name }}" @selected(old('category')===$cat->name)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="mt-1" style="font-size:.72rem;color:#9ca3af;"><a href="{{ route('admin.stock-categories.index') }}" target="_blank">Manage Categories</a></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Item Type <small class="text-muted fw-normal">(optional)</small></label>
                                <select name="item_type" class="form-select @error('item_type') is-invalid @enderror">
                                    <option value="">&#8212; None &#8212;</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t->name }}" @selected(old('item_type')===$t->name)>{{ $t->name }}</option>
                                    @endforeach
                                </select>
                                @error('item_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="mt-1" style="font-size:.72rem;color:#9ca3af;"><a href="{{ route('admin.stock-types.index') }}" target="_blank">Manage Types</a></div>
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
                                    placeholder="e.g. kg, liters, bags, bottles, pcs"
                                    value="{{ old('unit') }}" list="unit-list" required>
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
                                    placeholder="e.g. 100" value="{{ old('min_stock', 0) }}" required>
                                @error('min_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div style="font-size:.72rem;color:#9ca3af;margin-top:4px;">Alert triggers when available stock falls below this level</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description <small class="text-muted fw-normal">(optional)</small></label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="2" placeholder="Brief notes about this item&#8230;">{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.stock-items.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Save Item
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

@endsection