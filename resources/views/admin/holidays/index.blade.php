@extends('layouts.app')

@section('title', 'Holiday Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Holiday Management</h4>
        <p class="text-muted mb-0">Manage public and company holidays</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal" onclick="openCreate()">
        <i class="bi bi-plus-circle me-1"></i>Add Holiday
    </button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold">Holidays {{ date('Y') }}</span>
        <select id="yearFilter" class="form-select form-select-sm" style="width: auto;"
            onchange="location.href='?year='+this.value">
            @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                <option value="{{ $y }}" @selected(request('year', date('Y')) == $y)>{{ $y }}</option>
            @endfor
        </select>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Holiday Name</th>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $holiday)
                    @php $isPast = \Carbon\Carbon::parse($holiday->date)->isPast(); @endphp
                    <tr class="{{ $isPast ? 'text-muted' : '' }}">
                        <td class="fw-semibold">{{ $holiday->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($holiday->date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($holiday->date)->format('l') }}</td>
                        <td>
                            @php
                                $typeColors = ['public' => 'primary', 'company' => 'success', 'optional' => 'warning', 'restricted' => 'info'];
                            @endphp
                            <span class="badge bg-{{ $typeColors[$holiday->type ?? 'public'] ?? 'secondary' }}">
                                {{ ucfirst($holiday->type ?? 'public') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $holiday->is_active ? 'success' : 'secondary' }}">
                                {{ $holiday->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary"
                                    onclick="openEdit({{ $holiday->id }}, '{{ addslashes($holiday->name) }}', '{{ $holiday->date }}', '{{ $holiday->type }}', {{ $holiday->is_active ? 1 : 0 }})"
                                    title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger"
                                    onclick="deleteHoliday({{ $holiday->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar3 fs-1 d-block mb-2"></i>
                            No holidays configured
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="holidayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="holidayModalTitle">Add Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="holidayForm" method="POST">
                @csrf
                <span id="holidayMethod"></span>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Holiday Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="hName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" id="hDate" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Type</label>
                            <select name="type" id="hType" class="form-select">
                                <option value="public">Public</option>
                                <option value="company">Company</option>
                                <option value="optional">Optional</option>
                                <option value="restricted">Restricted</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Status</label>
                            <select name="is_active" id="hStatus" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="hSubmitBtn">Add Holiday</button>
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
    ['hName'].forEach(id => document.getElementById(id).value = '');
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
    APP.confirm('Delete this holiday?', function() {
        fetch(`/admin/holidays/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        }).then(r => r.json()).then(data => {
            APP.toast(data.message, data.success ? 'success' : 'danger');
            if (data.success) setTimeout(() => location.reload(), 1000);
        });
    });
}
</script>
@endpush
