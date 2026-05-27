@extends('layouts.app')

@section('title', 'Leave Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('employee.attendance.index') }}" class="text-decoration-none text-muted">
            <i class="bi bi-arrow-left me-1"></i>Back to Attendance
        </a>
        <h4 class="mb-0 fw-bold mt-1">Leave Requests</h4>
    </div>
    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#requestLeaveModal">
        <i class="bi bi-calendar-plus me-1"></i>Request Leave
    </button>
</div>

{{-- Leave Balance --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-success">{{ $employee->annual_leave_balance ?? 21 }}</div>
            <div class="text-muted">Annual Leave Balance</div>
            <small class="text-muted">days remaining</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-info">{{ $employee->sick_leave_balance ?? 10 }}</div>
            <div class="text-muted">Sick Leave Balance</div>
            <small class="text-muted">days remaining</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="fs-2 fw-bold text-warning">
                {{ $leaves->where('status', 'pending')->count() }}
            </div>
            <div class="text-muted">Pending Requests</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Leave Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Applied</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ ucwords(str_replace('_', ' ', $leave->type)) }}
                            </span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($leave->from_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}</td>
                        <td>
                            {{ $leave->days }}
                            @if($leave->is_half_day)
                                <span class="badge bg-info">Half</span>
                            @endif
                        </td>
                        <td>{{ Str::limit($leave->reason, 40) }}</td>
                        <td>
                            @php
                                $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                $st = $leave->status ?? 'pending';
                            @endphp
                            <span class="badge bg-{{ $colors[$st] ?? 'secondary' }}">
                                {{ ucfirst($st) }}
                            </span>
                            @if($leave->rejection_reason && $st === 'rejected')
                            <br><small class="text-danger">{{ $leave->rejection_reason }}</small>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $leave->created_at->format('M d') }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-minus fs-1 d-block mb-2"></i>
                            No leave requests yet
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($leaves->hasPages())
    <div class="card-footer bg-transparent">{{ $leaves->links() }}</div>
    @endif
</div>

{{-- Request Leave Modal --}}
<div class="modal fade" id="requestLeaveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-plus me-2"></i>Request Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.attendance.leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <select name="type" class="form-select" required>
                            <option value="annual">Annual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="casual">Casual Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="paternity">Paternity Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" required
                                min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" required
                                min="{{ now()->addDay()->format('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_half_day" value="1">
                            <label class="form-check-label">Half Day</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" required
                            placeholder="Please provide a reason for your leave request..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send me-1"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
