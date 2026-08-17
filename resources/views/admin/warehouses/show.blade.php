@extends('layouts.app')

@section('title', 'Warehouse — ' . $warehouse->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-0 fw-bold">{{ $warehouse->name }} <span class="badge bg-primary-subtle text-primary fs-6">{{ $warehouse->code }}</span></h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.warehouses.index') }}">Warehouses</a></li>
                <li class="breadcrumb-item active">{{ $warehouse->name }}</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addLocationModal">
                <i class="bi bi-geo-alt me-1"></i> Add Location
            </button>
            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Warehouse Info --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">Warehouse Details</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-muted">Code</dt><dd class="col-7">{{ $warehouse->code }}</dd>
                        <dt class="col-5 text-muted">Name</dt><dd class="col-7">{{ $warehouse->name }}</dd>
                        <dt class="col-5 text-muted">City</dt><dd class="col-7">{{ $warehouse->city ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Country</dt><dd class="col-7">{{ $warehouse->country ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Manager</dt><dd class="col-7">{{ $warehouse->manager?->name ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7"><span class="badge bg-{{ $warehouse->is_active ? 'success' : 'secondary' }}">{{ $warehouse->is_active ? 'Active' : 'Inactive' }}</span></dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-transparent fw-semibold">Locations ({{ $warehouse->locations->count() }})</div>
                <div class="list-group list-group-flush">
                    @forelse ($warehouse->locations as $loc)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-light text-dark border me-1">{{ $loc->code }}</span>
                            {{ $loc->name }}
                            <span class="text-muted small ms-1">({{ $loc->type }})</span>
                        </div>
                        @if (!$loc->is_active)<span class="badge bg-secondary">Inactive</span>@endif
                    </div>
                    @empty
                    <div class="list-group-item text-muted text-center py-3 small">No locations defined.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Stock --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold">Stock at this Warehouse</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Location</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Reserved</th>
                                    <th class="text-end">Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($stock as $s)
                                <tr>
                                    <td>{{ $s->inventoryItem?->name ?? '—' }}</td>
                                    <td class="text-muted small">{{ $s->location?->name ?? 'Default' }}</td>
                                    <td class="text-end">{{ number_format($s->quantity, 3) }}</td>
                                    <td class="text-end text-warning">{{ number_format($s->reserved_qty, 3) }}</td>
                                    <td class="text-end fw-semibold text-success">{{ number_format($s->available_qty, 3) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No stock records.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($stock->hasPages())
                    <div class="px-3 py-2">{{ $stock->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Location Modal --}}
<div class="modal fade" id="addLocationModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Location</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" id="loc_code" class="form-control" placeholder="WH-001-A1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="loc_name" class="form-control" placeholder="Aisle A, Row 1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select id="loc_type" class="form-select">
                        <option value="bin">Bin</option>
                        <option value="rack">Rack</option>
                        <option value="aisle">Aisle</option>
                        <option value="zone">Zone</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="addLocation()">Add</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addLocation() {
    fetch('{{ route("admin.warehouses.locations.store", $warehouse) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({
            code: document.getElementById('loc_code').value,
            name: document.getElementById('loc_name').value,
            type: document.getElementById('loc_type').value,
        })
    }).then(r => r.json()).then(d => { if (d.success) location.reload(); else alert(d.message); });
}
</script>
@endpush
@endsection
