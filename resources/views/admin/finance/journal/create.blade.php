@extends('layouts.app')

@section('title', 'New Journal Entry')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">New Journal Entry</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.finance.journal.index') }}">Journal Entries</a></li>
                <li class="breadcrumb-item active">New Entry</li>
            </ol></nav>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">Entry Header</div>
                    <div class="card-body row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                            <input type="date" name="entry_date" class="form-control" value="{{ old('entry_date', now()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                @foreach ($types as $t)
                                    <option value="{{ $t }}" @selected(old('type', 'general') === $t)>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Financial Period</label>
                            <select name="period_id" class="form-select">
                                <option value="">None</option>
                                @foreach ($periods as $p)
                                    <option value="{{ $p->id }}" @selected(old('period_id') == $p->id)>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Reference</label>
                            <input type="text" name="reference" class="form-control" placeholder="Invoice #, PO #…" value="{{ old('reference') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <input type="text" name="description" class="form-control" placeholder="Memo / narration" value="{{ old('description') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lines --}}
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
                        <span>Entry Lines (Debit = Credit)</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLine()">
                            <i class="bi bi-plus-lg me-1"></i> Add Line
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="linesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width:280px">Account</th>
                                        <th>Description</th>
                                        <th style="width:140px" class="text-end">Debit</th>
                                        <th style="width:140px" class="text-end">Credit</th>
                                        <th style="width:50px"></th>
                                    </tr>
                                </thead>
                                <tbody id="linesBody"></tbody>
                                <tfoot class="table-light fw-semibold">
                                    <tr>
                                        <td colspan="2" class="text-end">Totals:</td>
                                        <td class="text-end" id="totalDebit">0.00</td>
                                        <td class="text-end" id="totalCredit">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <a href="{{ route('admin.finance.journal.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Journal Entry</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
const accounts = @json($accounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name]));

let lineIdx = 0;

function addLine() {
    const tbody = document.getElementById('linesBody');
    const i = lineIdx++;
    const options = accounts.map(a => `<option value="${a.id}">${a.code} — ${a.name}</option>`).join('');
    const row = `<tr id="line_${i}">
        <td><select name="lines[${i}][account_id]" class="form-select form-select-sm" required><option value="">Select account…</option>${options}</select></td>
        <td><input type="text" name="lines[${i}][description]" class="form-control form-control-sm" placeholder="Memo"></td>
        <td><input type="number" name="lines[${i}][debit]" class="form-control form-control-sm text-end debit-input" step="0.01" min="0" value="0" oninput="updateTotals()"></td>
        <td><input type="number" name="lines[${i}][credit]" class="form-control form-control-sm text-end credit-input" step="0.01" min="0" value="0" oninput="updateTotals()"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(${i})"><i class="bi bi-trash"></i></button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
}

function removeLine(i) {
    document.getElementById(`line_${i}`)?.remove();
    updateTotals();
}

function updateTotals() {
    let d = 0, c = 0;
    document.querySelectorAll('.debit-input').forEach(el => d += parseFloat(el.value) || 0);
    document.querySelectorAll('.credit-input').forEach(el => c += parseFloat(el.value) || 0);
    document.getElementById('totalDebit').textContent = d.toFixed(2);
    document.getElementById('totalCredit').textContent = c.toFixed(2);
    document.getElementById('totalDebit').classList.toggle('text-danger', Math.abs(d - c) > 0.001);
    document.getElementById('totalCredit').classList.toggle('text-danger', Math.abs(d - c) > 0.001);
}

// Start with 2 default lines
addLine(); addLine();
</script>
@endpush
@endsection
