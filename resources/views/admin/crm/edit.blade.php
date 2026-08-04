@extends('layouts.app')
@section('title', 'Edit Customer — ' . $crmCustomer->name)

@section('content')
<div class="container py-3" style="max-width:700px;">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.crm.show', $crmCustomer) }}" class="btn btn-outline-secondary me-3"><i class="bi bi-arrow-left"></i> Back</a>
        <h1 class="h3 mb-0 fw-bold text-primary"><i class="bi bi-pencil me-2"></i>Edit — {{ $crmCustomer->name }}</h1>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.crm.update', $crmCustomer) }}" method="POST">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $crmCustomer->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            @foreach(['Milk Buyer','Animal Buyer','Franchise Lead','Investor','Government Official','Veterinary Doctor'] as $c)
                                <option value="{{ $c }}" @selected(old('category', $crmCustomer->category) === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $crmCustomer->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $crmCustomer->email) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Address</label>
                        <input type="text" name="address" class="form-control" value="{{ old('address', $crmCustomer->address) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Total Business Value (₹)</label>
                        <input type="number" step="0.01" min="0" name="total_business_value" class="form-control" value="{{ old('total_business_value', $crmCustomer->total_business_value) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['Lead','Contacted','Active Customer','Partner','Inactive'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $crmCustomer->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('admin.crm.show', $crmCustomer) }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
