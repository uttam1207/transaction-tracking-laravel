@extends('layouts.app')
@section('title', 'My Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- Welcome Hero --}}
<div style="background:linear-gradient(135deg,#4f46e5,#7c3aed);border-radius:16px;padding:28px 32px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:rgba(255,255,255,.06);border-radius:50%;"></div>
    <div style="position:absolute;bottom:-60px;right:60px;width:140px;height:140px;background:rgba(255,255,255,.04);border-radius:50%;"></div>
    <div class="d-flex align-items-center gap-3 flex-wrap" style="position:relative;z-index:1;">
        <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle" style="width:56px;height:56px;border:2.5px solid rgba(255,255,255,.5);object-fit:cover;" alt="">
        <div>
            <h4 style="margin:0;font-weight:800;font-size:1.3rem;">
                Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->name }}!
            </h4>
            <div style="opacity:.75;font-size:.85rem;margin-top:2px;">{{ $employee->designation }} &bull; {{ $employee->department?->name }}</div>
        </div>
        <div class="ms-auto text-end d-none d-md-block">
            <div style="opacity:.7;font-size:.8rem;">{{ now()->format('l, F j, Y') }}</div>
            <div style="font-size:1.4rem;font-weight:800;font-family:monospace;" id="clock">{{ now()->format('H:i:s') }}</div>
        </div>
    </div>
</div>

{{-- Check-In Card + Stats --}}
<div class="row g-3 mb-4">
    {{-- Check-In/Out --}}
    <div class="col-md-4">
        <div class="info-card h-100 text-center" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:28px 20px;">
            @if(!$data['is_checked_in'] && !$data['check_in_time'])
                <div style="margin-bottom:16px;">
                    <i class="bi bi-clock" style="font-size:2.5rem;color:#d1d5db;"></i>
                    <div style="font-size:.82rem;color:#9ca3af;margin-top:8px;">Not checked in yet</div>
                </div>
                <button class="btn btn-sm" onclick="checkIn()"
                    style="background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:9px;font-weight:600;padding:9px 24px;font-size:.85rem;">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Check In
                </button>
            @elseif($data['is_checked_in'])
                <div style="margin-bottom:12px;">
                    <i class="bi bi-check-circle-fill" style="font-size:2.5rem;color:#16a34a;"></i>
                </div>
                <div style="font-size:.78rem;color:#9ca3af;margin-bottom:4px;">Checked in at</div>
                <div style="font-size:1.6rem;font-weight:800;color:#16a34a;font-family:monospace;">{{ $data['check_in_time']->format('H:i') }}</div>
                <div id="workTimer" style="font-size:.78rem;color:#6b7280;margin:8px 0 16px;"></div>
                <button class="btn btn-sm" onclick="checkOut()"
                    style="background:transparent;color:#dc2626;border:1.5px solid #dc2626;border-radius:9px;font-weight:600;padding:8px 22px;font-size:.84rem;">
                    <i class="bi bi-box-arrow-right me-2"></i>Check Out
                </button>
            @else
                <div style="margin-bottom:12px;">
                    <i class="bi bi-check-all" style="font-size:2.5rem;color:#6366f1;"></i>
                </div>
                <div style="font-size:.82rem;color:#9ca3af;">Day Complete</div>
                <div style="font-size:1.3rem;font-weight:800;color:#111827;margin-top:4px;">{{ $data['work_hours_today'] }}h worked</div>
            @endif
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="col-md-8">
        <div class="row g-3">
            <div class="col-6">
                <div class="info-card" style="border-left:4px solid #f59e0b;">
                    <div class="info-card-body">
                        <div style="font-size:.78rem;color:#9ca3af;margin-bottom:4px;">Pending Tasks</div>
                        <div style="font-size:1.8rem;font-weight:800;color:#111827;line-height:1;">{{ $data['pending_tasks'] }}</div>
                        <a href="{{ route('employee.tasks.index') }}" style="font-size:.78rem;color:#6366f1;text-decoration:none;margin-top:6px;display:inline-block;">View tasks →</a>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="info-card" style="border-left:4px solid #16a34a;">
                    <div class="info-card-body">
                        <div style="font-size:.78rem;color:#9ca3af;margin-bottom:4px;">Completed This Month</div>
                        <div style="font-size:1.8rem;font-weight:800;color:#111827;line-height:1;">{{ $data['completed_tasks_month'] }}</div>
                        <div style="font-size:.78rem;color:#9ca3af;margin-top:6px;">tasks done</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="info-card" style="border-left:4px solid #6366f1;">
                    <div class="info-card-body">
                        <div style="font-size:.78rem;color:#9ca3af;margin-bottom:6px;">Performance Score</div>
                        <div style="font-size:1.4rem;font-weight:800;color:#6366f1;margin-bottom:6px;">{{ $data['performance_score'] }}%</div>
                        <div style="height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                            <div style="width:{{ $data['performance_score'] }}%;height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="info-card" style="border-left:4px solid #0ea5e9;">
                    <div class="info-card-body">
                        <div style="font-size:.78rem;color:#9ca3af;margin-bottom:8px;">Leave Balance</div>
                        <div class="d-flex gap-3">
                            <div>
                                <div style="font-size:1.3rem;font-weight:800;color:#16a34a;line-height:1;">{{ $data['leave_balance']['annual'] }}</div>
                                <div style="font-size:.72rem;color:#9ca3af;">Annual</div>
                            </div>
                            <div>
                                <div style="font-size:1.3rem;font-weight:800;color:#0ea5e9;line-height:1;">{{ $data['leave_balance']['sick'] }}</div>
                                <div style="font-size:.72rem;color:#9ca3af;">Sick</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Tasks & Attendance --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">My Recent Tasks</span>
                <a href="{{ route('employee.tasks.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:7px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('employee.tasks.show', $task) }}" style="font-weight:700;font-size:.87rem;color:#111827;text-decoration:none;">
                                    {{ Str::limit($task->title, 38) }}
                                </a>
                                <div style="font-size:.72rem;color:#9ca3af;">{{ $task->project?->name ?? 'No Project' }}</div>
                            </td>
                            <td>
                                @php
                                    $pColor = ['high'=>'danger','medium'=>'warning','low'=>'success'][$task->priority] ?? 'secondary';
                                @endphp
                                <span class="spill spill-{{ $pColor === 'danger' ? 'danger' : ($pColor === 'warning' ? 'warning' : 'success') }}" style="font-size:.7rem;">{{ ucfirst($task->priority) }}</span>
                            </td>
                            <td>
                                @php
                                    $sColor = ['todo'=>'secondary','in_progress'=>'processing','done'=>'success','cancelled'=>'cancelled'][$task->status] ?? 'secondary';
                                @endphp
                                <span class="spill spill-{{ $sColor }}" style="font-size:.7rem;">{{ ucwords(str_replace('_',' ',$task->status)) }}</span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;min-width:80px;">
                                    <div style="flex:1;height:5px;background:#f3f4f6;border-radius:3px;overflow:hidden;">
                                        <div style="width:{{ $task->progress }}%;height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6);border-radius:3px;"></div>
                                    </div>
                                    <span style="font-size:.72rem;color:#6b7280;">{{ $task->progress }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4">
                            <div class="empty-state"><i class="bi bi-list-task"></i><p>No tasks assigned</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="table-card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="card-title">Recent Attendance</span>
                <a href="{{ route('employee.attendance.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;border-radius:7px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table modern-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentAttendance as $att)
                        @php
                            $attColor = match($att->status) {
                                'present' => 'success',
                                'late'    => 'warning',
                                'on_leave'=> 'info',
                                default   => 'danger'
                            };
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700;font-size:.85rem;color:#111827;">{{ $att->date->format('M d') }}</div>
                                <div style="font-size:.72rem;color:#9ca3af;">{{ $att->date->format('l') }}</div>
                            </td>
                            <td><span class="spill spill-{{ $attColor }}" style="font-size:.7rem;">{{ ucfirst($att->status) }}</span></td>
                            <td>
                                <div style="font-size:.83rem;color:#374151;font-family:monospace;">{{ $att->check_in?->format('H:i') ?? '--' }}–{{ $att->check_out?->format('H:i') ?? '--' }}</div>
                                <div style="font-size:.72rem;color:#9ca3af;">{{ $att->work_hours }}h</div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3">
                            <div class="empty-state"><i class="bi bi-calendar-x"></i><p>No attendance records</p></div>
                        </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
setInterval(() => {
    document.getElementById('clock').textContent = new Date().toLocaleTimeString('en-US', { hour12: false });
}, 1000);

@if($data['is_checked_in'] && $data['check_in_time'])
const checkInTime = new Date('{{ $data['check_in_time']->toISOString() }}');
setInterval(() => {
    const diff = new Date() - checkInTime;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    const el = document.getElementById('workTimer');
    if (el) el.textContent = `${h}h ${m}m ${s}s working`;
}, 1000);
@endif

function checkIn() {
    APP.ajax('/employee/attendance/check-in', 'POST')
        .done(res => {
            if (res.success) { APP.toast('Checked in at ' + res.check_in + '!'); setTimeout(() => location.reload(), 1000); }
        })
        .fail(err => APP.toast(err.responseJSON?.message || 'Failed', 'error'));
}

function checkOut() {
    APP.confirm('Check Out', 'Are you sure you want to check out?', () => {
        APP.ajax('/employee/attendance/check-out', 'POST')
            .done(res => {
                if (res.success) { APP.toast(`Checked out! Worked ${res.work_hours}h`); setTimeout(() => location.reload(), 1000); }
            })
            .fail(err => APP.toast(err.responseJSON?.message || 'Failed', 'error'));
    });
}
</script>
@endpush
