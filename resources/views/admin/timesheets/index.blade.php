@extends('layouts.app')
@section('title', 'Timesheet Management')
@section('breadcrumb')
    <li class="breadcrumb-item active">Timesheets</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Timesheet Management</h4>
            <p>Review and approve employee time entries</p>
        </div>
        <form method="POST" action="{{ route('admin.timesheets.bulk-approve') }}" id="bulkForm">
            @csrf
            <button type="submit" class="btn btn-sm" id="bulkApproveBtn" disabled
                style="background:rgba(255,255,255,.2);color:#fff;border:1.5px solid rgba(255,255,255,.4);border-radius:9px;font-weight:600;backdrop-filter:blur(4px);">
                <i class="bi bi-check-all me-1"></i>Approve Selected
            </button>
        </form>
    </div>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    <div class="col-sm-3">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #6366f1;">
            <div style="font-size:1.7rem;font-weight:800;color:#6366f1;line-height:1;">{{ number_format($totalHours, 1) }}</div>
            <div style="font-size:.8rem;color:#6b7280;margin-top:4px;">Total Hours (filtered)</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #f59e0b;">
            <div style="font-size:1.7rem;font-weight:800;color:#f59e0b;line-height:1;">{{ $timesheets->where('status','pending')->count() }}</div>
            <div style="font-size:.8rem;color:#6b7280;margin-top:4px;">Pending Approval</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #16a34a;">
            <div style="font-size:1.7rem;font-weight:800;color:#16a34a;line-height:1;">{{ $timesheets->where('status','approved')->count() }}</div>
            <div style="font-size:.8rem;color:#6b7280;margin-top:4px;">Approved</div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="info-card text-center" style="padding:16px;border-top:4px solid #0ea5e9;">
            <div style="font-size:1.7rem;font-weight:800;color:#0ea5e9;line-height:1;">{{ $timesheets->pluck('employee_id')->unique()->count() }}</div>
            <div style="font-size:.8rem;color:#6b7280;margin-top:4px;">Employees</div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2">
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
            <label class="flabel">Project</label>
            <select name="project_id" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Status</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="flabel">Month</label>
            <select name="month" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                <option value="">All Months</option>
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0,0,0,$m,1)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label class="flabel">Year</label>
            <select name="year" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;">
                @foreach(range(date('Y'), date('Y')-3) as $y)
                    <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
            <a href="{{ route('admin.timesheets.index') }}" class="btn btn-sm btn-outline-secondary px-3" style="border-radius:9px;">Reset</a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="table-card">
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" id="selectAll" class="form-check-input" style="width:15px;height:15px;cursor:pointer;">
                    </th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Project</th>
                    <th>Task</th>
                    <th>Time</th>
                    <th>Hours</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($timesheets as $ts)
                @php $sMap = ['pending'=>'warning','approved'=>'success','rejected'=>'danger']; @endphp
                <tr>
                    <td>
                        @if($ts->status === 'pending')
                        <input type="checkbox" class="form-check-input row-check" name="ids[]" value="{{ $ts->id }}" form="bulkForm"
                            style="width:15px;height:15px;cursor:pointer;">
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700;font-size:.87rem;color:#111827;">{{ $ts->employee?->full_name }}</div>
                        <div style="font-size:.73rem;color:#9ca3af;">{{ $ts->employee?->employee_id }}</div>
                    </td>
                    <td style="font-size:.84rem;color:#374151;">{{ $ts->date?->format('d M Y') }}</td>
                    <td style="font-size:.83rem;color:#374151;">{{ $ts->project?->name ?? '—' }}</td>
                    <td style="font-size:.83rem;color:#374151;max-width:140px;" class="text-truncate">{{ $ts->task ? Str::limit($ts->task->title, 25) : '—' }}</td>
                    <td style="font-size:.82rem;color:#6b7280;white-space:nowrap;">
                        {{ $ts->start_time ? $ts->start_time->format('H:i') : '—' }}
                        @if($ts->end_time) &mdash; {{ $ts->end_time->format('H:i') }} @endif
                    </td>
                    <td>
                        <span class="spill spill-info" style="font-size:.72rem;">{{ number_format($ts->hours, 1) }}h</span>
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;max-width:160px;" class="text-truncate" title="{{ $ts->description }}">
                        {{ $ts->description ?? '—' }}
                    </td>
                    <td>
                        <span class="spill spill-{{ $sMap[$ts->status] ?? 'secondary' }}" style="font-size:.72rem;">
                            {{ ucfirst($ts->status) }}
                        </span>
                    </td>
                    <td>
                        @if($ts->status === 'pending')
                        <div class="d-flex gap-1">
                            <form method="POST" action="{{ route('admin.timesheets.approve', $ts) }}" class="d-inline">
                                @csrf
                                <button class="act-btn act-green" title="Approve"><i class="bi bi-check-lg"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.timesheets.reject', $ts) }}" class="d-inline">
                                @csrf
                                <button class="act-btn act-delete" title="Reject"><i class="bi bi-x-lg"></i></button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="10">
                    <div class="empty-state"><i class="bi bi-clock-history"></i><p>No timesheet records found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($timesheets->hasPages())
    <div class="pagination-wrap">{{ $timesheets->withQueryString()->links() }}</div>
    @endif
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
    btn.innerHTML = checked > 0
        ? `<i class="bi bi-check-all me-1"></i>Approve Selected (${checked})`
        : '<i class="bi bi-check-all me-1"></i>Approve Selected';
}
</script>
@endpush
