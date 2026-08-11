@extends('layouts.app')
@section('title', 'Edit Contact')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.contacts.index') }}">Contacts</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Contact</h4>
            <p>Update details for <strong>{{ $contact->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.contacts.show', $contact) }}" class="btn btn-sm btn-outline-light px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm btn-outline-light px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.contacts.update', $contact) }}">
@csrf @method('PUT')
<div class="row g-4">

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
                                <option value="{{ $cat->id }}" @selected(old('contact_category_id', $contact->contact_category_id) == $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('contact_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $contact->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Company / Organisation</label>
                        <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
                               value="{{ old('company', $contact->company) }}">
                        @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Designation / Role</label>
                        <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                               value="{{ old('designation', $contact->designation) }}">
                        @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" @selected(old('is_active', $contact->is_active ? '1' : '0') === '1')>Active</option>
                            <option value="0" @selected(old('is_active', $contact->is_active ? '1' : '0') === '0')>Inactive</option>
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
                               value="{{ old('phone', $contact->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Alternate Phone</label>
                        <input type="text" name="alternate_phone" class="form-control @error('alternate_phone') is-invalid @enderror"
                               value="{{ old('alternate_phone', $contact->alternate_phone) }}">
                        @error('alternate_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $contact->email) }}">
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
                                  rows="2">{{ old('address', $contact->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                               value="{{ old('city', $contact->city) }}">
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">State</label>
                        <input type="text" name="state" class="form-control @error('state') is-invalid @enderror"
                               value="{{ old('state', $contact->state) }}">
                        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Pincode</label>
                        <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror"
                               value="{{ old('pincode', $contact->pincode) }}">
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
                          rows="3">{{ old('notes', $contact->notes) }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-4">
        <div class="form-section mb-4">
            <div class="form-section-header"><i class="bi bi-info-circle"></i>Contact Info</div>
            <div class="form-section-body">
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">ID</div>
                    <div style="font-size:.85rem;font-weight:700;font-family:monospace;color:#4f46e5;">#{{ $contact->id }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Created</div>
                    <div style="font-size:.85rem;color:#374151;">{{ $contact->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div>
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Last Updated</div>
                    <div style="font-size:.85rem;color:#374151;">{{ $contact->updated_at->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>
        <div class="d-flex flex-column gap-2">
            <button type="submit" class="btn btn-save">
                <i class="bi bi-check-lg me-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.contacts.index') }}" class="btn-cancel">Cancel</a>
        </div>
    </div>

</div>
</form>

@endsection