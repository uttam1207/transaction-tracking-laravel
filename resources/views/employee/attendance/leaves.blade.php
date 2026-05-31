@extends('layouts.app')
@section('title', 'Leave Requests')

@section('content')

<a href="{{ route('employee.attendance.index') }}" class="back-btn"><i class="bi bi-arrow-left"></i>Back to Attendance</a>

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Leave Requests</h4>
            <p>Manage your leave applications</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#requestLeaveModal">
            <i class="bi bi-calendar-plus me-1"></i>Request Leave
        </button>
    </div>
</div>

{{-- Leave Balance Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="info-card text-center" style="padding:20px;border-top:4px solid #16a34a;">
            <div style="font-size:2.2rem;font-weight:800;color:#16a34a;line-height:1;">{{ $employee->annual_leave_balance ?? 21 }}</div>
            <div style="font-size:.84rem;color:#374151;margin-top:6px;font-weight:600;">Annual Leave Balance</div>
            <div style="font-size:.74rem;color:#9ca3af;">days remaining</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="padding:20px;border-top:4px solid #0ea5e9;">
            <div style="font-size:2.2rem;font-weight:800;color:#0ea5e9;line-height:1;">{{ $employee->sick_leave_balance ?? 10 }}</div>
            <div style="font-size:.84rem;color:#374151;margin-top:6px;font-weight:600;">Sick Leave Balance</div>
            <div style="font-size:.74rem;color:#9ca3af;">days remaining</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-card text-center" style="padding:20px;border-top:4px solid #f59e0b;">
            <div style="font-size:2.2rem;font-weight:800;color:#f59e0b;line-height:1;">{{ $leaves->where('status','pending')->count() }}</div>
            <div style="font-size:.84rem;color:#374151;margin-top:6px;font-weight:600;">Pending Requests</div>
            <div style="font-size:.74rem;color:#9ca3af;">awaiting approval</div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="card-header"><span class="card-title">My Leave History</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
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
                @php $st = $leave->status ?? 'pending'; @endphp
                <tr>
                    <td>
                        <span style="background:#f0f4ff;color:#4f46e5;padding:3px 9px;border-radius:6px;font-size:.72rem;font-weight:600;">
                            {{ ucwords(str_replace('_', ' ', $leave->type)) }}
                        </span>
                    </td>
                    <td style="font-size:.83rem;">{{ \Carbon\Carbon::parse($leave->from_date)->format('M d, Y') }}</td>
                    <td style="font-size:.83rem;">{{ \Carbon\Carbon::parse($leave->to_date)->format('M d, Y') }}</td>
                    <td>
                        <span style="font-weight:700;font-size:.85rem;">{{ $leave->days }}</span>
                        @if($leave->is_half_day)
                            <span style="background:#dbeafe;color:#2563eb;padding:1px 6px;border-radius:4px;font-size:.65rem;font-weight:700;margin-left:3px;">Half</span>
                        @endif
                    </td>
                    <td style="font-size:.82rem;color:#6b7280;max-width:160px;">
                        <span title="{{ $leave->reason }}">{{ Str::limit($leave->reason, 40) }}</span>
                    </td>
                    <td>
                        <span class="spill spill-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($st) }}</span>
                        @if($leave->rejection_reason && $st === 'rejected')
                        <div style="font-size:.72rem;color:#dc2626;margin-top:3px;">{{ Str::limit($leave->rejection_reason, 40) }}</div>
                        @endif
                    </td>
                    <td style="font-size:.78rem;color:#9ca3af;">{{ $leave->created_at->format('M d') }}</td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-calendar-minus"></i><p>No leave requests yet</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($leaves->hasPages())
    <div class="pagination-wrap">{{ $leaves->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Request Leave Modal --}}
<div class="modal fade" id="requestLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2"></i>Request Leave</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('employee.attendance.leaves.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Leave Type <span class="req">*</span></label>
                            <select name="type" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="annual">Annual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="casual">Casual Leave</option>
                                <option value="maternity">Maternity Leave</option>
                                <option value="paternity">Paternity Leave</option>
                                <option value="unpaid">Unpaid Leave</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="flabel">From Date <span class="req">*</span></label>
                            <input type="date" name="from_date" class="form-control" required
                                min="{{ now()->addDay()->format('Y-m-d') }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-6">
                            <label class="flabel">To Date <span class="req">*</span></label>
                            <input type="date" name="to_date" class="form-control" required
                                min="{{ now()->addDay()->format('Y-m-d') }}"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <div class="form-check" style="padding-left:1.6rem;">
                                <input class="form-check-input" type="checkbox" name="is_half_day" value="1" id="halfDayCheck">
                                <label class="form-check-label" for="halfDayCheck" style="font-size:.84rem;color:#374151;">Half Day</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="flabel">Reason <span class="req">*</span></label>
                            <textarea name="reason" rows="3" class="form-control" required
                                placeholder="Please provide a reason for your leave request…"
                                style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">
                        <i class="bi bi-send me-1"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
