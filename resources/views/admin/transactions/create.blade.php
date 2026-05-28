@extends('layouts.app')

@section('title', 'Create Transaction')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.transactions.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Transactions
        </a>
        <h4 class="mb-0 fw-bold mt-1">Create Transaction</h4>
    </div>
</div>

<form action="{{ route('admin.transactions.store') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="row g-4">
    {{-- Transaction Info --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Transaction Details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach(['transfer' => 'Bank Transfer', 'payment' => 'Payment', 'withdrawal' => 'Withdrawal', 'deposit' => 'Deposit', 'refund' => 'Refund', 'purchase' => 'Purchase', 'salary' => 'Salary', 'investment' => 'Investment', 'loan' => 'Loan', 'other' => 'Other'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <select name="currency" class="form-select" style="max-width: 90px;">
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="INR">INR</option>
                            </select>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01"
                                placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fee</label>
                        <input type="number" name="fee" class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            @foreach(['bank_transfer' => 'Bank Transfer', 'credit_card' => 'Credit Card', 'debit_card' => 'Debit Card', 'crypto' => 'Cryptocurrency', 'cash' => 'Cash', 'mobile_money' => 'Mobile Money', 'wire_transfer' => 'Wire Transfer'] as $val => $label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">User (Account Owner)</label>
                        <select name="user_id" class="form-select">
                            <option value="">Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference" class="form-control" placeholder="REF-XXXXXXXX">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" placeholder="US">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sender Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Sender Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Sender Name <span class="text-danger">*</span></label>
                        <input type="text" name="sender_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sender Account</label>
                        <input type="text" name="sender_account" class="form-control" placeholder="Account number">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Sender Bank</label>
                        <input type="text" name="sender_bank" class="form-control" placeholder="Bank name">
                    </div>
                </div>
            </div>
        </div>

        {{-- Receiver Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Receiver Information</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Receiver Name <span class="text-danger">*</span></label>
                        <input type="text" name="receiver_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Receiver Account</label>
                        <input type="text" name="receiver_account" class="form-control" placeholder="Account number">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Receiver Bank</label>
                        <input type="text" name="receiver_bank" class="form-control" placeholder="Bank name">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Fraud Detection</div>
            <div class="card-body">
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Fraud detection will run automatically on submission.
                    The transaction will be flagged if risk score exceeds threshold.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent fw-semibold">Additional Info</div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Device ID</label>
                    <input type="text" name="device_id" class="form-control" placeholder="Device identifier">
                </div>
                <div class="mb-3">
                    <label class="form-label">Processed At</label>
                    <input type="datetime-local" name="processed_at" class="form-control"
                        value="{{ now()->format('Y-m-d\TH:i') }}">
                </div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-plus-circle me-2"></i>Create Transaction
            </button>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary">
                Cancel
            </a>
        </div>
    </div>
</div>
</form>
@endsection
