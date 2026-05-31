@extends('layouts.app')
@section('title', 'Transactions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Transactions</li>
@endsection

@push('styles')
<style>
.page-hero {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%);
    border-radius: 16px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    color: #fff;
}
.page-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.06);
    border-radius: 50%;
}
.page-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; right: 80px;
    width: 160px; height: 160px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.hero-stat { text-align: center; }
.hero-stat .val { font-size: 1.6rem; font-weight: 800; line-height: 1; }
.hero-stat .lbl { font-size: .72rem; opacity: .7; margin-top: 3px; text-transform: uppercase; letter-spacing: .5px; }
.hero-divider { width: 1px; background: rgba(255,255,255,.2); align-self: stretch; margin: 4px 0; }

.filter-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.filter-card .form-control, .filter-card .form-select {
    border-radius: 8px;
    border: 1.5px solid #e5e7eb;
    font-size: .83rem;
    height: 36px;
    background: #f9fafb;
}
.filter-card .form-control:focus, .filter-card .form-select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79,70,229,.1);
    background: #fff;
}
.btn-filter {
    height: 36px;
    border-radius: 8px;
    font-size: .83rem;
    font-weight: 600;
    padding: 0 16px;
}

.tx-table-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    overflow: hidden;
}
.tx-table-card .card-header {
    background: #fff;
    border-bottom: 1px solid #f3f4f6;
    padding: 16px 20px;
}
.tx-table thead th {
    background: #f8fafc;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #6b7280;
    border-bottom: 1px solid #e5e7eb;
    padding: 10px 14px;
    white-space: nowrap;
}
.tx-table tbody td {
    padding: 12px 14px;
    font-size: .85rem;
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}
.tx-table tbody tr:last-child td { border-bottom: none; }
.tx-table tbody tr:hover { background: #fafbff; }
.tx-table tbody tr.flagged-row { background: #fff8f0; }
.tx-table tbody tr.flagged-row:hover { background: #fff3e8; }

.tx-id-link {
    font-family: 'JetBrains Mono', 'Fira Code', monospace;
    font-size: .78rem;
    font-weight: 700;
    color: #4f46e5;
    text-decoration: none;
    letter-spacing: -.3px;
}
.tx-id-link:hover { color: #3730a3; text-decoration: underline; }

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .73rem;
    font-weight: 600;
}
.status-pill.success  { background: #dcfce7; color: #16a34a; }
.status-pill.failed   { background: #fee2e2; color: #dc2626; }
.status-pill.pending  { background: #fef9c3; color: #ca8a04; }
.status-pill.processing { background: #dbeafe; color: #2563eb; }
.status-pill.cancelled  { background: #f3f4f6; color: #6b7280; }
.status-pill.reversed   { background: #ede9fe; color: #7c3aed; }

.cat-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: .72rem;
    font-weight: 600;
    background: #f3f4f6;
    color: #374151;
}

.risk-bar-wrap { display: flex; align-items: center; gap: 8px; }
.risk-bar { flex: 1; height: 5px; border-radius: 3px; background: #e5e7eb; overflow: hidden; min-width: 50px; }
.risk-bar-fill { height: 100%; border-radius: 3px; }
.risk-val { font-size: .75rem; font-weight: 700; min-width: 26px; }
.risk-low  .risk-bar-fill { background: #22c55e; }
.risk-low  .risk-val      { color: #16a34a; }
.risk-mid  .risk-bar-fill { background: #f59e0b; }
.risk-mid  .risk-val      { color: #b45309; }
.risk-high .risk-bar-fill { background: #ef4444; }
.risk-high .risk-val      { color: #dc2626; }

.flag-badge { display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; background: #fee2e2; color: #dc2626; border-radius: 5px; font-size: .7rem; font-weight: 700; margin-left: 4px; }

.action-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 7px; border: none;
    font-size: .8rem; cursor: pointer; text-decoration: none;
    transition: background .15s;
}
.action-btn.view   { background: #ede9fe; color: #7c3aed; }
.action-btn.view:hover { background: #ddd6fe; }
.action-btn.edit   { background: #fef3c7; color: #d97706; }
.action-btn.edit:hover { background: #fde68a; }

.btn-export { height: 32px; padding: 0 12px; font-size: .78rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 5px; font-weight: 600; }
.btn-new-tx { height: 32px; padding: 0 14px; font-size: .8rem; border-radius: 8px; background: linear-gradient(135deg,#4f46e5,#7c3aed); color: #fff; border: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
.btn-new-tx:hover { opacity: .9; color: #fff; }

.empty-state { text-align: center; padding: 56px 24px; color: #9ca3af; }
.empty-state i { font-size: 2.5rem; display: block; margin-bottom: 12px; opacity: .35; }
.empty-state p { font-size: .9rem; margin: 0; }

.pagination-wrap {
    padding: 12px 20px;
    border-top: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
}
.pagination-info { font-size: .78rem; color: #6b7280; }

/* Modal */
.modal-content { border-radius: 14px; border: none; box-shadow: 0 20px 60px rgba(0,0,0,.15); }
.modal-header { border-bottom: 1px solid #f3f4f6; padding: 16px 20px; }
.modal-footer { border-top: 1px solid #f3f4f6; padding: 12px 20px; }
.modal-body .form-select, .modal-body .form-control { border-radius: 8px; font-size: .85rem; border: 1.5px solid #e5e7eb; }
.modal-body .form-select:focus, .modal-body .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.1); }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4 class="mb-1 fw-800" style="font-weight:800; letter-spacing:-.4px;">Transactions</h4>
            <p class="mb-0" style="opacity:.7; font-size:.85rem;">Monitor, filter and manage all financial transactions</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            @foreach(['total'=>['Total','bi-arrow-left-right'],'success'=>['Success','bi-check-circle'],'failed'=>['Failed','bi-x-circle'],'flagged'=>['Flagged','bi-flag-fill']] as $k=>$v)
            <div class="hero-stat">
                <div class="val">{{ number_format($summary[$k]) }}</div>
                <div class="lbl">{{ $v[0] }}</div>
            </div>
            @if(!$loop->last)<div class="hero-divider"></div>@endif
            @endforeach
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-semibold mb-1" style="color:#374151; font-size:.75rem;">Search</label>
            <div class="position-relative">
                <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:.8rem;"></i>
                <input type="text" name="search" class="form-control ps-4"
                       placeholder="ID, sender, receiver…" value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1" style="color:#374151; font-size:.75rem;">Status</label>
            <select name="status" class="form-select">
                <option value="">All Status</option>
                @foreach(['pending','processing','success','failed','cancelled','reversed'] as $s)
                    <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1" style="color:#374151; font-size:.75rem;">From Date</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold mb-1" style="color:#374151; font-size:.75rem;">To Date</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-1">
            <label class="form-label small fw-semibold mb-1" style="color:#374151; font-size:.75rem;">Fraud</label>
            <select name="is_flagged" class="form-select">
                <option value="">All</option>
                <option value="1" {{ request('is_flagged')=='1'?'selected':'' }}>Flagged</option>
                <option value="0" {{ request('is_flagged')=='0'?'selected':'' }}>Clean</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-filter btn-primary flex-grow-1">
                <i class="bi bi-search me-1"></i>Filter
            </button>
            <a href="{{ route('admin.transactions.index') }}" class="btn btn-filter btn-outline-secondary px-3">
                <i class="bi bi-x-lg"></i>
            </a>
        </div>
    </form>
</div>

{{-- Table Card --}}
<div class="tx-table-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span style="font-weight:700; font-size:.9rem; color:#111827;">All Transactions</span>
            @if($transactions->total())
                <span style="margin-left:8px; background:#ede9fe; color:#7c3aed; font-size:.72rem; font-weight:700; padding:2px 8px; border-radius:20px;">{{ number_format($transactions->total()) }}</span>
            @endif
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('admin.transactions.export.csv') }}" class="btn-export btn btn-outline-success">
                <i class="bi bi-filetype-csv"></i>CSV
            </a>
            <a href="{{ route('admin.transactions.export.pdf') }}" class="btn-export btn btn-outline-danger">
                <i class="bi bi-filetype-pdf"></i>PDF
            </a>
            <a href="{{ route('admin.transactions.create') }}" class="btn-new-tx">
                <i class="bi bi-plus-lg"></i>New Transaction
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table tx-table mb-0">
            <thead>
                <tr>
                    <th style="width:36px;" class="ps-3"><input type="checkbox" id="selectAll" style="border-radius:4px;"></th>
                    <th>Transaction ID</th>
                    <th>User / IP</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Risk Score</th>
                    <th>Date</th>
                    <th style="width:80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                @php $riskClass = $tx->risk_score >= 70 ? 'risk-high' : ($tx->risk_score >= 40 ? 'risk-mid' : 'risk-low'); @endphp
                <tr class="{{ $tx->is_flagged ? 'flagged-row' : '' }}">
                    <td class="ps-3"><input type="checkbox" value="{{ $tx->id }}" class="tx-check" style="border-radius:4px;"></td>
                    <td>
                        <a href="{{ route('admin.transactions.show', $tx) }}" class="tx-id-link">{{ $tx->transaction_id }}</a>
                        @if($tx->is_flagged)
                            <span class="flag-badge"><i class="bi bi-flag-fill"></i>Fraud</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600; font-size:.83rem; color:#111827;">{{ $tx->user?->name ?? $tx->sender_name ?? 'N/A' }}</div>
                        <div style="font-size:.74rem; color:#9ca3af;">{{ $tx->ip_address ?? '—' }}</div>
                    </td>
                    <td>
                        <div style="font-weight:700; font-size:.88rem; color:#111827;">₹{{ number_format($tx->amount, 2) }}</div>
                        <div style="font-size:.74rem; color:#9ca3af;">Fee: ₹{{ number_format($tx->fee, 2) }}</div>
                    </td>
                    <td><span class="cat-pill">{{ $tx->category }}</span></td>
                    <td>
                        <span class="status-pill {{ $tx->status }}">
                            <span style="width:6px;height:6px;border-radius:50%;background:currentColor;display:inline-block;"></span>
                            {{ ucfirst($tx->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="risk-bar-wrap {{ $riskClass }}">
                            <div class="risk-bar"><div class="risk-bar-fill" style="width:{{ $tx->risk_score }}%;"></div></div>
                            <span class="risk-val">{{ $tx->risk_score }}</span>
                        </div>
                    </td>
                    <td style="font-size:.78rem; color:#6b7280; white-space:nowrap;">{{ $tx->created_at->format('M d, H:i') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.transactions.show', $tx) }}" class="action-btn view" title="View"><i class="bi bi-eye"></i></a>
                            <button class="action-btn edit" onclick="changeStatus({{ $tx->id }}, '{{ $tx->status }}')" title="Update Status"><i class="bi bi-pencil"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state">
                        <i class="bi bi-arrow-left-right"></i>
                        <p>No transactions found matching your filters</p>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div class="pagination-wrap">
        <span class="pagination-info">Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ number_format($transactions->total()) }} transactions</span>
        {{ $transactions->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

{{-- Status Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-700" style="font-weight:700;">Update Status</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="statusTxId">
                <div class="mb-3">
                    <label class="form-label" style="font-size:.78rem; font-weight:600; color:#374151;">New Status</label>
                    <select id="newStatus" class="form-select">
                        @foreach(['pending','processing','success','failed','cancelled','reversed'] as $s)
                            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:.78rem; font-weight:600; color:#374151;">Notes</label>
                    <textarea id="statusNotes" class="form-control" rows="2" placeholder="Optional notes…" style="resize:none;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-sm btn-primary" onclick="submitStatusChange()">Update</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function changeStatus(txId, currentStatus) {
    document.getElementById('statusTxId').value = txId;
    document.getElementById('newStatus').value = currentStatus;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}
function submitStatusChange() {
    const id     = document.getElementById('statusTxId').value;
    const status = document.getElementById('newStatus').value;
    const notes  = document.getElementById('statusNotes').value;
    APP.ajax(`/admin/transactions/${id}/status`, 'POST', { status, notes })
        .done(res => { if (res.success) { APP.toast('Status updated!'); setTimeout(() => location.reload(), 1000); } })
        .fail(() => APP.toast('Failed to update status', 'error'));
}
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.tx-check').forEach(c => c.checked = this.checked);
});
</script>
@endpush
