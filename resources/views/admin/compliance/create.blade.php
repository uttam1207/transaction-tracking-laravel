@extends('layouts.app')
@section('title', 'Log Compliance Document')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.compliance.index') }}">Compliance</a></li>
    <li class="breadcrumb-item active">Log Document</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Log Compliance Document</h4>
            <p>Register FSSAI licenses, insurance, certifications and government documents</p>
        </div>
        <a href="{{ route('admin.compliance.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back to Compliance
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card-glass p-4">
            <div class="card-glass-header">
                <div class="card-glass-icon" style="background:linear-gradient(135deg,#059669,#16a34a);"><i class="bi bi-shield-fill-check"></i></div>
                <div class="card-glass-title">Compliance Document Details</div>
            </div>

            <form action="{{ route('admin.compliance.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Document Title <span class="text-danger">*</span></label>
                        <input type="text" name="document_title" class="form-control @error('document_title') is-invalid @enderror"
                            placeholder="e.g. FSSAI Dairy License" value="{{ old('document_title') }}" required>
                        @error('document_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            @foreach(['FSSAI','Animal Insurance','Vaccination Certificates','Government Licenses','Bank Loan Documents','Land Records','Audit Files'] as $cat)
                                <option value="{{ $cat }}" @selected(old('category')===$cat)>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(['Active','Expiring Soon','Expired'] as $s)
                                <option value="{{ $s }}" @selected(old('status','Active')===$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Document Number</label>
                        <input type="text" name="document_number" class="form-control @error('document_number') is-invalid @enderror"
                            placeholder="e.g. FSSAI-11826000192" value="{{ old('document_number') }}">
                        @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control @error('issue_date') is-invalid @enderror"
                            value="{{ old('issue_date') }}">
                        @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror"
                            value="{{ old('expiry_date') }}">
                        @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-paperclip me-1"></i>Attach Document / Scan
                        </label>
                        <input type="file" name="file" id="complianceFile"
                            class="form-control @error('file') is-invalid @enderror"
                            accept=".pdf,.jpg,.jpeg,.png"
                            onchange="showFileName(this,'complianceFileHint')">
                        <div id="complianceFileHint" class="form-text text-muted" style="font-size:.75rem;">
                            PDF, JPG, PNG — max 10 MB
                        </div>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.compliance.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5">
                        <i class="bi bi-save me-1"></i>Save Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showFileName(input, hintId) {
    const hint = document.getElementById(hintId);
    if (input.files && input.files[0]) {
        const f = input.files[0];
        const mb = (f.size / 1048576).toFixed(2);
        hint.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><strong>' + f.name + '</strong> (' + mb + ' MB)';
    }
}
</script>
@endpush
