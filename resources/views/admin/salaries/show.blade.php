@extends('layouts.app')
@section('title', 'Salary Record')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.salaries.index') }}">Salary & Payroll</a></li>
    <li class="breadcrumb-item active">{{ $salary->employee?->user?->name }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Salary Record — {{ $salary->month_label }}</h4>
            <p>{{ $salary->employee?->user?->name }} · {{ $salary->employee?->department?->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.salaries.payslip', $salary) }}" class="btn btn-sm btn-outline-primary px-4" target="_blank">
                <i class="bi bi-file-earmark-person me-1"></i>Print Payslip
            </a>
            <a href="{{ route('admin.salaries.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-glass p-4">
            <h6 class="form-section-label">Earnings</h6>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div style="font-size:.72rem;color:#9ca3af;font-weight:700;text-transform:uppercase;">Basic Salary</div>
                    <div class="fw-bold" style="font-size:1.1rem;">&#8377;{{ number_format($salary->basic_salary,2) }}</div>
                </div>
                <div class="col-6">
                    <div style="font-size:.72rem;color:#9ca3af;font-weight:700;text-transform:uppercase;">HRA</div>
                    <div class="fw-bold">&#8377;{{ number_format($salary->hra,2) }}</div>
                </div>
                <div class="col-6">
                    <div style="font-size:.72rem;color:#9ca3af;font-weight:700;text-transform:uppercase;">Other Allowances</div>
                    <div class="fw-bold">&#8377;{{ number_format($salary->other_allowances,2) }}</div>
                </div>
                <div class="col-6">
                    <div style="font-size:.72rem;color:#059669;font-weight:700;text-transform:uppercase;">Gross Salary</div>
                    <div class="fw-bold text-success" style="font-size:1.05rem;">&#8377;{{ number_format($salary->gross_salary,2) }}</div>
                </div>
            </div>

            <h6 class="form-section-label">Deductions</h6>
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div style="font-size:.72rem;color:#9ca3af;font-weight:700;text-transform:uppercase;">PF</div>
                    <div style="color:#ef4444;">&#8377;{{ number_format($salary->pf_deduction,2) }}</div>
                </div>
                <div class="col-4">
                    <div style="font-size:.72rem;color:#9ca3af;font-weight:700;text-transform:uppercase;">Tax</div>
                    <div style="color:#ef4444;">&#8377;{{ number_format($salary->tax_deduction,2) }}</div>
                </div>
                <div class="col-4">
                    <div style="font-size:.72rem;color:#9ca3af;font-weight:700;text-transform:uppercase;">Others</div>
                    <div style="color:#ef4444;">&#8377;{{ number_format($salary->other_deductions,2) }}</div>
                </div>
            </div>

            <div class="p-3 rounded" style="background:rgba(5,150,105,.08);border:1px solid rgba(5,150,105,.2);">
                <div style="font-size:.72rem;font-weight:700;color:#065f46;text-transform:uppercase;">Net Salary</div>
                <div style="font-size:2rem;font-weight:800;color:#059669;letter-spacing:-1px;">&#8377;{{ number_format($salary->net_salary,2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-glass p-4">
            <h6 class="form-section-label">Attendance</h6>
            <div class="mb-3">
                <div style="font-size:.72rem;color:#9ca3af;">Days Worked</div>
                <div class="fw-bold text-success">{{ $salary->days_worked }}</div>
            </div>
            <div class="mb-4">
                <div style="font-size:.72rem;color:#9ca3af;">Days Absent</div>
                <div class="fw-bold text-danger">{{ $salary->days_absent }}</div>
            </div>

            <h6 class="form-section-label">Payment</h6>
            <div class="mb-2">
                <span class="spill {{ $salary->payment_status === 'Paid' ? 'spill-green' : ($salary->payment_status === 'Processing' ? 'spill-amber' : 'spill-red') }}">
                    {{ $salary->payment_status }}
                </span>
            </div>
            @if($salary->payment_date)
                <div style="font-size:.8rem;"><span class="text-muted">Date:</span> {{ $salary->payment_date->format('d M Y') }}</div>
                <div style="font-size:.8rem;"><span class="text-muted">Mode:</span> {{ $salary->payment_mode }}</div>
            @endif

            @if($salary->payment_status !== 'Paid')
            <hr class="my-3 opacity-25">
            <form action="{{ route('admin.salaries.mark-paid', $salary) }}" method="POST">
                @csrf
                <div class="mb-2">
                    <label class="form-label fw-semibold" style="font-size:.78rem;">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:.78rem;">Payment Mode</label>
                    <select name="payment_mode" class="form-select form-select-sm" required>
                        <option value="">— Select —</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                        <option value="UPI">UPI</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100 btn-sm">
                    <i class="bi bi-check2 me-1"></i>Mark as Paid
                </button>
            </form>
            @endif

            @if($salary->remarks)
            <hr class="my-3 opacity-25">
            <div style="font-size:.78rem;color:#6b7280;"><strong>Remarks:</strong> {{ $salary->remarks }}</div>
            @endif
        </div>
    </div>
</div>

@endsection
