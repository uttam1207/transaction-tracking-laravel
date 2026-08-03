@extends('layouts.app')
@section('title', 'Edit Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.expenses.index') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Edit Expense #{{ $expense->id }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Expense</h4>
            <p>#{{ $expense->id }} &mdash; {{ $expense->description }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-glass p-4">
            <form method="POST" action="{{ route('admin.expenses.update', $expense) }}" enctype="multipart/form-data">
                @csrf @method('PUT')

                {{-- Section A --}}
                <div class="mb-4">
                    <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">A — Expense Information</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                                value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required>
                            @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="expense_category_id" class="form-select @error('expense_category_id') is-invalid @enderror" required>
                                <option value="">Select category…</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('expense_category_id', $expense->expense_category_id) == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                                value="{{ old('description', $expense->description) }}" required>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount (&#8377;) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">&#8377;</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                    step="0.01" min="0.01" value="{{ old('amount', $expense->amount) }}" required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Vendor / Payee</label>
                            <input type="text" name="vendor_payee" class="form-control @error('vendor_payee') is-invalid @enderror"
                                value="{{ old('vendor_payee', $expense->vendor_payee) }}">
                            @error('vendor_payee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-3 opacity-25">

                {{-- Section B --}}
                <div class="mb-4">
                    <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">B — Payment Details</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="cash"          @selected(old('payment_method', $expense->payment_method)==='cash')>Cash</option>
                                <option value="upi"           @selected(old('payment_method', $expense->payment_method)==='upi')>UPI</option>
                                <option value="bank_transfer" @selected(old('payment_method', $expense->payment_method)==='bank_transfer')>Bank Transfer</option>
                                <option value="cheque"        @selected(old('payment_method', $expense->payment_method)==='cheque')>Cheque</option>
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                            <select name="payment_status" class="form-select @error('payment_status') is-invalid @enderror" required>
                                <option value="paid"    @selected(old('payment_status', $expense->payment_status)==='paid')>Paid</option>
                                <option value="pending" @selected(old('payment_status', $expense->payment_status)==='pending')>Pending</option>
                            </select>
                            @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control @error('reference_number') is-invalid @enderror"
                                value="{{ old('reference_number', $expense->reference_number) }}">
                            @error('reference_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-3 opacity-25">

                {{-- Section C --}}
                <div class="mb-4">
                    <h6 class="fw-semibold text-muted mb-3" style="font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;">C — Supporting Documents</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Bill / Invoice</label>
                            @if($expense->bill_path)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($expense->bill_path) }}" target="_blank" class="btn btn-xs btn-outline-info">
                                        <i class="bi bi-file-earmark me-1"></i>View Current Bill
                                    </a>
                                </div>
                            @endif
                            <input type="file" name="bill" class="form-control @error('bill') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">Upload new file to replace existing. JPG, PNG, PDF — max 5 MB</div>
                            @error('bill') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Remarks</label>
                            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="2">{{ old('remarks', $expense->remarks) }}</textarea>
                            @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2">
                    <a href="{{ route('admin.expenses.show', $expense) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5"><i class="bi bi-check-lg me-1"></i>Update Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
