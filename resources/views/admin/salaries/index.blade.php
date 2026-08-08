@extends('layouts.app')
@section('title', 'Salary & Payroll')

@section('breadcrumb')
    <li class="breadcrumb-item active">Salary & Payroll</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Salary & Payroll Management</h4>
            <p>Generate, review, and disburse employee salaries with payslip generation</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.salaries.create') }}" class="btn btn-primary-grad btn-sm px-4">
                <i class="bi bi-plus-lg me-1"></i>Add Salary Record
            </a>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#0d9488);">
            <i class="bi bi-currency-rupee kpi-icon"></i>
            <div class="kpi-value" style="font-size:1.3rem;">&#8377;{{ number_format($summary['total_payroll'],0) }}</div>
            <div class="kpi-label">Total Payroll</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#10b981,#059669);">
            <i class="bi bi-check-circle-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['paid_count'] }}</div>
            <div class="kpi-label">Paid</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#dc2626);">
            <i class="bi bi-hourglass-split kpi-icon"></i>
            <div class="kpi-value">{{ $summary['pending_count'] }}</div>
            <div class="kpi-label">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
            <i class="bi bi-people-fill kpi-icon"></i>
            <div class="kpi-value">{{ $summary['total_employees'] }}</div>
            <div class="kpi-label">Total Employees</div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.salaries.index') }}">
<div class="card-glass mb-3 px-4 py-3">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;">Year</label>
            <select name="year" class="form-select form-select-sm">
                @for($y = now()->year; $y >= 2024; $y--)
                    <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;">Month</label>
            <select name="month" class="form-select form-select-sm">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" @selected($m == $month)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-semibold" style="font-size:.75rem;">Department</label>
            <select name="department" class="form-select form-select-sm">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.75rem;">Status</label>
            <select name="payment_status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="Pending" @selected(request('payment_status')=='Pending')>Pending</option>
                <option value="Processing" @selected(request('payment_status')=='Processing')>Processing</option>
                <option value="Paid" @selected(request('payment_status')=='Paid')>Paid</option>
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary-grad btn-sm flex-fill">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            {{-- Bulk Generate --}}
            <form action="{{ route('admin.salaries.bulk-generate') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <button type="submit" class="btn btn-sm btn-outline-primary px-3"
                    onclick="return confirm('Auto-generate salaries for all active employees for {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}?')"
                    title="Bulk generate from employee base salary">
                    <i class="bi bi-lightning-charge me-1"></i>Bulk Gen
                </button>
            </form>
        </div>
    </div>
</div>
</form>

{{-- Salary Table --}}
<div class="card-glass overflow-hidden">
    <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
        <span class="fw-bold">{{ date('F Y', mktime(0,0,0,$month,1,$year)) }} Payroll — {{ $salaries->count() }} records</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="text-end">Basic</th>
                    <th class="text-end">Gross</th>
                    <th class="text-end">Deductions</th>
                    <th class="text-end">Net Pay</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $sal)
                <tr>
                    <td>
                        <div class="fw-semibold" style="font-size:.85rem;">{{ $sal->employee?->user?->name ?? '—' }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;">{{ $sal->employee?->employee_id }}</div>
                    </td>
                    <td style="font-size:.8rem;">{{ $sal->employee?->department?->name ?? '—' }}</td>
                    <td class="text-end" style="font-size:.82rem;">&#8377;{{ number_format($sal->basic_salary,0) }}</td>
                    <td class="text-end" style="font-size:.82rem;">&#8377;{{ number_format($sal->gross_salary,0) }}</td>
                    <td class="text-end" style="font-size:.82rem;color:#ef4444;">
                        &#8377;{{ number_format($sal->pf_deduction + $sal->tax_deduction + $sal->other_deductions, 0) }}
                    </td>
                    <td class="text-end fw-bold" style="color:#059669;">&#8377;{{ number_format($sal->net_salary,0) }}</td>
                    <td style="font-size:.8rem;">
                        <span style="color:#059669;">{{ $sal->days_worked }}d</span>
                        @if($sal->days_absent > 0)<span style="color:#ef4444;"> / {{ $sal->days_absent }}A</span>@endif
                    </td>
                    <td>
                        <span class="spill {{ $sal->payment_status === 'Paid' ? 'spill-green' : ($sal->payment_status === 'Processing' ? 'spill-amber' : 'spill-red') }}" style="font-size:.72rem;">
                            {{ $sal->payment_status }}
                        </span>
                        @if($sal->payment_date)
                            <div style="font-size:.68rem;color:#9ca3af;">{{ $sal->payment_date->format('d M') }}</div>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.salaries.show', $sal) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.salaries.payslip', $sal) }}" class="act-btn" title="Payslip" target="_blank"><i class="bi bi-file-earmark-person"></i></a>
                            @if($sal->payment_status !== 'Paid')
                            <button class="act-btn act-view" title="Mark Paid" data-bs-toggle="modal" data-bs-target="#payModal{{ $sal->id }}" style="color:#059669;">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            @endif
                            <form action="{{ route('admin.salaries.destroy', $sal) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete this salary record?')">
                                @csrf @method('DELETE')
                                <button class="act-btn act-delete" title="Delete"><i class="bi bi-trash3"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-5">
                        <i class="bi bi-cash-stack" style="font-size:2rem;opacity:.3;"></i>
                        <div class="mt-2">No salary records for this period. Use <strong>Bulk Gen</strong> or <a href="{{ route('admin.salaries.create') }}">Add Salary Record</a>.</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if($salaries->count() > 0)
            <tfoot>
                <tr style="background:rgba(5,150,105,.05);font-weight:700;">
                    <td colspan="5" class="text-end">TOTAL NET PAYROLL</td>
                    <td class="text-end" style="color:#059669;">&#8377;{{ number_format($salaries->sum('net_salary'),2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

{{-- Mark Paid Modals --}}
@foreach($salaries->where('payment_status', '!=', 'Paid') as $sal)
<div class="modal fade" id="payModal{{ $sal->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Mark as Paid — {{ $sal->employee?->user?->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.salaries.mark-paid', $sal) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="">— Select —</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="alert alert-info p-2" style="font-size:.82rem;">
                        Net Salary: <strong>&#8377;{{ number_format($sal->net_salary,2) }}</strong>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check2 me-1"></i>Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
