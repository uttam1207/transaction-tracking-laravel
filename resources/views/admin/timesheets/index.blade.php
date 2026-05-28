@extends('layouts.app')
@section('title', 'Timesheet Management')
@section('breadcrumb')
    <li class="breadcrumb-item active">Timesheets</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold">Timesheet Management</h4>
        <p class="text-muted small mb-0">Review and approve employee time entries</p>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.timesheets.bulk-approve') }}" id="bulkForm">
            @csrf
            <button type="submit" class="btn btn-success btn-sm" id="bulkApproveBtn" disabled>
                <i class="bi bi-check-all me-1"></i>Approve Selected
            </button>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-15 text-primary"><i class="bi bi-clock"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ number_format($totalHours, 1) }}</div>
                    <div class="text-muted small">Total Hours (filtered)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-15 text-warning"><i class="bi bi-hourglass-split"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ $timesheets->where('status','pending')->count() }}</div>
                    <div class="text-muted small">Pending Approval</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-15 text-success"><i class="bi bi-check-circle"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ $timesheets->where('status','approved')->count() }}</div>
                    <div class="text-muted small">Approved</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-15 text-info"><i class="bi bi-people"></i></div>
                <div>
                    <div class="h4 mb-0 fw-bold">{{ $timesheets->pluck('employee_id')->unique()->count() }}</div>
                    <div class="text-muted small">Employees</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Employee</label>
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
                <label class="form-label small">Project</label>
                <select name="project_id" class="form-select form-select-sm">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Month</label>
                <select name="month" class="form-select form-select-sm">
                    <option value="">All Months</option>
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0,0,0,$m,1)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small">Year</label>
                <select name="year" class="form-select form-select-sm">
                    @foreach(range(date('Y'), date('Y')-3) as $y)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.timesheets.index') }}" class="btn btn-secondary btn-sm w-100">Reset</a>
            </div>
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
                        <th>Date</th>
                        <th>Project</th>
                        <th>Task</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Hours</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timesheets as $ts)
                    <tr>
                        <td>
                            @if($ts->status === 'pending')
                            <input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $ts->id }}" form="bulkForm">
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $ts->employee?->full_name }}</div>
                            <div class="text-muted" style="font-size:.7rem">{{ $ts->employee?->employee_id }}</div>
                        </td>
                        <td>{{ $ts->date?->format('d M Y') }}</td>
                        <td>{{ $ts->project?->name ?? '—' }}</td>
                        <td>{{ $ts->task ? Str::limit($ts->task->title, 25) : '—' }}</td>
                        <td>{{ $ts->start_time ? $ts->start_time->format('H:i') : '—' }}</td>
                        <td>{{ $ts->end_time ? $ts->end_time->format('H:i') : '—' }}</td>
                        <td><span class="badge bg-primary bg-opacity-15 text-primary">{{ number_format($ts->hours, 1) }}h</span></td>
                        <td style="max-width:160px" class="text-truncate" title="{{ $ts->description }}">{{ $ts->description }}</td>
                        <td>
                            @php $colors = ['pending'=>'warning','approved'=>'success','rejected'=>'danger']; @endphp
                            <span class="badge bg-{{ $colors[$ts->status] ?? 'secondary' }}">{{ ucfirst($ts->status) }}</span>
                        </td>
                        <td>
                            @if($ts->status === 'pending')
                            <form method="POST" action="{{ route('admin.timesheets.approve', $ts) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-xs btn-success py-0 px-2" title="Approve">
                                    <i class="bi bi-check"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.timesheets.reject', $ts) }}" class="d-inline ms-1">
                                @csrf
                                <button class="btn btn-xs btn-danger py-0 px-2" title="Reject">
                                    <i class="bi bi-x"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-4 text-muted">No timesheet records found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($timesheets->hasPages())
        <div class="px-3 py-2">{{ $timesheets->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    updateBulkBtn();
});
document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBulkBtn));
function updateBulkBtn() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    const btn = document.getElementById('bulkApproveBtn');
    btn.disabled = checked === 0;
    btn.textContent = checked > 0 ? `Approve Selected (${checked})` : 'Approve Selected';
}
</script>
@endpush
