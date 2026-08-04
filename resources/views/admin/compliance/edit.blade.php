@extends('layouts.app')
@section('title', 'Edit — ' . $complianceDocument->document_title)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.compliance.show', $complianceDocument) }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-success"><i class="bi bi-pencil me-2"></i>Edit Compliance Document</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.compliance.update', $complianceDocument) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Document Title <span class="text-danger">*</span></label>
                        <input type="text" name="document_title" class="form-control" value="{{ old('document_title', $complianceDocument->document_title) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            @foreach(['FSSAI','Animal Insurance','Vaccination Certificates','Government Licenses','Bank Loan Documents','Land Records','Audit Files'] as $c)
                                <option value="{{ $c }}" @selected(old('category', $complianceDocument->category) === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Document Number</label>
                        <input type="text" name="document_number" class="form-control" value="{{ old('document_number', $complianceDocument->document_number) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', $complianceDocument->issue_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', $complianceDocument->expiry_date?->toDateString()) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['Active','Expiring Soon','Expired'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $complianceDocument->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.compliance.show', $complianceDocument) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-lg me-1"></i>Update Document</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
