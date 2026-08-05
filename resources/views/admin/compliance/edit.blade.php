@extends('layouts.app')
@section('title', 'Edit — ' . $complianceDocument->document_title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.compliance.index') }}">Compliance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.compliance.show', $complianceDocument) }}">{{ Str::limit($complianceDocument->document_title,30) }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Compliance Document</h4>
            <p>{{ $complianceDocument->document_title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.compliance.show', $complianceDocument) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.compliance.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass overflow-hidden">

            <div style="background:linear-gradient(135deg,#059669,#16a34a);padding:22px 28px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div style="position:absolute;bottom:-20px;right:90px;width:80px;height:80px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:rgba(255,255,255,.18);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-shield-fill-check" style="font-size:1.4rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Compliance Document</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-.01em;">{{ Str::limit($complianceDocument->document_title, 40) }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.8rem;margin-top:2px;">{{ $complianceDocument->category }} &mdash; {{ $complianceDocument->status }}</div>
                    </div>
                </div>
            </div>

            <div class="p-4">

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form action="{{ route('admin.compliance.update', $complianceDocument) }}" method="POST">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="form-section-label">A — Document Details</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Document Title <span class="text-danger">*</span></label>
                                <input type="text" name="document_title" class="form-control @error('document_title') is-invalid @enderror"
                                    value="{{ old('document_title', $complianceDocument->document_title) }}" required>
                                @error('document_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                    @foreach(['FSSAI','Animal Insurance','Vaccination Certificates','Government Licenses','Bank Loan Documents','Land Records','Audit Files'] as $cat)
                                        <option value="{{ $cat }}" @selected(old('category',$complianceDocument->category)===$cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                                @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Document Number</label>
                                <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror"
                                    value="{{ old('document_number', $complianceDocument->document_number) }}">
                                @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Issue Date</label>
                                <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                                    value="{{ old('issue_date', $complianceDocument->issue_date?->toDateString()) }}">
                                @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                                    value="{{ old('expiry_date', $complianceDocument->expiry_date?->toDateString()) }}">
                                @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach(['Active','Expiring Soon','Expired'] as $s)
                                        <option value="{{ $s }}" @selected(old('status',$complianceDocument->status)===$s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="{{ route('admin.compliance.show', $complianceDocument) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary-grad px-5">
                            <i class="bi bi-check-lg me-1"></i>Update Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
