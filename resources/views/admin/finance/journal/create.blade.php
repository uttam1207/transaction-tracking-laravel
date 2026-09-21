@extends('layouts.app')
@section('title', 'New Journal Entry')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.journal.index') }}">Finance</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.journal.index') }}">Journal Entries</a></li>
    <li class="breadcrumb-item active">New Entry</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>New Journal Entry</h4>
            <p>Create a balanced double-entry journal</p>
        </div>
        <a href="{{ route('admin.finance.journal.index') }}" class="btn btn-sm btn-outline-secondary px-4">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('admin.finance.journal.store') }}">
    @csrf
    <div class="row g-4">
        {{-- Header --}}
        <div class="col-12">
            <div class="card-glass p-4">
                <h6 class="form-section-label mb-3">Entry Header</h6>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Entry Date <span class="text-danger">*</span></label>
                        <input type="date" name="entry_date" class="form-control" value="{{ old('entry_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            @foreach ($types as $t)
                                <option value="{{ $t }}" @selected(old('type', 'general') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Financial Period</label>
                        <select name="period_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach ($periods as $p)
                                <option value="{{ $p->id }}" @selected(old('period_id') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Customer / Contact</label>
                        <select name="contact_id" class="form-select">
                            <option value="">— No Contact —</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}" @selected(old('contact_id') == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Reference</label>
                        <input type="text" name="reference" class="form-control" placeholder="Invoice #, PO #…" value="{{ old('reference') }}">
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Description / Narration</label>
                        <input type="text" name="description" class="form-control" placeholder="Brief memo / narration" value="{{ old('description') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Notes <span style="font-size:.72rem;color:#9ca3af;">(internal — not printed)</span></label>
                        <input type="text" name="notes" class="form-control" placeholder="Internal notes…" value="{{ old('notes') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Lines --}}
        <div class="col-12">
            <div class="card-glass overflow-hidden">
                <div class="px-4 pt-4 pb-3 d-flex justify-content-between align-items-center border-bottom">
                    <span class="fw-semibold">Entry Lines <span style="font-size:.78rem;color:#9ca3af;font-weight:400;">(Debit must equal Credit)</span></span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLine()">
                        <i class="bi bi-plus-lg me-1"></i>Add Line
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="modern-table table mb-0" id="linesTable">
                        <thead>
                            <tr>
                                <th style="min-width:280px">Account</th>
                                <th>Description</th>
                                <th style="width:140px" class="text-end">Debit (Dr)</th>
                                <th style="width:140px" class="text-end">Credit (Cr)</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody id="linesBody"></tbody>
                        <tfoot class="fw-semibold" style="background:#f8fafc;">
                            <tr>
                                <td colspan="2" class="text-end" style="padding:12px 16px;">Totals:</td>
                                <td class="text-end" id="totalDebit" style="padding:12px 16px;font-size:1rem;">0.00</td>
                                <td class="text-end" id="totalCredit" style="padding:12px 16px;font-size:1rem;">0.00</td>
                                <td></td>
                            </tr>
                            <tr id="balanceRow" style="display:none;">
                                <td colspan="5" class="text-center" id="balanceMsg" style="padding:6px;font-size:.8rem;font-weight:700;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.finance.journal.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
            <button type="submit" class="btn btn-primary-grad px-5">
                <i class="bi bi-check-lg me-1"></i>Save Journal Entry
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
const accounts = @json($accounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name]));

let lineIdx = 0;

function addLine(data) {
    data = data || {};
    const tbody = document.getElementById('linesBody');
    const i = lineIdx++;
    const options = accounts.map(a =>
        `<option value="${a.id}" ${String(data.account_id) === String(a.id) ? 'selected' : ''}>${a.code} — ${a.name}</option>`
    ).join('');
    const row = `<tr id="line_${i}" style="border-bottom:1px solid #f1f5f9;">
        <td style="padding:6px 8px;"><select name="lines[${i}][account_id]" class="form-select form-select-sm" required>
            <option value="">Select account…</option>${options}
        </select></td>
        <td style="padding:6px 8px;"><input type="text" name="lines[${i}][description]" class="form-control form-control-sm" placeholder="Memo" value="${(data.description||'').replace(/"/g,'&quot;')}"></td>
        <td style="padding:6px 8px;"><input type="number" name="lines[${i}][debit]" class="form-control form-control-sm text-end debit-input" step="0.01" min="0" value="${data.debit||0}" oninput="updateTotals()"></td>
        <td style="padding:6px 8px;"><input type="number" name="lines[${i}][credit]" class="form-control form-control-sm text-end credit-input" step="0.01" min="0" value="${data.credit||0}" oninput="updateTotals()"></td>
        <td style="padding:6px 8px;text-align:center;">
            <button type="button" onclick="removeLine(${i})" style="background:transparent;border:1px solid #fca5a5;color:#dc2626;border-radius:6px;padding:2px 7px;cursor:pointer;" title="Remove">
                <i class="bi bi-x-lg" style="font-size:.7rem;"></i>
            </button>
        </td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
    updateTotals();
}

function removeLine(i) {
    document.getElementById(`line_${i}`)?.remove();
    updateTotals();
}

function updateTotals() {
    let d = 0, c = 0;
    document.querySelectorAll('.debit-input').forEach(el => d += parseFloat(el.value) || 0);
    document.querySelectorAll('.credit-input').forEach(el => c += parseFloat(el.value) || 0);
    const diff = Math.abs(d - c);
    const balanced = diff < 0.01;

    document.getElementById('totalDebit').textContent  = d.toFixed(2);
    document.getElementById('totalCredit').textContent = c.toFixed(2);
    document.getElementById('totalDebit').style.color  = balanced && d > 0 ? '#059669' : (d > 0 ? '#dc2626' : '#1f2937');
    document.getElementById('totalCredit').style.color = balanced && c > 0 ? '#059669' : (c > 0 ? '#dc2626' : '#1f2937');

    const balRow = document.getElementById('balanceRow');
    const balMsg = document.getElementById('balanceMsg');
    if (!balanced && d > 0) {
        balRow.style.display = '';
        balMsg.style.background = '#fff1f2';
        balMsg.style.color = '#dc2626';
        balMsg.textContent = `\u26A0 Unbalanced \u2014 difference: \u20B9${diff.toFixed(2)}`;
    } else if (balanced && d > 0) {
        balRow.style.display = '';
        balMsg.style.background = '#f0fdf4';
        balMsg.style.color = '#059669';
        balMsg.textContent = '\u2713 Balanced \u2014 ready to save';
    } else {
        balRow.style.display = 'none';
    }
}

// Start with 2 default lines
addLine(); addLine();
</script>
@endpush
@endsection
