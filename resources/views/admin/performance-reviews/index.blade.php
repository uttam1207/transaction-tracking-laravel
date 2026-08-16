@extends('layouts.app')
@section('title', 'Performance Reviews')
@section('breadcrumb')
    <li class="breadcrumb-item active">Performance Reviews</li>
@endsection

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Performance Reviews</h4>
            <p>Manage employee appraisals, goals, and review lifecycle</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#prModal">
            <i class="bi bi-plus-lg me-1"></i>New Review
        </button>
    </div>
</div>

{{-- Filters --}}
<div class="filter-card">
    <form method="GET" class="d-flex gap-2 align-items-end flex-wrap">
        <div>
            <label class="flabel">Search Employee</label>
            <input type="text" name="search" class="form-control" placeholder="Employee name…"
                value="{{ request('search') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:200px;">
        </div>
        <div>
            <label class="flabel">Status</label>
            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:150px;">
                <option value="">All Statuses</option>
                <option value="draft"        {{ request('status') === 'draft'        ? 'selected' : '' }}>Draft</option>
                <option value="submitted"    {{ request('status') === 'submitted'    ? 'selected' : '' }}>Submitted</option>
                <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                <option value="closed"       {{ request('status') === 'closed'       ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
        <div>
            <label class="flabel">Period</label>
            <input type="text" name="period" class="form-control" placeholder="Q3 2026"
                value="{{ request('period') }}" style="border-radius:9px;border:1.5px solid #e5e7eb;font-size:.84rem;width:130px;">
        </div>
        <button type="submit" class="btn btn-sm btn-primary-grad px-4">Filter</button>
        @if(request()->hasAny(['search','status','period','employee_id']))
            <a href="{{ route('admin.performance-reviews.index') }}" class="btn btn-sm btn-outline-secondary px-3">Clear</a>
        @endif
    </form>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">All Reviews</span>
        <span style="font-size:.82rem;color:#6b7280;">{{ $reviews->total() }} reviews</span>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Period</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                @php
                    $statusColors = [
                        'draft'        => ['bg'=>'#f3f4f6','color'=>'#6b7280'],
                        'submitted'    => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
                        'acknowledged' => ['bg'=>'#dcfce7','color'=>'#16a34a'],
                        'closed'       => ['bg'=>'#ede9fe','color'=>'#7c3aed'],
                    ];
                    $sc = $statusColors[$review->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                @endphp
                <tr>
                    <td>
                        <div>
                            <div style="font-weight:700;font-size:.86rem;color:#111827;">{{ $review->employee->user->name ?? '—' }}</div>
                            <div style="font-size:.75rem;color:#6b7280;">{{ $review->employee->employee_id ?? '' }}</div>
                        </div>
                    </td>
                    <td>
                        <div style="font-weight:600;color:#374151;font-size:.85rem;">{{ $review->period }}</div>
                        <div style="font-size:.75rem;color:#9ca3af;">
                            {{ \Carbon\Carbon::parse($review->period_start)->format('d M') }} –
                            {{ \Carbon\Carbon::parse($review->period_end)->format('d M Y') }}
                        </div>
                    </td>
                    <td style="font-size:.83rem;color:#374151;">{{ $review->reviewer->name ?? '—' }}</td>
                    <td>
                        @if($review->overall_rating)
                        @php
                            $ratingColor = $review->overall_rating >= 4 ? '#16a34a' : ($review->overall_rating >= 3 ? '#d97706' : '#dc2626');
                        @endphp
                        <span style="font-size:1.1rem;font-weight:800;color:{{ $ratingColor }};">{{ number_format($review->overall_rating, 1) }}</span>
                        <span style="font-size:.75rem;color:#9ca3af;">/5</span>
                        @else
                        <span style="color:#9ca3af;font-size:.8rem;">Pending</span>
                        @endif
                    </td>
                    <td>
                        <span style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;text-transform:capitalize;">
                            {{ ucfirst($review->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.performance-reviews.show', $review) }}" class="act-btn" title="View" style="color:#4f46e5;">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($review->status === 'draft')
                            <button class="act-btn act-delete" title="Delete" onclick="deletePR({{ $review->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state"><i class="bi bi-star"></i><p>No performance reviews found</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reviews->hasPages())
    <div class="pagination-wrap">{{ $reviews->links('pagination::bootstrap-5') }}</div>
    @endif
</div>

{{-- Create Review Modal --}}
<div class="modal fade" id="prModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">New Performance Review</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.performance-reviews.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="flabel">Employee <span class="req">*</span></label>
                            <select name="employee_id" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— Select Employee —</option>
                                @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name ?? '' }} ({{ $emp->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Reviewer <span class="req">*</span></label>
                            <select name="reviewer_id" class="form-select" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="">— Select Reviewer —</option>
                                @foreach($reviewers as $rev)
                                <option value="{{ $rev->id }}">{{ $rev->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Period <span class="req">*</span></label>
                            <input type="text" name="period" class="form-control" placeholder="Q3 2026" required maxlength="20" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Period Start <span class="req">*</span></label>
                            <input type="date" name="period_start" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Period End <span class="req">*</span></label>
                            <input type="date" name="period_end" class="form-control" required style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status</label>
                            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="draft">Draft</option>
                                <option value="submitted">Submitted</option>
                            </select>
                        </div>

                        {{-- Goals --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="flabel mb-0">Goals</label>
                                <button type="button" class="btn btn-sm btn-outline-primary px-3" onclick="addGoalRow()">
                                    <i class="bi bi-plus-lg me-1"></i>Add Goal
                                </button>
                            </div>
                            <div id="goalRows"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Create Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;
let goalIdx = 0;

function addGoalRow() {
    const html = `
    <div class="row g-2 mb-2 align-items-start" id="goal_${goalIdx}">
        <div class="col-md-5">
            <input type="text" name="goals[${goalIdx}][title]" class="form-control form-control-sm" placeholder="Goal title" required style="border-radius:7px;">
        </div>
        <div class="col-md-4">
            <input type="text" name="goals[${goalIdx}][description]" class="form-control form-control-sm" placeholder="Description (optional)" style="border-radius:7px;">
        </div>
        <div class="col-md-2">
            <select name="goals[${goalIdx}][status]" class="form-select form-select-sm" style="border-radius:7px;">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="missed">Missed</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger px-2" onclick="removeGoal(${goalIdx})">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>`;
    document.getElementById('goalRows').insertAdjacentHTML('beforeend', html);
    goalIdx++;
}

function removeGoal(idx) {
    const el = document.getElementById(`goal_${idx}`);
    if (el) el.remove();
}

function deletePR(id) {
    APP.confirm('Delete review?', 'Only draft reviews can be deleted.', function() {
        fetch(`/admin/performance-reviews/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Review deleted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Cannot delete.', 'error');
        });
    });
}
</script>
@endpush
