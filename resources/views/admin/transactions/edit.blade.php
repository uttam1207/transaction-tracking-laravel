@extends('layouts.app')
@section('title', 'Edit — '.$transaction->transaction_id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions.index') }}">Transactions</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.transactions.show', $transaction) }}">{{ $transaction->transaction_id }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@push('styles')
<style>
.form-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
    border-radius: 16px; padding: 22px 28px; margin-bottom: 24px;
    color: #fff; position: relative; overflow: hidden;
}
.form-hero::before {
    content:'';position:absolute;top:-40px;right:-30px;
    width:160px;height:160px;background:rgba(255,255,255,.06);border-radius:50%;pointer-events:none;
}
.form-section {
    background:#fff;border:1px solid #e5e7eb;border-radius:14px;
    box-shadow:0 1px 4px rgba(0,0,0,.04);margin-bottom:20px;overflow:hidden;
}
.form-section-header {
    padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;
    display:flex;align-items:center;gap:8px;
    font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#6b7280;
}
.form-section-header i { color:#4f46e5;font-size:.9rem; }
.form-section-body { padding:20px; }
.form-label { font-size:.78rem!important;font-weight:600!important;color:#374151!important;margin-bottom:6px!important; }
.form-label .req { color:#ef4444; }
.form-control,.form-select {
    border-radius:9px!important;border:1.5px solid #e5e7eb!important;
    font-size:.875rem!important;height:40px!important;background:#f9fafb!important;
    transition:border-color .2s,box-shadow .2s!important;
}
.form-control:focus,.form-select:focus {
    border-color:#4f46e5!important;box-shadow:0 0 0 3px rgba(79,70,229,.1)!important;background:#fff!important;
}
textarea.form-control { height:auto!important; }
.locked-field {
    background:#f3f4f6!important;color:#6b7280;cursor:not-allowed;
    border-color:#e5e7eb!important;
}
.locked-label { display:flex;align-items:center;gap:6px; }
.locked-label .lock-icon { font-size:.72rem;color:#9ca3af; }
.btn-save {
    background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;
    border:none;border-radius:10px;height:44px;font-size:.9rem;font-weight:700;width:100%;transition:opacity .2s;
}
.btn-save:hover { opacity:.9;color:#fff; }
.btn-cancel {
    background:#fff;color:#6b7280;border:1.5px solid #e5e7eb;border-radius:10px;
    height:44px;font-size:.9rem;font-weight:600;width:100%;text-decoration:none;
    display:flex;align-items:center;justify-content:center;transition:background .15s;
}
.btn-cancel:hover { background:#f3f4f6;color:#374151; }
.notice-box {
    background:#fef9c3;border:1px solid #fcd34d;border-radius:10px;
    padding:12px 16px;font-size:.8rem;color:#92400e;display:flex;gap:10px;align-items:flex-start;
}
</style>
@endpush

@section('content')

<div class="form-hero">
    <div style="position:relative;z-index:1;">
        <h5 class="mb-1 fw-bold" style="font-weight:800;letter-spacing:-.3px;">
            <i class="bi bi-pencil-square me-2"></i>Edit Transaction
        </h5>
        <p class="mb-0" style="opacity:.7;font-size:.82rem;">
            Update party details, category, and notes for
            <strong style="font-family:monospace;opacity:1;">{{ $transaction->transaction_id }}</strong>
        </p>
    </div>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius:10px;font-size:.85rem;">
    <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form action="{{ route('admin.transactions.update', $transaction) }}" method="POST" id="editTxnForm">
@csrf
@method('PUT')
<div class="row g-4">

    {{-- Left Column --}}
    <div class="col-lg-8">

        {{-- Locked Financial Fields --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-lock"></i>Financial Details (Read-only)</div>
            <div class="form-section-body">
                <div class="notice-box mb-3">
                    <i class="bi bi-info-circle-fill" style="color:#f59e0b;flex-shrink:0;margin-top:1px;"></i>
                    Amount, type, currency, and status cannot be edited here. Use <strong>Update Status</strong> on the transaction page to change status.
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label locked-label">
                            Amount <span class="lock-icon"><i class="bi bi-lock-fill"></i></span>
                        </label>
                        <input type="text" class="form-control locked-field"
                               value="₹{{ number_format($transaction->amount, 2) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label locked-label">
                            Type <span class="lock-icon"><i class="bi bi-lock-fill"></i></span>
                        </label>
                        <input type="text" class="form-control locked-field"
                               value="{{ ucfirst($transaction->type) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label locked-label">
                            Status <span class="lock-icon"><i class="bi bi-lock-fill"></i></span>
                        </label>
                        <input type="text" class="form-control locked-field"
                               value="{{ ucfirst($transaction->status) }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Editable Details --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-credit-card"></i>Transaction Details</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Category <span class="req">*</span></label>
                        <select name="category" class="form-select" required>
                            @foreach(['transfer'=>'Bank Transfer','payment'=>'Payment','withdrawal'=>'Withdrawal','deposit'=>'Deposit','refund'=>'Refund','purchase'=>'Purchase','salary'=>'Salary','investment'=>'Investment','loan'=>'Loan','other'=>'Other'] as $val=>$label)
                                <option value="{{ $val }}" {{ old('category', $transaction->category)==$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            @foreach(['bank_transfer'=>'Bank Transfer','credit_card'=>'Credit Card','debit_card'=>'Debit Card','cash'=>'Cash','mobile_money'=>'Mobile Money','wire_transfer'=>'Wire Transfer','crypto'=>'Cryptocurrency'] as $val=>$label)
                                <option value="{{ $val }}" {{ old('payment_method', $transaction->payment_method)==$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reference Number</label>
                        <input type="text" name="reference" class="form-control"
                               value="{{ old('reference', $transaction->reference) }}" placeholder="REF-XXXXXXXX">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"
                                  placeholder="Optional notes about this transaction…">{{ old('description', $transaction->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Private notes (not shown to account owner)…">{{ old('notes', $transaction->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sender --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-box-arrow-up-right"></i>Sender Information</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sender Name</label>
                        <input type="text" name="sender_name" class="form-control"
                               value="{{ old('sender_name', $transaction->sender_name) }}" placeholder="Full name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:9px 0 0 9px;border:1.5px solid #e5e7eb;border-right:none;background:#f3f4f6;font-size:.8rem;color:#6b7280;">+91</span>
                            <input type="text" name="sender_mobile" class="form-control"
                                   value="{{ old('sender_mobile', $transaction->sender_mobile) }}"
                                   placeholder="98XXXXXXXX" style="border-radius:0 9px 9px 0 !important;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company / Organisation</label>
                        <input type="text" name="sender_company" class="form-control"
                               value="{{ old('sender_company', $transaction->sender_company) }}" placeholder="Company name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="sender_account" class="form-control"
                               value="{{ old('sender_account', $transaction->sender_account) }}" placeholder="Account no.">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bank</label>
                        <input type="text" name="sender_bank" class="form-control"
                               value="{{ old('sender_bank', $transaction->sender_bank) }}" placeholder="Bank name">
                    </div>
                </div>
            </div>
        </div>

        {{-- Receiver --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-box-arrow-in-down-right"></i>Receiver Information</div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Receiver Name</label>
                        <input type="text" name="receiver_name" class="form-control"
                               value="{{ old('receiver_name', $transaction->receiver_name) }}" placeholder="Full name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius:9px 0 0 9px;border:1.5px solid #e5e7eb;border-right:none;background:#f3f4f6;font-size:.8rem;color:#6b7280;">+91</span>
                            <input type="text" name="receiver_mobile" class="form-control"
                                   value="{{ old('receiver_mobile', $transaction->receiver_mobile) }}"
                                   placeholder="98XXXXXXXX" style="border-radius:0 9px 9px 0 !important;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company / Organisation</label>
                        <input type="text" name="receiver_company" class="form-control"
                               value="{{ old('receiver_company', $transaction->receiver_company) }}" placeholder="Company name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="receiver_address" class="form-control"
                               value="{{ old('receiver_address', $transaction->receiver_address) }}" placeholder="Street, City, State — Pincode">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="receiver_account" class="form-control"
                               value="{{ old('receiver_account', $transaction->receiver_account) }}" placeholder="Account no.">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank</label>
                        <input type="text" name="receiver_bank" class="form-control"
                               value="{{ old('receiver_bank', $transaction->receiver_bank) }}" placeholder="Bank name">
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-4">

        {{-- Transaction Info --}}
        <div class="form-section">
            <div class="form-section-header"><i class="bi bi-info-circle"></i>Transaction Info</div>
            <div class="form-section-body">
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">ID</div>
                    <div style="font-size:.85rem;font-weight:700;font-family:monospace;color:#4f46e5;">{{ $transaction->transaction_id }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Created</div>
                    <div style="font-size:.85rem;font-weight:500;color:#374151;">{{ $transaction->created_at->format('d M Y, H:i') }}</div>
                </div>
                <div class="mb-3">
                    <div style="font-size:.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Risk Score</div>
                    <div style="font-size:.85rem;font-weight:700;color:{{ $transaction->risk_score >= 70 ? '#dc2626' : ($transaction->risk_score >= 40 ? '#d97706' : '#16a34a') }};">
                        {{ $transaction->risk_score }}/100
                    </div>
                </div>
                @if($transaction->is_flagged)
                <div style="background:#fee2e2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:.8rem;font-weight:600;color:#dc2626;">
                    <i class="bi bi-flag-fill me-1"></i>Flagged for Fraud
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="d-flex flex-column gap-2">
            <button type="submit" class="btn btn-save">
                <i class="bi bi-check-lg me-2"></i>Save Changes
            </button>
            <a href="{{ route('admin.transactions.show', $transaction) }}" class="btn-cancel">
                Cancel
            </a>
        </div>

    </div>
</div>
</form>
@endsection
