@extends('layouts.app')
@section('title', 'Work Reports Review')
@section('breadcrumb')
    <li class="breadcrumb-item active">Work Reports</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Work Reports</h4>
            <p>Review and approve employee daily work reports</p>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="page-hero-stat"><div class="v" style="color:#94a3b8;">{{ $stats['draft'] }}</div><div class="l">Draft</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#fde047;">{{ $stats['submitted'] }}</div><div class="l">Submitted</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#86efac;">{{ $stats['approved'] }}</div><div class="l">Approved</div></div>
            <div class="hero-vr"></div>
            <div class="page-hero-stat"><div class="v" style="color:#fca5a5;">{{ $stats['rejected'] }}</div><div class="l">Rejected</div></div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="flabel">Employee</label>
            <select name="employee_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->full_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Status</option>
                @foreach(['draft','submitted','approved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Month</label>
            <select name="month" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Months</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label class="flabel">Year</label>
            <select name="year" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                @foreach(range(date('Y'),date('Y')-2) as $y)
                    <option value="{{ $y }}" {{ request('year',$y==date('Y')?$y:0) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button class="btn btn-sm btn-primary-grad px-4">Filter</button>
        </div>
        <div class="col-md-auto">
            <a href="{{ route('admin.work-reports.index') }}" class="btn btn-sm btn-outline-secondary px-3">Reset</a>
        </div>
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">All Work Reports</span>
        <form method="POST" action="{{ route('admin.work-reports.bulk-approve') }}" id="bulkForm">
            @csrf
            <button type="submit" class="btn btn-sm act-btn act-green px-3" id="bulkBtn" disabled style="border-radius:7px;padding:5px 14px;font-size:.8rem;">
                <i class="bi bi-check-all me-1"></i>Approve Selected
            </button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="selectAll" class="form-check-input"></th>
                    <th>Employee</th>
                    <th>Report Date</th>
                    <th>Hours</th>
                    <th>Score</th>
                    <th>Summary</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                @php
                    $sc = $report->productivity_score ?? 0;
                    $scoreColor = $sc >= 80 ? '#16a34a' : ($sc >= 50 ? '#d97706' : '#dc2626');
                    $statusMap = ['draft'=>'secondary','submitted'=>'warning','approved'=>'success','rejected'=>'danger'];
                @endphp
                <tr>
                    <td>
                        @if($report->status === 'submitted')
                        <input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $report->id }}" form="bulkForm">
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $report->employee?->full_name }}</div>
                        <div style="font-size:.72rem;color:#9ca3af;">{{ $report->employee?->designation }}</div>
                    </td>
                    <td style="font-size:.83rem;">{{ $report->report_date?->format('d M Y') }}</td>
                    <td>
                        <span style="background:#eff6ff;color:#2563eb;padding:3px 8px;border-radius:6px;font-size:.75rem;font-weight:700;">{{ $report->hours_worked }}h</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;min-width:90px;">
                            <div style="flex:1;height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $sc }}%;height:100%;background:{{ $scoreColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:.75rem;font-weight:700;color:{{ $scoreColor }};min-width:28px;">{{ $sc }}%</span>
                        </div>
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;max-width:200px;">
                        <span title="{{ $report->summary }}">{{ Str::limit($report->summary, 45) }}</span>
                    </td>
                    <td style="font-size:.78rem;color:#9ca3af;">{{ $report->submitted_at?->diffForHumans() ?? '—' }}</td>
                    <td>
                        @php $st = $report->status ?? 'draft'; @endphp
                        <span class="spill spill-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'submitted' ? 'warning' : 'secondary')) }}">{{ ucfirst($st) }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.work-reports.show', $report) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            @if($report->status === 'submitted')
                            <form method="POST" action="{{ route('admin.work-reports.approve', $report) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="act-btn act-green" title="Approve"><i class="bi bi-check2"></i></button>
                            </form>
                            <button class="act-btn act-delete" title="Reject" onclick="openRejectModal({{ $report->id }})">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9">
                    <div class="empty-state"><i class="bi bi-journal-text"></i><p>No work reports found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div class="pagination-wrap">{{ $reports->withQueryString()->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form method="POST" id="rejectForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Reject Work Report</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="flabel">Reason <span class="req">*</span></label>
                <textarea name="reviewer_notes" class="form-control" rows="3" required
                    placeholder="Explain why this report is being rejected…"
                    style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openRejectModal(id) {
    document.getElementById('rejectForm').action = `/admin/work-reports/${id}/reject`;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBtn();
});
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBulkBtn));
function updateBulkBtn() {
    const n = document.querySelectorAll('.row-check:checked').length;
    const btn = document.getElementById('bulkBtn');
    btn.disabled = !n;
    btn.innerHTML = n
        ? `<i class="bi bi-check-all me-1"></i>Approve Selected (${n})`
        : '<i class="bi bi-check-all me-1"></i>Approve Selected';
}
</script>
@endpush
