@extends('layouts.app')

@section('title', 'Work Reports')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Work Reports</h4>
        <p class="text-muted mb-0">Submit and track your daily work reports</p>
    </div>
    <a href="{{ route('employee.work-reports.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>New Report
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Summary</th>
                        <th>Hours Worked</th>
                        <th>Productivity</th>
                        <th>Status</th>
                        <th>Reviewer Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $report)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($report->report_date)->format('d M Y') }}</td>
                        <td>{{ Str::limit($report->summary, 50) }}</td>
                        <td>{{ number_format($report->hours_worked, 1) }}h</td>
                        <td>
                            @php $score = $report->productivity_score ?? 0; @endphp
                            <div class="d-flex align-items-center gap-1">
                                <div class="progress" style="height: 6px; width: 60px;">
                                    <div class="progress-bar bg-{{ $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger') }}"
                                        style="width: {{ $score }}%"></div>
                                </div>
                                <small>{{ $score }}%</small>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusColors = ['draft' => 'secondary', 'submitted' => 'info', 'approved' => 'success', 'rejected' => 'danger'];
                                $st = $report->status ?? 'draft';
                            @endphp
                            <span class="badge bg-{{ $statusColors[$st] ?? 'secondary' }}">
                                {{ ucfirst($st) }}
                            </span>
                        </td>
                        <td>{{ $report->reviewer_notes ? Str::limit($report->reviewer_notes, 30) : '—' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('employee.work-reports.show', $report) }}"
                                    class="btn btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($report->status === 'draft')
                                <form action="{{ route('employee.work-reports.submit', $report) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Submit">
                                        <i class="bi bi-send"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-text fs-1 d-block mb-2"></i>
                            No work reports yet. Create your first report!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
    <div class="card-footer bg-transparent">{{ $reports->links() }}</div>
    @endif
</div>
@endsection
