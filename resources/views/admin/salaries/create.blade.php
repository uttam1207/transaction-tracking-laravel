@extends('layouts.app')
@section('title', 'Add Salary Record')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.salaries.index') }}">Salary & Payroll</a></li>
    <li class="breadcrumb-item active">Add Record</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Add Salary Record</h4>
            <p>Create or update a salary entry for a specific employee and month</p>
        </div>
        <a href="{{ route('admin.salaries.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card-glass p-4">

            @if($errors->any())
                <div class="alert alert-danger mb-4"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form action="{{ route('admin.salaries.store') }}" method="POST">
                @csrf

                <h6 class="form-section-label">A — Employee & Period</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" id="empSelect" required onchange="prefillSalary(this)">
                            <option value="">— Select Employee —</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}"
                                    data-salary="{{ $emp->salary ?? 0 }}"
                                    data-name="{{ $emp->user?->name }}"
                                    @selected(old('employee_id') == $emp->id)>
                                    {{ $emp->employee_id }} — {{ $emp->user?->name }} ({{ $emp->department?->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Year <span class="text-danger">*</span></label>
                        <select name="year" class="form-select @error('year') is-invalid @enderror" required>
                            @for($y = now()->year; $y >= 2024; $y--)
                                <option value="{{ $y }}" @selected((old('year', now()->year)) == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                        @error('year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Month <span class="text-danger">*</span></label>
                        <select name="month_number" class="form-select @error('month_number') is-invalid @enderror" required>
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" @selected(old('month_number', now()->month) == $m)>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endforeach
                        </select>
                        @error('month_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Payment Status</label>
                        <select name="payment_status" class="form-select" required>
                            <option value="Pending" @selected(old('payment_status')=='Pending')>Pending</option>
                            <option value="Processing" @selected(old('payment_status')=='Processing')>Processing</option>
                            <option value="Paid" @selected(old('payment_status')=='Paid')>Paid</option>
                        </select>
                    </div>
                </div>

                <hr class="my-3 opacity-25">
                <h6 class="form-section-label">B — Earnings</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Basic Salary (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="basic_salary" id="basicSalary"
                            class="form-control @error('basic_salary') is-invalid @enderror"
                            value="{{ old('basic_salary', 0) }}" required oninput="calcNet()">
                        @error('basic_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">HRA (₹)</label>
                        <input type="number" step="0.01" min="0" name="hra" id="hraInput"
                            class="form-control" value="{{ old('hra', 0) }}" oninput="calcNet()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Other Allowances (₹)</label>
                        <input type="number" step="0.01" min="0" name="other_allowances"
                            class="form-control" value="{{ old('other_allowances', 0) }}" id="otherAllow" oninput="calcNet()">
                    </div>
                </div>

                <hr class="my-3 opacity-25">
                <h6 class="form-section-label">C — Deductions</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">PF Deduction (₹)</label>
                        <input type="number" step="0.01" min="0" name="pf_deduction" id="pfInput"
                            class="form-control" value="{{ old('pf_deduction', 0) }}" oninput="calcNet()">
                        <div style="font-size:.7rem;color:#9ca3af;margin-top:3px;">Typically 12% of basic</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tax Deduction (₹)</label>
                        <input type="number" step="0.01" min="0" name="tax_deduction"
                            class="form-control" value="{{ old('tax_deduction', 0) }}" id="taxInput" oninput="calcNet()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Other Deductions (₹)</label>
                        <input type="number" step="0.01" min="0" name="other_deductions"
                            class="form-control" value="{{ old('other_deductions', 0) }}" id="otherDed" oninput="calcNet()">
                    </div>
                </div>

                <hr class="my-3 opacity-25">
                <h6 class="form-section-label">D — Attendance & Payment</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Days Worked</label>
                        <input type="number" step="0.5" min="0" max="31" name="days_worked"
                            class="form-control" value="{{ old('days_worked', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Days Absent</label>
                        <input type="number" step="0.5" min="0" max="31" name="days_absent"
                            class="form-control" value="{{ old('days_absent', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Payment Mode</label>
                        <select name="payment_mode" class="form-select">
                            <option value="">— Select —</option>
                            <option value="Bank Transfer" @selected(old('payment_mode')=='Bank Transfer')>Bank Transfer</option>
                            <option value="Cash" @selected(old('payment_mode')=='Cash')>Cash</option>
                            <option value="UPI" @selected(old('payment_mode')=='UPI')>UPI</option>
                            <option value="Cheque" @selected(old('payment_mode')=='Cheque')>Cheque</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes…">{{ old('remarks') }}</textarea>
                    </div>
                </div>

                {{-- Net Pay Preview --}}
                <div class="p-3 rounded mb-4" style="background:rgba(5,150,105,.08);border:1px solid rgba(5,150,105,.2);">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div style="font-size:.72rem;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:.07em;">Net Pay (Preview)</div>
                            <div id="netPayPreview" style="font-size:1.8rem;font-weight:800;color:#059669;letter-spacing:-1px;">&#8377;0</div>
                        </div>
                        <div style="font-size:.8rem;color:#6b7280;text-align:right;">
                            <div>Gross: <span id="grossPreview" class="fw-semibold">&#8377;0</span></div>
                            <div>Deductions: <span id="deductPreview" class="fw-semibold text-danger">&#8377;0</span></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.salaries.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary-grad px-5"><i class="bi bi-check-lg me-1"></i>Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function prefillSalary(sel) {
    const opt = sel.options[sel.selectedIndex];
    const basic = parseFloat(opt.dataset.salary || 0);
    document.getElementById('basicSalary').value = basic.toFixed(2);
    document.getElementById('hraInput').value = (basic * 0.20).toFixed(2);
    document.getElementById('pfInput').value = (basic * 0.12).toFixed(2);
    calcNet();
}

function calcNet() {
    const basic = parseFloat(document.getElementById('basicSalary').value || 0);
    const hra = parseFloat(document.getElementById('hraInput').value || 0);
    const otherAllow = parseFloat(document.getElementById('otherAllow').value || 0);
    const pf = parseFloat(document.getElementById('pfInput').value || 0);
    const tax = parseFloat(document.getElementById('taxInput').value || 0);
    const otherDed = parseFloat(document.getElementById('otherDed').value || 0);
    const gross = basic + hra + otherAllow;
    const deduct = pf + tax + otherDed;
    const net = gross - deduct;
    document.getElementById('grossPreview').textContent = '₹' + gross.toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('deductPreview').textContent = '₹' + deduct.toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('netPayPreview').textContent = '₹' + net.toLocaleString('en-IN', {minimumFractionDigits:2});
}
calcNet();
</script>
@endpush
