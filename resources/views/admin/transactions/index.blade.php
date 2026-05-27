@extends('layouts.app')
@section('title', 'Transactions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')
<!-- Summary Cards -->
<div class="row g-3 mb-4">
    @foreach(['total' => ['Total', 'primary', 'bi-arrow-left-right'], 'success' => ['Success', 'success', 'bi-check-circle'], 'failed' => ['Failed', 'danger', 'bi-x-circle'], 'flagged' => ['Flagged', 'warning', 'bi-flag']] as $key => $info)
    <div class="col-6 col-md-3">
        <div class="card stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $info[1] }}-subtle text-{{ $info[1] }}">
                    <i class="bi {{ $info[2] }}"></i>
                </div>
                <div>
                    <div class="text-muted small">{{ $info[0] }}</div>
                    <h5 class="mb-0 fw-bold">{{ number_format($summary[$key]) }}</h5>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by ID, sender, receiver..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['pending','processing','success','failed','cancelled','reversed'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" placeholder="From Date">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" placeholder="To Date">
            </div>
            <div class="col-md-1">
                <select name="is_flagged" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('is_flagged') == '1' ? 'selected' : '' }}>Flagged</option>
                    <option value="0" {{ request('is_flagged') == '0' ? 'selected' : '' }}>Clean</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-search"></i> Search</button>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- Transactions Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <h6 class="mb-0 fw-semibold">All Transactions</h6>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transactions.export.csv') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-filetype-csv me-1"></i>CSV
            </a>
            <a href="{{ route('admin.transactions.export.pdf') }}" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-filetype-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('admin.transactions.create') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg me-1"></i>New Transaction
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><input type="checkbox" id="selectAll"></th>
                        <th>Transaction ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Risk</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr class="{{ $tx->is_flagged ? 'table-warning' : '' }}">
                        <td class="ps-3"><input type="checkbox" value="{{ $tx->id }}" class="tx-check"></td>
                        <td>
                            <a href="{{ route('admin.transactions.show', $tx) }}" class="text-decoration-none fw-semibold">
                                {{ $tx->transaction_id }}
                            </a>
                            @if($tx->is_flagged)
                                <i class="bi bi-flag-fill text-danger ms-1" title="Flagged for fraud"></i>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $tx->user?->name ?? $tx->sender_name ?? 'N/A' }}</div>
                            <div class="smaller text-muted">{{ $tx->ip_address }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</div>
                            <div class="small text-muted">Fee: {{ number_format($tx->fee, 2) }}</div>
                        </td>
                        <td><span class="badge bg-secondary-subtle text-secondary">{{ $tx->category }}</span></td>
                        <td>
                            <span class="badge bg-{{ $tx->status_badge }}-subtle text-{{ $tx->status_badge }}">
                                {{ ucfirst($tx->status) }}
                            </span>
                        </td>
                        <td>
                            @php $riskColor = $tx->risk_score >= 70 ? 'danger' : ($tx->risk_score >= 40 ? 'warning' : 'success'); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 5px; width: 60px;">
                                    <div class="progress-bar bg-{{ $riskColor }}" style="width: {{ $tx->risk_score }}%"></div>
                                </div>
                                <span class="small text-{{ $riskColor }}">{{ $tx->risk_score }}%</span>
                            </div>
                        </td>
                        <td><small class="text-muted">{{ $tx->created_at->format('M d, H:i') }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.transactions.show', $tx) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-warning py-0 px-2" onclick="changeStatus({{ $tx->id }}, '{{ $tx->status }}')" title="Change Status">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>No transactions found
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-2">
        <span class="small text-muted">Showing {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} of {{ $transactions->total() }}</span>
        {{ $transactions->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Update Transaction Status</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="statusTxId">
                <div class="mb-3">
                    <label class="form-label small">New Status</label>
                    <select id="newStatus" class="form-select form-select-sm">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="reversed">Reversed</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Notes</label>
                    <textarea id="statusNotes" class="form-control form-control-sm" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-sm btn-primary" onclick="submitStatusChange()">Update Status</button>
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
    const id = document.getElementById('statusTxId').value;
    const status = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;

    APP.ajax(`/admin/transactions/${id}/status`, 'POST', { status, notes })
        .done(res => {
            if (res.success) {
                APP.toast('Status updated successfully!');
                setTimeout(() => location.reload(), 1000);
            }
        })
        .fail(err => APP.toast('Failed to update status', 'error'));
}

// Select All
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.tx-check').forEach(c => c.checked = this.checked);
});
</script>
@endpush
