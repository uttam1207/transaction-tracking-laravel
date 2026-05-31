@extends('layouts.app')
@section('title', 'Create Transaction')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transactions</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@push('styles')
<style>
.form-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
    border-radius: 16px;
    padding: 22px 28px;
    margin-bottom: 24px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.form-hero::before {
    content:'';position:absolute;top:-40px;right:-30px;
    width:160px;height:160px;background:rgba(255,255,255,.06);border-radius:50%;
}
.form-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    margin-bottom: 20px;
    overflow: hidden;
}
.form-section-header {
    padding: 14px 20px;
    border-bottom: 1px solid #f3f4f6;
    background: #f9fafb;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #6b7280;
}
.form-section-header i { color: #4f46e5; font-size: .9rem; }
.form-section-body { padding: 20px; }

.form-label {
    font-size: .78rem !important;
    font-weight: 600 !important;
    color: #374151 !important;
    margin-bottom: 6px !important;
}
.form-label .req { color: #ef4444; }
.form-control, .form-select {
    border-radius: 9px !important;
    border: 1.5px solid #e5e7eb !important;
    font-size: .875rem !important;
    height: 40px !important;
    background: #f9fafb !important;
    transition: border-color .2s, box-shadow .2s !important;
}
.form-control:focus, .form-select:focus {
    border-color: #4f46e5 !important;
    box-shadow: 0 0 0 3px rgba(79,70,229,.1) !important;
    background: #fff !important;
}
textarea.form-control { height: auto !important; }

.input-currency-wrap { position: relative; display: flex; gap: 0; }
.input-currency-wrap .currency-select {
    border-radius: 9px 0 0 9px !important;
    border-right: none !important;
    width: 90px !important;
    flex-shrink: 0;
}
.input-currency-wrap .amount-input {
    border-radius: 0 9px 9px 0 !important;
    flex: 1;
}

.sidebar-info-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
    margin-bottom: 20px;
    overflow: hidden;
}
.fraud-info-box {
    background: linear-gradient(135deg, #ede9fe, #e0e7ff);
    border: 1px solid #c4b5fd;
    border-radius: 10px;
    padding: 14px 16px;
}
.fraud-info-box i { color: #7c3aed; font-size: 1.2rem; }

.btn-submit {
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #fff;
    border: none;
    border-radius: 10px;
    height: 44px;
    font-size: .9rem;
    font-weight: 700;
    width: 100%;
    transition: opacity .2s;
}
.btn-submit:hover { opacity: .9; color: #fff; }
.btn-cancel {
    background: #fff;
    color: #6b7280;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    height: 44px;
    font-size: .9rem;
    font-weight: 600;
    width: 100%;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .15s;
}
.btn-cancel:hover { background: #f3f4f6; color: #374151; }
</style>
@endpush

@section('content')

<div class="form-hero">
    <div style="position:relative;z-index:1;">
        <h5 class="mb-1 fw-bold" style="font-weight:800;letter-spacing:-.3px;">Create Transaction</h5>
        <p class="mb-0" style="opacity:.7;font-size:.82rem;">Record a new financial transaction with full detail</p>
    </div>
</div>

<form action="{{ route('admin.transactions.store') }}" method="POST">
@csrf
<div class="row g-4">

    {{-- Left Column --}}
    <div class="col-lg-8">

        {{-- Transaction Details --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-credit-card"></i>Transaction Details</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Transaction Type <span class="req">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="req">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach(['transfer'=>'Bank Transfer','payment'=>'Payment','withdrawal'=>'Withdrawal','deposit'=>'Deposit','refund'=>'Refund','purchase'=>'Purchase','salary'=>'Salary','investment'=>'Investment','loan'=>'Loan','other'=>'Other'] as $val=>$label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Amount <span class="req">*</span></label>
                        <div class="input-currency-wrap">
                            <select name="currency" class="form-select currency-select">
                                <option value="INR" selected>INR</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                            <input type="number" name="amount" class="form-control amount-input" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fee</label>
                        <input type="number" name="fee" class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Method <span class="req">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            @foreach(['bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card','debit_card'=>'Debit Card','crypto'=>'Cryptocurrency','cash'=>'Cash','mobile_money'=>'Mobile Money','wire_transfer'=>'Wire Transfer'] as $val=>$label)
                                <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account Owner</label>
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
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="success">Success</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" placeholder="US">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Processed At</label>
                        <input type="datetime-local" name="processed_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional notes about this transaction…"></textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sender --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-box-arrow-up-right"></i>Sender Information</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Sender Name <span class="req">*</span></label>
                        <input type="text" name="sender_name" class="form-control" placeholder="Full name" required>
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

        {{-- Receiver --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-box-arrow-in-down-right"></i>Receiver Information</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Receiver Name <span class="req">*</span></label>
                        <input type="text" name="receiver_name" class="form-control" placeholder="Full name" required>
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

    {{-- Right Sidebar --}}
    <div class="col-lg-4">

        {{-- Fraud Detection --}}
        <div class="sidebar-info-card">
            <div class="form-section-header" style="background:#f5f3ff;border-bottom-color:#ede9fe;">
                <i class="bi bi-shield-check" style="color:#7c3aed;"></i>
                <span style="color:#5b21b6;">Fraud Detection</span>
            </div>
            <div class="form-section-body">
                <div class="fraud-info-box">
                    <div class="d-flex gap-3">
                        <i class="bi bi-robot mt-1"></i>
                        <div style="font-size:.82rem; color:#4c1d95; line-height:1.6;">
                            Fraud detection runs <strong>automatically</strong> on submission. The transaction will be flagged if the AI risk score exceeds your configured threshold.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="sidebar-info-card">
            <div class="form-section-header"><i class="bi bi-info-circle"></i>Additional Info</div>
            <div class="form-section-body">
                <div class="mb-3">
                    <label class="form-label">Device ID</label>
                    <input type="text" name="device_id" class="form-control" placeholder="Device identifier">
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-column gap-2">
            <button type="submit" class="btn btn-submit">
                <i class="bi bi-plus-circle me-2"></i>Create Transaction
            </button>
            <a href="{{ route('admin.transactions.index') }}" class="btn-cancel">
                Cancel
            </a>
        </div>

    </div>
</div>
</form>
@endsection
