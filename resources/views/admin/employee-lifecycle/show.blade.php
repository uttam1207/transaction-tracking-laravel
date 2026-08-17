@extends('layouts.app')

@section('title', ($employee->user->name ?? 'Employee') . ' — Lifecycle')

@section('content')
<div class="page-hero d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="page-hero-title">{{ $employee->user->name ?? 'Employee' }}</h1>
        <p class="page-hero-sub mb-0">
            {{ $employee->employee_id ?? '' }}
            @if($employee->department) &nbsp;·&nbsp; {{ $employee->department->name }} @endif
            @if($employee->designation) &nbsp;·&nbsp; {{ $employee->designation->name }} @endif
        </p>
    </div>
    <a href="{{ route('admin.employee-lifecycle.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<div class="row g-4">
    {{-- Timeline --}}
    <div class="col-lg-8">
        <div class="table-card">
            <div class="p-3 border-bottom fw-semibold">Lifecycle Timeline</div>
            <ul class="list-group list-group-flush">
                @forelse($events as $event)
                <li class="list-group-item d-flex gap-3 py-3">
                    <div class="text-center" style="min-width:60px">
                        <div class="fw-semibold text-primary">{{ $event->event_date->format('d M') }}</div>
                        <small class="text-muted">{{ $event->event_date->format('Y') }}</small>
                    </div>
                    <div class="flex-grow-1">
                        @php
                            $clsMap = [
                                'hired'       => 'success', 'onboarding_started' => 'info',
                                'probation_started' => 'info', 'probation_ended'  => 'success',
                                'confirmed'   => 'success', 'promoted'            => 'primary',
                                'transferred' => 'primary', 'resigned'            => 'warning',
                                'terminated'  => 'danger',  'exit_interview'      => 'warning',
                                'offboarding' => 'secondary','separated'          => 'secondary',
                                'reinstated'  => 'info',    'suspended'           => 'danger',
                                'warning_issued' => 'warning','contract_renewed'  => 'success',
                            ];
                            $cls = $clsMap[$event->event_type] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $cls }} mb-1">{{ $event->label }}</span>
                        @if($event->description)
                        <p class="mb-1 text-body-secondary">{{ $event->description }}</p>
                        @endif
                        @if($event->metadata)
                        <div class="small text-muted">
                            @foreach($event->metadata as $k => $v)
                                @if($v)<span class="me-2"><strong>{{ str_replace('_id','',str_replace('_',' ',$k)) }}:</strong> {{ $v }}</span>@endif
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="text-end text-muted small" style="min-width:80px">
                        {{ $event->triggeredBy->name ?? 'System' }}
                    </div>
                </li>
                @empty
                <li class="list-group-item text-center text-muted py-4">No lifecycle events recorded.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Summary sidebar --}}
    <div class="col-lg-4">
        {{-- Current Status --}}
        <div class="table-card mb-4">
            <div class="p-3 border-bottom fw-semibold">Current Status</div>
            <div class="p-3">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">Joined</dt>
                    <dd class="col-6">{{ $employee->join_date ? \Carbon\Carbon::parse($employee->join_date)->format('d M Y') : '—' }}</dd>
                    <dt class="col-6 text-muted">Department</dt>
                    <dd class="col-6">{{ $employee->department->name ?? '—' }}</dd>
                    <dt class="col-6 text-muted">Branch</dt>
                    <dd class="col-6">{{ $employee->branch->name ?? '—' }}</dd>
                    <dt class="col-6 text-muted">Designation</dt>
                    <dd class="col-6">{{ $employee->designation->name ?? '—' }}</dd>
                    <dt class="col-6 text-muted">Status</dt>
                    <dd class="col-6">
                        <span class="badge bg-{{ $employee->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($employee->status ?? 'active') }}
                        </span>
                    </dd>
                </dl>
            </div>
        </div>

        {{-- Department History --}}
        @if($departmentHistory->isNotEmpty())
        <div class="table-card mb-4">
            <div class="p-3 border-bottom fw-semibold">Department History</div>
            <ul class="list-group list-group-flush">
                @foreach($departmentHistory as $dh)
                <li class="list-group-item small">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">{{ $dh->department->name ?? '—' }}</span>
                        @if($dh->is_primary)<span class="badge bg-primary ms-1">Primary</span>@endif
                    </div>
                    <div class="text-muted">
                        {{ $dh->started_at ? \Carbon\Carbon::parse($dh->started_at)->format('d M Y') : '' }}
                        @if($dh->ended_at) – {{ \Carbon\Carbon::parse($dh->ended_at)->format('d M Y') }}
                        @else – Present @endif
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Training --}}
        @if($trainings->isNotEmpty())
        <div class="table-card">
            <div class="p-3 border-bottom fw-semibold">Training</div>
            <ul class="list-group list-group-flush">
                @foreach($trainings as $t)
                <li class="list-group-item small d-flex justify-content-between align-items-center">
                    <span>{{ $t->trainingProgram->title ?? '—' }}</span>
                    <span class="badge bg-{{ $t->status === 'completed' ? 'success' : 'secondary' }}">{{ ucfirst($t->status) }}</span>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection
