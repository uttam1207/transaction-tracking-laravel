@extends('layouts.app')
@section('title', 'Holiday Management')

@section('content')

<div class="page-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3" style="position:relative;z-index:1;">
        <div>
            <h4>Holiday Management</h4>
            <p>Manage public and company holidays</p>
        </div>
        <button class="btn btn-sm btn-primary-grad px-4" data-bs-toggle="modal" data-bs-target="#holidayModal" onclick="openCreate()">
            <i class="bi bi-plus-circle me-1"></i>Add Holiday
        </button>
    </div>
</div>

<div class="table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="card-title">Holidays {{ date('Y') }}</span>
        <select id="yearFilter" class="form-select form-select-sm" style="width:auto;border-radius:8px;border:1.5px solid #e5e7eb;font-size:.83rem;"
            onchange="location.href='?year='+this.value">
            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                <option value="{{ $y }}" @selected(request('year', date('Y')) == $y)>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <div class="table-responsive">
        <table class="table modern-table mb-0">
            <thead>
                <tr>
                    <th>Holiday Name</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($holidays as $holiday)
                @php
                    $isPast = \Carbon\Carbon::parse($holiday->date)->isPast();
                    $tMap = ['public'=>'secondary','company'=>'success','optional'=>'warning','restricted'=>'info'];
                @endphp
                <tr style="{{ $isPast ? 'opacity:.6;' : '' }}">
                    <td style="font-weight:700;font-size:.87rem;color:#111827;">{{ $holiday->name }}</td>
                    <td style="font-size:.83rem;color:#374151;">{{ \Carbon\Carbon::parse($holiday->date)->format('d M Y') }}</td>
                    <td style="font-size:.82rem;color:#6b7280;">{{ \Carbon\Carbon::parse($holiday->date)->format('l') }}</td>
                    <td><span class="spill spill-{{ $tMap[$holiday->type ?? 'public'] ?? 'secondary' }}" style="font-size:.72rem;">{{ ucfirst($holiday->type ?? 'public') }}</span></td>
                    <td><span class="spill spill-{{ $holiday->is_active ? 'active' : 'inactive' }}" style="font-size:.72rem;">{{ $holiday->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="act-btn act-edit" title="Edit"
                                onclick="openEdit({{ $holiday->id }}, '{{ addslashes($holiday->name) }}', '{{ $holiday->date }}', '{{ $holiday->type }}', {{ $holiday->is_active ? 1 : 0 }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="act-btn act-delete" title="Delete" onclick="deleteHoliday({{ $holiday->id }})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state"><i class="bi bi-calendar3"></i><p>No holidays configured</p></div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="holidayModalTitle">Add Holiday</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="holidayForm" method="POST">
                @csrf
                <span id="holidayMethod"></span>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="flabel">Holiday Name <span class="req">*</span></label>
                            <input type="text" name="name" id="hName" class="form-control" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-12">
                            <label class="flabel">Date <span class="req">*</span></label>
                            <input type="date" name="date" id="hDate" class="form-control" required
                                style="border-radius:9px;border:1.5px solid #e5e7eb;">
                        </div>
                        <div class="col-6">
                            <label class="flabel">Type</label>
                            <select name="type" id="hType" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="public">Public</option>
                                <option value="company">Company</option>
                                <option value="optional">Optional</option>
                                <option value="restricted">Restricted</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="flabel">Status</label>
                            <select name="is_active" id="hStatus" class="form-select" style="border-radius:9px;border:1.5px solid #e5e7eb;">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary-grad px-4" id="hSubmitBtn">Add Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name=csrf-token]').content;

function openCreate() {
    document.getElementById('holidayModalTitle').textContent = 'Add Holiday';
    document.getElementById('holidayForm').action = '{{ route("admin.holidays.store") }}';
    document.getElementById('holidayMethod').innerHTML = '';
    document.getElementById('hSubmitBtn').textContent = 'Add Holiday';
    document.getElementById('hName').value = '';
    document.getElementById('hDate').value = '';
    document.getElementById('hType').value = 'public';
    document.getElementById('hStatus').value = '1';
}

function openEdit(id, name, date, type, isActive) {
    document.getElementById('holidayModalTitle').textContent = 'Edit Holiday';
    document.getElementById('holidayForm').action = `/admin/holidays/${id}`;
    document.getElementById('holidayMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('hSubmitBtn').textContent = 'Save';
    document.getElementById('hName').value = name;
    document.getElementById('hDate').value = date;
    document.getElementById('hType').value = type;
    document.getElementById('hStatus').value = isActive;
    new bootstrap.Modal(document.getElementById('holidayModal')).show();
}

function deleteHoliday(id) {
    APP.confirm('Delete this holiday?', 'This action cannot be undone.', function() {
        fetch(`/admin/holidays/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            APP.toast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1000);
        });
    });
}
</script>
@endpush
