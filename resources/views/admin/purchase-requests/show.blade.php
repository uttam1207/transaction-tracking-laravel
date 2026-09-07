@extends('layouts.app')
@section('title', 'PR — ' . $pr->pr_number)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.procurement.index') }}">Procurement</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchase-requests.index') }}">Purchase Requests</a></li>
    <li class="breadcrumb-item active">{{ $pr->pr_number }}</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $pr->pr_number }}</h4>
            <p>
                <span class="badge bg-{{ $pr->status_color }} me-2">{{ ucfirst($pr->status) }}</span>
                <span class="badge bg-{{ $pr->priority_color }}">{{ ucfirst($pr->priority) }} Priority</span>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @if ($pr->status === 'draft')
            <button class="btn btn-sm btn-outline-info px-4" onclick="submitPR({{ $pr->id }})">
                <i class="bi bi-send me-1"></i>Submit
            </button>
            @endif
            @if ($pr->status === 'submitted')
            <button class="btn btn-sm btn-outline-success px-4" onclick="approvePR({{ $pr->id }})">
                <i class="bi bi-check-circle me-1"></i>Approve
            </button>
            <button class="btn btn-sm btn-outline-danger px-4" data-bs-toggle="modal" data-bs-target="#rejectModal">
                <i class="bi bi-x-circle me-1"></i>Reject
            </button>
            @endif
            <a href="{{ route('admin.purchase-requests.index') }}" class="btn btn-sm btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- Main Details + Items --}}
    <div class="col-md-8">
        <div class="card-glass overflow-hidden">
            {{-- Coloured header band --}}
            <div style="background:linear-gradient(135deg,#0d9488,#059669);padding:18px 24px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none;"></div>
                <div class="d-flex align-items-center gap-3">
                    <div style="width:46px;height:46px;background:rgba(255,255,255,.18);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-file-earmark-text" style="font-size:1.3rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.68rem;font-weight:700;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.08em;">Purchase Request</div>
                        <div style="font-size:1rem;font-weight:800;color:#fff;">{{ $pr->pr_number }}</div>
                        <div style="color:rgba(255,255,255,.7);font-size:.78rem;">
                            Created {{ $pr->created_at->format('d M Y') }}
                            @if($pr->requestedBy) by {{ $pr->requestedBy->name }} @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <h6 class="form-section-label">A — Request Details</h6>
                <div class="row g-3 mb-4">
                    <div class="col-sm-3">
                        <div class="text-muted small mb-1">PR Number</div>
                        <div class="fw-semibold">{{ $pr->pr_number }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small mb-1">Requested By</div>
                        <div>{{ $pr->requestedBy?->name ?? '—' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small mb-1">Department</div>
                        <div>{{ $pr->department?->name ?? '—' }}</div>
                    </div>
                    <div class="col-sm-3">
                        <div class="text-muted small mb-1">Required Date</div>
                        <div>{{ $pr->required_date?->format('d M Y') ?? '—' }}</div>
                    </div>
                    @if ($pr->purpose)
                    <div class="col-12">
                        <div class="text-muted small mb-1">Purpose / Justification</div>
                        <div>{{ $pr->purpose }}</div>
                    </div>
                    @endif
                    @if ($pr->rejection_reason)
                    <div class="col-12">
                        <div class="small mb-1 text-danger fw-semibold">Rejection Reason</div>
                        <div class="text-danger">{{ $pr->rejection_reason }}</div>
                    </div>
                    @endif
                </div>

                <h6 class="form-section-label">B — Items Requested</h6>
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th>Unit</th>
                                <th class="text-end">Est. Unit Price</th>
                                <th class="text-end">Est. Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pr->items as $i => $item)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $item->item_name }}</td>
                                <td class="text-muted" style="font-size:.82rem;">{{ $item->description ?? '—' }}</td>
                                <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td>{{ $item->unit }}</td>
                                <td class="text-end">{{ $item->estimated_unit_price ? '₹'.number_format($item->estimated_unit_price, 2) : '—' }}</td>
                                <td class="text-end fw-semibold">{{ $item->estimated_total ? '₹'.number_format($item->estimated_total, 2) : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @if ($pr->items->sum('estimated_total') > 0)
                        <tfoot>
                            <tr style="background:#f0fdf4;">
                                <td colspan="6" class="text-end fw-bold">Estimated Total</td>
                                <td class="text-end fw-bold" style="color:#059669;">₹{{ number_format($pr->items->sum('estimated_total'), 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Sidebar --}}
    <div class="col-md-4">
        <div class="card-glass p-4">
            <h6 class="form-section-label">Approval Info</h6>
            <dl class="row mb-0 small">
                <dt class="col-5 text-muted fw-normal">Status</dt>
                <dd class="col-7"><span class="badge bg-{{ $pr->status_color }}">{{ ucfirst($pr->status) }}</span></dd>

                <dt class="col-5 text-muted fw-normal">Priority</dt>
                <dd class="col-7"><span class="badge bg-{{ $pr->priority_color }}">{{ ucfirst($pr->priority) }}</span></dd>

                <dt class="col-5 text-muted fw-normal">Created</dt>
                <dd class="col-7">{{ $pr->created_at->format('d M Y') }}</dd>

                @if ($pr->approvedBy)
                <dt class="col-5 text-muted fw-normal">Actioned By</dt>
                <dd class="col-7 fw-semibold">{{ $pr->approvedBy->name }}</dd>

                <dt class="col-5 text-muted fw-normal">Actioned At</dt>
                <dd class="col-7">{{ $pr->approved_at?->format('d M Y H:i') }}</dd>
                @endif
            </dl>

            @if ($pr->status === 'draft')
            <hr>
            <button class="btn btn-outline-info w-100" onclick="submitPR({{ $pr->id }})" style="border-radius:10px;">
                <i class="bi bi-send me-2"></i>Submit for Approval
            </button>
            @endif

            @if ($pr->status === 'submitted')
            <hr>
            <div class="d-flex flex-column gap-2">
                <button class="btn btn-success w-100" onclick="approvePR({{ $pr->id }})" style="border-radius:10px;">
                    <i class="bi bi-check-circle me-2"></i>Approve
                </button>
                <button class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal" style="border-radius:10px;">
                    <i class="bi bi-x-circle me-2"></i>Reject
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2 text-danger"></i>Reject PR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <textarea id="rejectReason" class="form-control" rows="3"
                    placeholder="Reason for rejection…" style="border-radius:10px;"></textarea>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <button type="button" class="btn btn-danger px-4" onclick="doReject()" style="border-radius:10px;">Reject</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function submitPR(id) {
    APP.confirm('Submit PR', 'Submit this purchase request for approval?', () => {
        fetch(`/admin/purchase-requests/${id}/submit`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            if (d.success) { APP.toast(d.message ?? 'Submitted.', 'success'); setTimeout(() => location.reload(), 800); }
            else APP.toast(d.message, 'error');
        });
    });
}

function approvePR(id) {
    APP.confirm('Approve PR', 'Approve this purchase request?', () => {
        fetch(`/admin/purchase-requests/${id}/approve`, {
            method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(r => r.json()).then(d => {
            if (d.success) { APP.toast(d.message ?? 'Approved.', 'success'); setTimeout(() => location.reload(), 800); }
            else APP.toast(d.message, 'error');
        });
    });
}

function doReject() {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) { APP.toast('Please enter a reason.', 'error'); return; }
    fetch(`/admin/purchase-requests/{{ $pr->id }}/reject`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ rejection_reason: reason })
    }).then(r => r.json()).then(d => {
        if (d.success) { APP.toast(d.message ?? 'Rejected.', 'success'); setTimeout(() => location.reload(), 800); }
        else APP.toast(d.message, 'error');
    });
}
</script>
@endpush
