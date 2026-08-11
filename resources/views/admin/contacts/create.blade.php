@extends('layouts.app')
@section('title', 'Add Contact')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
    <li class="breadcrumb-item active">Add Contact</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Add New Contact</h4>
            <p>Save a new vendor, client, vet or any connection to the directory</p>
        </div>
        <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light px-4">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="{{ route('admin.contacts.store') }}">
@csrf
<div class="row g-4">

    {{-- Left: Main Info --}}
    <div class="col-lg-8">

        {{-- Basic Info --}}
        <div class="form-section mb-4">
            <div class="form-section-header"><i class="bi bi-person"></i>Basic Information</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="contact_category_id" class="form-select @error('contact_category_id') is-invalid @enderror" required>
                            <option value="">— Select Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('contact_category_id') == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('contact_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="e.g. Ramesh Patel" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company / Organisation</label>
                        <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
                               value="{{ old('company') }}" placeholder="e.g. Patel Dairy Farm">
                        @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Designation / Role</label>
                        <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                               value="{{ old('designation') }}" placeholder="e.g. Owner, Manager, Vet Doctor">
                        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" @selected(old('is_active', '1') === '1')>Active</option>
                            <option value="0" @selected(old('is_active') === '0')>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Info --}}
        <div class="form-section mb-4">
            <div class="form-section-header"><i class="bi bi-telephone"></i>Contact Details</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Primary Phone</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="e.g. +91 98765 43210">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Alternate Phone</label>
                        <input type="text" name="alternate_phone" class="form-control @error('alternate_phone') is-invalid @enderror"
                               value="{{ old('alternate_phone') }}" placeholder="e.g. +91 91234 56789">
                        @error('alternate_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" placeholder="e.g. ramesh@example.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="form-section mb-4">
            <div class="form-section-header"><i class="bi bi-geo-alt"></i>Address</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Street Address</label>
                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                  rows="2" placeholder="House / Flat no., Street, Area…">{{ old('address') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                               value="{{ old('city') }}" placeholder="e.g. Ahmedabad">
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">State</label>
                        <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                               value="{{ old('state') }}" placeholder="e.g. Gujarat">
                        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror"
                               value="{{ old('pincode') }}" placeholder="e.g. 380015">
                        @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-sticky"></i>Additional Notes</div>
            <div class="form-section-body">
                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror"
                          rows="3" placeholder="Any additional details, payment terms, specialisation, etc.…">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-4">
        <div class="form-section mb-4">
            <div class="form-section-header"><i class="bi bi-info-circle"></i>Quick Tips</div>
            <div class="form-section-body">
                <div style="font-size:.82rem;color:#6b7280;line-height:1.7;">
                    <div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i>Select the right <strong>category</strong> to keep the directory organised.</div>
                    <div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i>Phone and email are optional but useful for quick contact.</div>
                    <div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i>Use <strong>Notes</strong> for payment terms, specialisation or any custom details.</div>
                    <div><i class="bi bi-check-circle-fill text-success me-1"></i>You can add new categories from the <a href="{{ route('admin.contact-categories.index') }}" class="text-primary">Categories</a> page.</div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column gap-2">
            <button type="submit" class="btn btn-save">
                <i class="bi bi-check-lg me-2"></i>Save Contact
            </button>
            <a href="{{ route('admin.contacts.index') }}" class="btn-cancel">Cancel</a>
        </div>
    </div>

</div>
</form>

@endsection