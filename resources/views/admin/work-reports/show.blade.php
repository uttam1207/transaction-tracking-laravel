@extends('layouts.app')
@section('title', 'Work Report Detail')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.work-reports.index') }}">Work Reports</a></li>
    <li class="breadcrumb-item active">Detail</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">Work Report — {{ $workReport->report_date?->format('d M Y') }}</div>
                    <div class="text-muted small">{{ $workReport->employee?->full_name }} · {{ $workReport->employee?->designation }}</div>
                </div>
                @php $colors = ['draft'=>'secondary','submitted'=>'warning','approved'=>'success','rejected'=>'danger']; @endphp
                <span class="badge bg-{{ $colors[$workReport->status] ?? 'secondary' }} fs-6">{{ ucfirst($workReport->status) }}</span>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Summary</h6>
                <p>{{ $workReport->summary }}</p>

                @if(!empty($workReport->tasks_completed))
                <h6 class="fw-semibold mb-2 mt-3">Tasks Completed</h6>
                <ul class="list-group list-group-flush">
                    @foreach((array)$workReport->tasks_completed as $task)
                    <li class="list-group-item py-1 px-0 small">
                        <i class="bi bi-check-circle text-success me-2"></i>{{ $task }}
                    </li>
                    @endforeach
                </ul>
                @endif

                @if($workReport->reviewer_notes)
                <div class="alert alert-{{ $workReport->status === 'rejected' ? 'danger' : 'success' }} mt-3">
                    <strong>Reviewer Notes:</strong> {{ $workReport->reviewer_notes }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Report Info</h6>
                <table class="table table-sm small mb-0">
                    <tr><th class="text-muted fw-normal">Hours Worked</th><td class="fw-semibold">{{ $workReport->hours_worked }}h</td></tr>
                    <tr><th class="text-muted fw-normal">Productivity</th>
                        <td>
                            @php $sc = $workReport->productivity_score ?? 0; $c = $sc>=80?'success':($sc>=50?'warning':'danger'); @endphp
                            <span class="badge bg-{{ $c }}">{{ $sc }}%</span>
                        </td>
                    </tr>
                    <tr><th class="text-muted fw-normal">Submitted</th><td>{{ $workReport->submitted_at?->format('d M Y H:i') ?? '—' }}</td></tr>
                    <tr><th class="text-muted fw-normal">Created</th><td>{{ $workReport->created_at->format('d M Y') }}</td></tr>
                </table>
            </div>
        </div>

        @if($workReport->status === 'submitted')
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Review</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.work-reports.approve', $workReport) }}" class="mb-2">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Reviewer Notes (optional)</label>
                        <textarea name="reviewer_notes" class="form-control form-control-sm" rows="2" placeholder="Well done..."></textarea>
                    </div>
                    <button class="btn btn-success btn-sm w-100">
                        <i class="bi bi-check-circle me-1"></i>Approve Report
                    </button>
                </form>

                <hr class="my-2">

                <form method="POST" action="{{ route('admin.work-reports.reject', $workReport) }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="reviewer_notes" class="form-control form-control-sm" rows="2" required placeholder="Reason..."></textarea>
                    </div>
                    <button class="btn btn-danger btn-sm w-100">
                        <i class="bi bi-x-circle me-1"></i>Reject Report
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
