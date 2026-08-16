@extends('layouts.app')
@section('title', 'Performance Review — ' . $performanceReview->period)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.performance-reviews.index') }}">Performance Reviews</a></li>
    <li class="breadcrumb-item active">{{ $performanceReview->period }}</li>
@endsection

@section('content')

@php
    $review = $performanceReview;
    $statusColors = [
        'draft'        => ['bg'=>'#f3f4f6','color'=>'#6b7280','label'=>'Draft'],
        'submitted'    => ['bg'=>'#dbeafe','color'=>'#1d4ed8','label'=>'Submitted'],
        'acknowledged' => ['bg'=>'#dcfce7','color'=>'#16a34a','label'=>'Acknowledged'],
        'closed'       => ['bg'=>'#ede9fe','color'=>'#7c3aed','label'=>'Closed'],
    ];
    $sc = $statusColors[$review->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151','label'=>ucfirst($review->status)];
@endphp

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>{{ $review->employee->user->name ?? '—' }}</h4>
            <p>Performance Review — {{ $review->period }}</p>
        </div>
        <div class="d-flex gap-2">
            <span style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};padding:6px 16px;border-radius:20px;font-size:.8rem;font-weight:700;">
                {{ $sc['label'] }}
            </span>
            @if($review->status === 'draft')
            <button class="btn btn-sm btn-primary-grad px-4" onclick="submitReview()">
                <i class="bi bi-send me-1"></i>Submit
            </button>
            @elseif($review->status === 'submitted')
            <button class="btn btn-sm btn-success px-4" onclick="acknowledgeReview()">
                <i class="bi bi-check-circle me-1"></i>Acknowledge
            </button>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">

    {{-- Employee & Review Info --}}
    <div class="col-md-4">
        <div class="table-card h-100">
            <div class="card-header"><span class="card-title">Review Details</span></div>
            <div style="padding:20px;">
                <table class="table mb-0" style="font-size:.84rem;">
                    <tr>
                        <td style="color:#6b7280;font-weight:600;width:45%;">Employee</td>
                        <td style="font-weight:700;">{{ $review->employee->user->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Employee ID</td>
                        <td>{{ $review->employee->employee_id ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Department</td>
                        <td>{{ $review->employee->department->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Reviewer</td>
                        <td>{{ $review->reviewer->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Period</td>
                        <td style="font-weight:700;">{{ $review->period }}</td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Period Start</td>
                        <td>{{ \Carbon\Carbon::parse($review->period_start)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Period End</td>
                        <td>{{ \Carbon\Carbon::parse($review->period_end)->format('d M Y') }}</td>
                    </tr>
                    @if($review->submitted_at)
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Submitted</td>
                        <td>{{ \Carbon\Carbon::parse($review->submitted_at)->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($review->acknowledged_at)
                    <tr>
                        <td style="color:#6b7280;font-weight:600;">Acknowledged</td>
                        <td>{{ \Carbon\Carbon::parse($review->acknowledged_at)->format('d M Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Rating & Feedback --}}
    <div class="col-md-8">
        <div class="table-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Ratings & Feedback</span>
                <button class="btn btn-sm btn-outline-primary px-3" data-bs-toggle="modal" data-bs-target="#editModal">
                    <i class="bi bi-pencil me-1"></i>Edit
                </button>
            </div>
            <div style="padding:20px;">
                {{-- Overall Rating --}}
                <div class="mb-4 text-center">
                    @if($review->overall_rating)
                    @php $rc = $review->overall_rating >= 4 ? '#16a34a' : ($review->overall_rating >= 3 ? '#d97706' : '#dc2626'); @endphp
                    <div style="font-size:3rem;font-weight:900;color:{{ $rc }};line-height:1;">{{ number_format($review->overall_rating, 1) }}</div>
                    <div style="font-size:.85rem;color:#9ca3af;">Overall Rating / 5.0</div>
                    @else
                    <div style="font-size:1.5rem;font-weight:700;color:#d1d5db;">Not Rated Yet</div>
                    @endif
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div style="background:#f0fdf4;border-radius:10px;padding:14px;">
                            <div style="font-size:.75rem;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Strengths</div>
                            <div style="font-size:.84rem;color:#374151;">{{ $review->strengths ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:#fef3f2;border-radius:10px;padding:14px;">
                            <div style="font-size:.75rem;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Areas for Improvement</div>
                            <div style="font-size:.84rem;color:#374151;">{{ $review->areas_for_improvement ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:#f0f4ff;border-radius:10px;padding:14px;">
                            <div style="font-size:.75rem;font-weight:700;color:#4f46e5;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Goals for Next Period</div>
                            <div style="font-size:.84rem;color:#374151;">{{ $review->goals_for_next_period ?: '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div style="background:#fdfce4;border-radius:10px;padding:14px;">
                            <div style="font-size:.75rem;font-weight:700;color:#ca8a04;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Employee Comments</div>
                            <div style="font-size:.84rem;color:#374151;">{{ $review->employee_comments ?: '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Goals Table --}}
    <div class="col-12">
        <div class="table-card">
            <div class="card-header"><span class="card-title">Goals & Objectives</span></div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:35%;">Goal</th>
                            <th>Description</th>
                            <th>Target</th>
                            <th>Achieved</th>
                            <th>Rating</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($review->goals as $goal)
                        @php
                            $goalStatusColors = [
                                'pending'     => ['bg'=>'#f3f4f6','color'=>'#6b7280'],
                                'in_progress' => ['bg'=>'#dbeafe','color'=>'#1d4ed8'],
                                'completed'   => ['bg'=>'#dcfce7','color'=>'#16a34a'],
                                'missed'      => ['bg'=>'#fee2e2','color'=>'#dc2626'],
                            ];
                            $gc = $goalStatusColors[$goal->status] ?? ['bg'=>'#f3f4f6','color'=>'#374151'];
                        @endphp
                        <tr>
                            <td style="font-weight:700;font-size:.86rem;color:#111827;">{{ $goal->title }}</td>
                            <td style="font-size:.82rem;color:#6b7280;">{{ $goal->description ?: '—' }}</td>
                            <td style="font-size:.83rem;">{{ $goal->target_value ?: '—' }}</td>
                            <td style="font-size:.83rem;">{{ $goal->achieved_value ?: '—' }}</td>
                            <td>
                                @if($goal->rating)
                                <span style="font-weight:700;color:#374151;">{{ $goal->rating }}/5</span>
                                @else<span style="color:#9ca3af;">—</span>@endif
                            </td>
                            <td>
                                <span style="background:{{ $gc['bg'] }};color:{{ $gc['color'] }};padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:700;">
                                    {{ ucwords(str_replace('_', ' ', $goal->status)) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6">
                            <div class="empty-state"><i class="bi bi-list-check"></i><p>No goals defined for this review</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Update Review</h6>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST" action="{{ route('admin.performance-reviews.update', $review) }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="flabel">Overall Rating (1–5)</label>
                            <input type="number" name="overall_rating" class="form-control" min="1" max="5" step="0.1"
                                value="{{ $review->overall_rating }}" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-md-4">
                            <label class="flabel">Status</label>
                            <select name="status" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="draft"        {{ $review->status === 'draft'        ? 'selected' : '' }}>Draft</option>
                                <option value="submitted"    {{ $review->status === 'submitted'    ? 'selected' : '' }}>Submitted</option>
                                <option value="acknowledged" {{ $review->status === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                                <option value="closed"       {{ $review->status === 'closed'       ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Strengths</label>
                            <textarea name="strengths" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;">{{ $review->strengths }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Areas for Improvement</label>
                            <textarea name="areas_for_improvement" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;">{{ $review->areas_for_improvement }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Goals for Next Period</label>
                            <textarea name="goals_for_next_period" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;">{{ $review->goals_for_next_period }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="flabel">Employee Comments</label>
                            <textarea name="employee_comments" class="form-control" rows="3" style="border-radius:9px;border:1.5px solid #e5e7eb;resize:none;">{{ $review->employee_comments }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function submitReview() {
    APP.confirm('Submit review?', 'Once submitted, the employee will be notified for acknowledgement.', function() {
        fetch('{{ route("admin.performance-reviews.submit", $review) }}', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Review submitted.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Error.', 'error');
        });
    });
}

function acknowledgeReview() {
    APP.confirm('Acknowledge review?', 'This confirms the employee has reviewed and acknowledged the appraisal.', function() {
        fetch('{{ route("admin.performance-reviews.acknowledge", $review) }}', {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            if (data.success) { APP.toast('Review acknowledged.'); setTimeout(() => location.reload(), 1000); }
            else APP.toast(data.message || 'Error.', 'error');
        });
    });
}

// Edit form submits via fetch (JSON)
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fetch(this.action, {
        method: 'POST',
        body: fd,
        headers: { 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) { APP.toast('Review updated.'); setTimeout(() => location.reload(), 1000); }
        else APP.toast(data.message || 'Error saving.', 'error');
    });
});
</script>
@endpush
