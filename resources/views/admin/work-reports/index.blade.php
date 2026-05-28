@extends('layouts.app')
@section('title', 'Work Reports Review')
@section('breadcrumb')
    <li class="breadcrumb-item active">Work Reports</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Work Reports Review</h4>
        <p class="text-muted small mb-0">Review and approve employee work reports</p>
    </div>
    <form method="POST" action="{{ route('admin.work-reports.bulk-approve') }}" id="bulkForm">
        @csrf
        <button type="submit" class="btn btn-success btn-sm" id="bulkBtn" disabled>
            <i class="bi bi-check-all me-1"></i>Approve Selected
        </button>
    </form>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    @foreach(['draft'=>['secondary','journal'],'submitted'=>['warning','send'],'approved'=>['success','check-circle'],'rejected'=>['danger','x-circle']] as $s => [$color,$icon])
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $color }} bg-opacity-15 text-{{ $color }}"><i class="bi bi-{{ $icon }}"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ $stats[$s] }}</div>
                    <div class="text-muted small">{{ ucfirst($s) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <select name="employee_id" class="form-select form-select-sm">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach(['draft','submitted','approved','rejected'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-select form-select-sm">
                    <option value="">All Months</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ date('F',mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <select name="year" class="form-select form-select-sm">
                    @foreach(range(date('Y'),date('Y')-2) as $y)
                        <option value="{{ $y }}" {{ request('year',$y==date('Y')?$y:0) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1"><button class="btn btn-primary btn-sm w-100">Filter</button></div>
            <div class="col-md-1"><a href="{{ route('admin.work-reports.index') }}" class="btn btn-secondary btn-sm w-100">Reset</a></div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
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
                    <tr>
                        <td>
                            @if($report->status === 'submitted')
                            <input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $report->id }}" form="bulkForm">
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $report->employee?->full_name }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ $report->employee?->designation }}</div>
                        </td>
                        <td>{{ $report->report_date?->format('d M Y') }}</td>
                        <td><span class="badge bg-primary bg-opacity-15 text-primary">{{ $report->hours_worked }}h</span></td>
                        <td>
                            @php $sc = $report->productivity_score ?? 0; $c = $sc >= 80 ? 'success' : ($sc >= 50 ? 'warning' : 'danger'); @endphp
                            <span class="badge bg-{{ $c }}">{{ $sc }}%</span>
                        </td>
                        <td style="max-width:200px" class="text-truncate" title="{{ $report->summary }}">{{ $report->summary }}</td>
                        <td>{{ $report->submitted_at?->diffForHumans() ?? '—' }}</td>
                        <td>
                            @php $colors = ['draft'=>'secondary','submitted'=>'warning','approved'=>'success','rejected'=>'danger']; @endphp
                            <span class="badge bg-{{ $colors[$report->status] ?? 'secondary' }}">{{ ucfirst($report->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.work-reports.show', $report) }}" class="btn btn-xs btn-outline-primary py-0 px-2" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($report->status === 'submitted')
                            <form method="POST" action="{{ route('admin.work-reports.approve', $report) }}" class="d-inline ms-1">
                                @csrf
                                <button class="btn btn-xs btn-success py-0 px-2" title="Approve"><i class="bi bi-check"></i></button>
                            </form>
                            <button class="btn btn-xs btn-danger py-0 px-2 ms-1" title="Reject"
                                onclick="openRejectModal({{ $report->id }})">
                                <i class="bi bi-x"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">No work reports found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
        <div class="px-3 py-2">{{ $reports->withQueryString()->links() }}</div>
        @endif
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form method="POST" id="rejectForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title">Reject Work Report</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small">Reason for Rejection <span class="text-danger">*</span></label>
                <textarea name="reviewer_notes" class="form-control form-control-sm" rows="3" required placeholder="Explain why..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
    btn.innerHTML = n ? `<i class="bi bi-check-all me-1"></i>Approve Selected (${n})` : '<i class="bi bi-check-all me-1"></i>Approve Selected';
}
</script>
@endpush
