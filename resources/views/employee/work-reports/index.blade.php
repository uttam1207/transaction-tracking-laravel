@extends('layouts.app')
@section('title', 'Work Reports')

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Work Reports</h4>
            <p>Submit and track your daily work reports</p>
        </div>
        <a href="{{ route('employee.work-reports.create') }}" class="btn btn-sm btn-primary-grad px-4">
            <i class="bi bi-plus-circle me-1"></i>New Report
        </a>
    </div>
</div>

<div class="table-card">
    <div class="card-header"><span class="card-title">My Reports</span></div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Summary</th>
                    <th>Hours</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Reviewer Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                @php
                    $st = $report->status ?? 'draft';
                    $score = $report->productivity_score ?? 0;
                    $scoreColor = $score >= 80 ? '#16a34a' : ($score >= 60 ? '#d97706' : '#dc2626');
                @endphp
                <tr>
                    <td style="font-weight:700;font-size:.87rem;color:#111827;">
                        {{ \Carbon\Carbon::parse($report->report_date)->format('d M Y') }}
                    </td>
                    <td style="font-size:.83rem;color:#6b7280;max-width:180px;">
                        <span title="{{ $report->summary }}">{{ Str::limit($report->summary, 50) }}</span>
                    </td>
                    <td>
                        <span style="background:#eff6ff;color:#2563eb;padding:3px 8px;border-radius:6px;font-size:.75rem;font-weight:700;">{{ number_format($report->hours_worked, 1) }}h</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;min-width:80px;">
                            <div style="flex:1;height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $score }}%;height:100%;background:{{ $scoreColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:.75rem;font-weight:700;color:{{ $scoreColor }};">{{ $score }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="spill spill-{{ $st === 'approved' ? 'success' : ($st === 'rejected' ? 'danger' : ($st === 'submitted' ? 'warning' : 'secondary')) }}">{{ ucfirst($st) }}</span>
                    </td>
                    <td style="font-size:.8rem;color:#6b7280;">{{ $report->reviewer_notes ? Str::limit($report->reviewer_notes, 30) : '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('employee.work-reports.show', $report) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            @if($report->status === 'draft')
                            <form action="{{ route('employee.work-reports.submit', $report) }}" method="POST">
                                @csrf
                                <button type="submit" class="act-btn act-green" title="Submit"><i class="bi bi-send"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7">
                    <div class="empty-state"><i class="bi bi-file-earmark-text"></i><p>No work reports yet. Create your first report!</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div class="pagination-wrap">{{ $reports->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
