@extends('layouts.app')
@section('title', 'Edit Company Profile')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.company.index') }}">Company</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div style="position:relative;z-index:1;">
        <h4>Edit Company Profile</h4>
        <p>Update your organisation's identity and contact information</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.company.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">
        {{-- Identity --}}
        <div class="col-12">
            <div class="table-card">
                <div class="card-header"><span class="card-title"><i class="bi bi-building me-2"></i>Identity</span></div>
                <div style="padding:24px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Company Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $company->name) }}" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Code</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                                value="{{ old('code', $company->code) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-family:monospace;">
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Currency</label>
                            <select name="currency" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(['INR','USD','EUR','GBP','AED','SGD','AUD'] as $cur)
                                <option value="{{ $cur }}" {{ old('currency', $company->currency) === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Tagline</label>
                            <input type="text" name="tagline" class="form-control"
                                value="{{ old('tagline', $company->tagline) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">GST Number</label>
                            <input type="text" name="gst_number" class="form-control"
                                value="{{ old('gst_number', $company->gst_number) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control"
                                value="{{ old('pan_number', $company->pan_number) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">CIN Number</label>
                            <input type="text" name="cin_number" class="form-control"
                                value="{{ old('cin_number', $company->cin_number) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Timezone</label>
                            <select name="timezone" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $company->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Financial Year Start</label>
                            <select name="financial_year_start" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                @foreach([1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'] as $num => $month)
                                <option value="{{ $num }}" {{ old('financial_year_start', $company->financial_year_start) == $num ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Logo</label>
                            <input type="file" name="logo" accept="image/*" class="form-control @error('logo') is-invalid @enderror"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($company->logo)
                            <div class="mt-2"><img src="{{ $company->logo_url }}" style="height:40px;border-radius:6px;border:1px solid #e5e7eb;" alt="Current Logo"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact & Location --}}
        <div class="col-12">
            <div class="table-card">
                <div class="card-header"><span class="card-title"><i class="bi bi-telephone me-2"></i>Contact & Location</span></div>
                <div style="padding:24px;">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="flabel">Phone</label>
                            <input type="text" name="phone" class="form-control"
                                value="{{ old('phone', $company->phone) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $company->email) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Website</label>
                            <input type="url" name="website" class="form-control"
                                value="{{ old('website', $company->website) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Address</label>
                            <input type="text" name="address" class="form-control"
                                value="{{ old('address', $company->address) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">City</label>
                            <input type="text" name="city" class="form-control"
                                value="{{ old('city', $company->city) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">State</label>
                            <input type="text" name="state" class="form-control"
                                value="{{ old('state', $company->state) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Country</label>
                            <input type="text" name="country" class="form-control"
                                value="{{ old('country', $company->country) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-3">
                            <label class="flabel">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control"
                                value="{{ old('postal_code', $company->postal_code) }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Description</label>
                            <textarea name="description" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;">{{ old('description', $company->description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="col-12">
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.company.index') }}" class="btn btn-sm btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-sm btn-primary-grad px-5">Save Changes</button>
            </div>
        </div>
    </div>
</form>
@endsection
