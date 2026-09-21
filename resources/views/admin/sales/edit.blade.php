@extends('layouts.app')
@section('title', 'Edit Invoice — ' . $salesOrder->invoice_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.index') }}">Sales</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sales.show', $salesOrder) }}">{{ $salesOrder->invoice_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Edit Sales Invoice</h4>
            <p>{{ $salesOrder->invoice_number }} &mdash; {{ $salesOrder->sale_date->format('d M Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales.show', $salesOrder) }}" class="btn btn-sm btn-outline-info px-4">
                <i class="bi bi-eye me-1"></i>View
            </a>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="card-glass overflow-hidden">

    {{-- Header band --}}
    <div style="background:linear-gradient(135deg,#059669,#16a34a);padding:20px 28px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
        <div class="d-flex align-items-center gap-3">
            <div style="width:48px;height:48px;background:rgba(255,255,255,.18);border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-receipt-cutoff" style="font-size:1.3rem;color:#fff;"></i>
            </div>
            <div>
                <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Edit Invoice</div>
                <div style="font-size:1.05rem;font-weight:800;color:#fff;">{{ $salesOrder->invoice_number }}</div>
            </div>
            @if($salesOrder->journal_entry_id)
            <div class="ms-auto">
                <div style="background:rgba(255,193,7,.25);border:1px solid rgba(255,193,7,.5);border-radius:8px;padding:6px 14px;font-size:.75rem;color:#fff;">
                    <i class="bi bi-info-circle me-1"></i>This invoice has a posted accounting entry — changes will reverse and repost it automatically.
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="p-4">

        @if($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('admin.sales.update', $salesOrder) }}" method="POST" id="saleForm">
            @csrf @method('PUT')

            {{-- ── A — Invoice Header ────────────────────────────────────── --}}
            <h6 class="form-section-label mb-3">A — Invoice Details</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Invoice Number <span class="text-danger">*</span></label>
                    <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror"
                        value="{{ old('invoice_number', $salesOrder->invoice_number) }}" required>
                    @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
                    <input type="date" name="sale_date" id="saleDate" class="form-control @error('sale_date') is-invalid @enderror"
                        value="{{ old('sale_date', $salesOrder->sale_date->toDateString()) }}" required>
                    @error('sale_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Customer</label>
                    <select name="crm_customer_id" class="form-select @error('crm_customer_id') is-invalid @enderror">
                        <option value="">— Walk-in / Retail —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('crm_customer_id', $salesOrder->crm_customer_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('crm_customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Payment Terms</label>
                    <select name="payment_terms" id="paymentTerms" class="form-select @error('payment_terms') is-invalid @enderror" onchange="applyPaymentTerms()">
                        <option value="">— No Terms —</option>
                        @foreach(['Due on Receipt','Net 15','Net 30','Net 45','Net 60'] as $pt)
                            <option value="{{ $pt }}" @selected(old('payment_terms', $salesOrder->payment_terms) === $pt)>{{ $pt }}</option>
                        @endforeach
                    </select>
                    @error('payment_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Due Date</label>
                    <input type="date" name="due_date" id="dueDate" class="form-control @error('due_date') is-invalid @enderror"
                        value="{{ old('due_date', $salesOrder->due_date?->toDateString()) }}">
                    <div style="font-size:.68rem;color:#9ca3af;margin-top:3px;">Auto-set by terms, or enter manually</div>
                    @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-3 opacity-25">

            {{-- ── B — Line Items ─────────────────────────────────────────── --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="form-section-label mb-0">B — Items</h6>
                <a href="{{ route('admin.sales.item-types.index') }}" target="_blank" style="font-size:.75rem;color:#6b7280;">
                    <i class="bi bi-gear me-1"></i>Manage Item Types
                </a>
            </div>

            @error('items')<div class="alert alert-danger py-2 mb-3">{{ $message }}</div>@enderror

            <div class="table-responsive mb-2">
                <table class="table mb-0" id="itemsTable" style="min-width:820px;">
                    <thead style="background:#f8fafc;">
                        <tr style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;font-weight:700;">
                            <th style="width:36px;padding:10px 8px;">#</th>
                            <th style="min-width:180px;padding:10px 8px;">Item Type</th>
                            <th style="min-width:160px;padding:10px 8px;">Description</th>
                            <th style="min-width:90px;padding:10px 8px;">Quantity</th>
                            <th style="min-width:230px;padding:10px 8px;">Rate / [Fat% × Fat Rate]</th>
                            <th style="min-width:110px;padding:10px 8px;text-align:right;">Amount</th>
                            <th style="width:40px;padding:10px 8px;"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody"></tbody>
                    <tfoot>
                        <tr style="background:#f0fdf4;border-top:2px solid #d1fae5;">
                            <td colspan="5" class="text-end fw-bold pe-3" style="padding:12px 8px;font-size:.9rem;color:#374151;">Grand Total</td>
                            <td class="text-end" style="padding:12px 8px;">
                                <span id="grandTotalDisplay" style="font-size:1.1rem;font-weight:800;color:#059669;">₹0.00</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <button type="button" id="addRowBtn" class="btn btn-sm btn-outline-success mb-4" style="border-radius:8px;">
                <i class="bi bi-plus-lg me-1"></i>Add New Row
            </button>

            <hr class="my-3 opacity-25">

            {{-- ── C — Payment ─────────────────────────────────────────────── --}}
            <h6 class="form-section-label mb-3">C — Payment</h6>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                    <select name="payment_status" id="paymentStatus" class="form-select @error('payment_status') is-invalid @enderror" required onchange="toggleAmountPaid()">
                        @foreach(['Paid' => 'Paid (Invoice issued &amp; received)', 'Pending' => 'Pending (Invoice issued, not paid)', 'Partial' => 'Partial (Invoice issued, partly paid)', 'Unbilled' => 'Unbilled (Goods delivered, no invoice yet)'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('payment_status', $salesOrder->payment_status) === $val)>{!! $label !!}</option>
                        @endforeach
                    </select>
                    @error('payment_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 {{ old('payment_status', $salesOrder->payment_status) === 'Partial' ? '' : 'd-none' }}" id="amountPaidRow">
                    <label class="form-label fw-semibold">Amount Already Received (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="amount_paid" id="amountPaid"
                        class="form-control @error('amount_paid') is-invalid @enderror"
                        placeholder="0.00" value="{{ old('amount_paid', $salesOrder->amount_paid) }}">
                    <div style="font-size:.68rem;color:#9ca3af;margin-top:3px;">Amount received so far</div>
                    @error('amount_paid')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 pt-4">
                <a href="{{ route('admin.sales.show', $salesOrder) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary-grad px-5">
                    <i class="bi bi-check-lg me-1"></i>Update Invoice
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const TYPES = @json($itemTypes->keyBy('id'));
    const INIT_ITEMS = @json(old('items') ? old('items') : $existingItems);

    let rowIndex = 0;

    function addRow(data) {
        data = data || {};
        const idx  = rowIndex++;
        const tbody = document.getElementById('itemsBody');
        const tr   = document.createElement('tr');
        tr.dataset.row = idx;
        tr.style.borderBottom = '1px solid #f1f5f9';

        const typeOptions = Object.values(TYPES).map(t =>
            `<option value="${t.id}" data-milk="${t.is_milk_type ? '1':'0'}"
             ${String(data.sale_item_type_id) === String(t.id) ? 'selected' : ''}>${t.name}</option>`
        ).join('');

        const isMilk  = isMilkType(data.sale_item_type_id);
        const qty     = data.quantity   || '';
        const rate    = (data.rate && !isMilk) ? data.rate : '';
        const fatPct  = data.fat_percentage || '';
        const fatRate = data.fat_rate || '';
        const desc    = (data.description || '').replace(/"/g, '&quot;');
        const amount  = parseFloat(data.amount) || 0;

        tr.innerHTML = `
            <td class="row-num text-muted fw-semibold" style="padding:8px;font-size:.82rem;vertical-align:middle;">${tbody.rows.length + 1}</td>
            <td style="padding:6px 8px;vertical-align:top;">
                <select name="items[${idx}][sale_item_type_id]" class="form-select form-select-sm item-type-select" required>
                    <option value="">— Select —</option>
                    ${typeOptions}
                </select>
            </td>
            <td style="padding:6px 8px;vertical-align:top;">
                <input type="text" name="items[${idx}][description]" class="form-control form-control-sm"
                       placeholder="Description (optional)" value="${desc}">
            </td>
            <td style="padding:6px 8px;vertical-align:top;">
                <input type="number" name="items[${idx}][quantity]" class="form-control form-control-sm item-qty"
                       step="0.01" min="0.01" placeholder="0" value="${qty}" required>
            </td>
            <td style="padding:6px 8px;vertical-align:top;">
                <div class="rate-group${isMilk ? ' d-none' : ''}">
                    <input type="number" name="items[${idx}][rate]" class="form-control form-control-sm item-rate"
                           step="0.01" min="0" placeholder="₹ per unit" value="${rate}"
                           ${isMilk ? '' : 'required'}>
                </div>
                <div class="fat-group${isMilk ? '' : ' d-none'}">
                    <div class="d-flex align-items-center gap-1">
                        <input type="number" name="items[${idx}][fat_percentage]" class="form-control form-control-sm item-fat"
                               step="0.01" min="0.01" max="100" placeholder="Fat %" value="${fatPct}"
                               ${isMilk ? 'required' : ''}>
                        <span class="text-muted px-1" style="font-size:.85rem;">×</span>
                        <input type="number" name="items[${idx}][fat_rate]" class="form-control form-control-sm item-fat-rate"
                               step="0.01" min="0.01" placeholder="Fat Rate" value="${fatRate}"
                               ${isMilk ? 'required' : ''}>
                    </div>
                    <div style="font-size:.65rem;color:#9ca3af;margin-top:2px;">Qty × Fat% × Fat Rate</div>
                </div>
            </td>
            <td style="padding:6px 8px;text-align:right;vertical-align:middle;">
                <div class="item-amount-display fw-bold" style="color:#059669;font-size:.95rem;">
                    ${amount > 0 ? '₹' + amount.toFixed(2) : '—'}
                </div>
                <input type="hidden" name="items[${idx}][amount]" class="item-amount-input" value="${amount.toFixed(2)}">
            </td>
            <td style="padding:6px 8px;text-align:center;vertical-align:middle;">
                <button type="button" class="btn btn-sm remove-row"
                        style="padding:2px 7px;border-radius:6px;border:1px solid #fca5a5;color:#dc2626;background:transparent;"
                        title="Remove row">
                    <i class="bi bi-x-lg" style="font-size:.7rem;"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        renumber();
        recalcRow(tr);
    }

    function isMilkType(id) {
        if (!id) return false;
        const t = TYPES[id];
        return t ? t.is_milk_type == 1 || t.is_milk_type === true : false;
    }

    function updateRowType(tr) {
        const sel  = tr.querySelector('.item-type-select');
        const milk = isMilkType(sel.value);
        const rg   = tr.querySelector('.rate-group');
        const fg   = tr.querySelector('.fat-group');
        const ri   = tr.querySelector('.item-rate');
        const fi   = tr.querySelector('.item-fat');
        const fri  = tr.querySelector('.item-fat-rate');

        rg.classList.toggle('d-none', milk);
        fg.classList.toggle('d-none', !milk);

        if (ri)  { ri.required  = !milk; if (milk)  ri.value  = ''; }
        if (fi)  { fi.required  =  milk; if (!milk) fi.value  = ''; }
        if (fri) { fri.required =  milk; if (!milk) fri.value = ''; }

        recalcRow(tr);
    }

    function recalcRow(tr) {
        const sel  = tr.querySelector('.item-type-select');
        const qty  = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
        const milk = isMilkType(sel?.value);
        let amount = 0;

        if (milk) {
            const fat = parseFloat(tr.querySelector('.item-fat')?.value)      || 0;
            const fr  = parseFloat(tr.querySelector('.item-fat-rate')?.value) || 0;
            amount = qty * fat * fr;
        } else {
            const rate = parseFloat(tr.querySelector('.item-rate')?.value) || 0;
            amount = qty * rate;
        }

        const display = tr.querySelector('.item-amount-display');
        const hidden  = tr.querySelector('.item-amount-input');
        if (display) display.textContent = amount > 0 ? '₹' + amount.toFixed(2) : '—';
        if (hidden)  hidden.value        = amount.toFixed(2);

        recalcTotal();
    }

    function recalcTotal() {
        let total = 0;
        document.querySelectorAll('#itemsBody .item-amount-input').forEach(i => {
            total += parseFloat(i.value) || 0;
        });
        document.getElementById('grandTotalDisplay').textContent = '₹' + total.toFixed(2);
    }

    function renumber() {
        document.querySelectorAll('#itemsBody tr .row-num').forEach((el, i) => {
            el.textContent = i + 1;
        });
    }

    document.getElementById('itemsBody').addEventListener('change', function (e) {
        if (e.target.classList.contains('item-type-select')) updateRowType(e.target.closest('tr'));
    });
    document.getElementById('itemsBody').addEventListener('input', function (e) {
        const tr = e.target.closest('tr');
        if (tr) recalcRow(tr);
    });
    document.getElementById('itemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (!btn) return;
        if (document.getElementById('itemsBody').rows.length <= 1) {
            alert('An invoice must have at least one item.');
            return;
        }
        btn.closest('tr').remove();
        renumber();
        recalcTotal();
    });

    document.getElementById('addRowBtn').addEventListener('click', () => addRow());

    /* ── Payment terms → auto due date ──────────────────────────── */
    function applyPaymentTerms() {
        const terms     = document.getElementById('paymentTerms').value;
        const saleDate  = document.getElementById('saleDate').value;
        const dueDateEl = document.getElementById('dueDate');
        if (!terms || !saleDate) return;
        const d = new Date(saleDate);
        const map = { 'Due on Receipt': 0, 'Net 15': 15, 'Net 30': 30, 'Net 45': 45, 'Net 60': 60 };
        if (map[terms] !== undefined) {
            d.setDate(d.getDate() + map[terms]);
            dueDateEl.value = d.toISOString().split('T')[0];
        }
    }
    window.applyPaymentTerms = applyPaymentTerms;
    document.getElementById('saleDate').addEventListener('change', function () {
        if (document.getElementById('paymentTerms').value) applyPaymentTerms();
    });

    /* Init with existing/old items */
    (Array.isArray(INIT_ITEMS) ? INIT_ITEMS : Object.values(INIT_ITEMS)).forEach(item => addRow(item));
    if (document.getElementById('itemsBody').rows.length === 0) addRow();

    /* Payment status toggle */
    function toggleAmountPaid() {
        const status = document.getElementById('paymentStatus').value;
        document.getElementById('amountPaidRow').classList.toggle('d-none', status !== 'Partial');
    }
    window.toggleAmountPaid = toggleAmountPaid;
    toggleAmountPaid();
})();
</script>
@endpush

@endsection
