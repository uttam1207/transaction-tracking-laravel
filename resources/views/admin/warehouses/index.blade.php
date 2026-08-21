@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0 fw-bold">Warehouses</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Warehouses</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.warehouses.transfers') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left-right me-1"></i> Stock Transfers
                </a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWarehouseModal">
                    <i class="bi bi-plus-lg me-1"></i> New Warehouse
                </button>
            </div>
        </div>

        {{-- Search --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search code or name…" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="active" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="1" @selected(request('active') === '1')>Active</option>
                            <option value="0" @selected(request('active') === '0')>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-outline-primary">Filter</button>
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3">
            @forelse ($warehouses as $wh)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary me-1">{{ $wh->code }}</span>
                                    @if (!$wh->is_active)
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </div>
                                <button class="btn btn-sm btn-outline-secondary"
                                    onclick="editWarehouse({{ $wh->id }}, @json($wh))"><i
                                        class="bi bi-pencil"></i></button>
                            </div>
                            <h6 class="fw-bold mb-1">{{ $wh->name }}</h6>
                            <div class="text-muted small mb-2">
                                @if ($wh->city)
                                    {{ $wh->city }}@if ($wh->country)
                                        , {{ $wh->country }}
                                    @endif
                                @endif
                            </div>
                            <div class="d-flex gap-3 small">
                                <span><i class="bi bi-geo-alt me-1"></i>{{ $wh->locations_count }} locations</span>
                                @if ($wh->manager)
                                    <span><i class="bi bi-person me-1"></i>{{ $wh->manager->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <a href="{{ route('admin.warehouses.show', $wh) }}"
                                class="btn btn-sm btn-outline-primary w-100">View Stock</a>
                        </div>
                    </div>
                </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">No warehouses found. Create your first warehouse.</div>
                @endforelse
            </div>

            @if ($warehouses->hasPages())
                <div class="mt-3">{{ $warehouses->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- Add Warehouse Modal --}}
        <div class="modal fade" id="addWarehouseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">New Warehouse</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Code <span class="text-danger">*</span></label>
                            <input type="text" id="wh_code" class="form-control" placeholder="WH-001" maxlength="20">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="wh_name" class="form-control" placeholder="Main Warehouse">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" id="wh_city" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Country</label>
                            <input type="text" id="wh_country" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Manager</label>
                            <select id="wh_manager" class="form-select">
                                <option value="">None</option>
                                @foreach ($managers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea id="wh_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" onclick="createWarehouse()">Create</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Edit Warehouse Modal --}}
        <div class="modal fade" id="editWarehouseModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Warehouse</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-4"><label class="form-label">Code</label><input type="text" id="e_code"
                                class="form-control"></div>
                        <div class="col-md-8"><label class="form-label">Name</label><input type="text" id="e_name"
                                class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">City</label><input type="text" id="e_city"
                                class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Country</label><input type="text" id="e_country"
                                class="form-control"></div>
                        <div class="col-md-6">
                            <label class="form-label">Manager</label>
                            <select id="e_manager" class="form-select">
                                <option value="">None</option>
                                @foreach ($managers as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select id="e_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" id="saveEditBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                let editWhId = null;

                function createWarehouse() {
                    fetch('{{ route('admin.warehouses.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            code: document.getElementById('wh_code').value,
                            name: document.getElementById('wh_name').value,
                            city: document.getElementById('wh_city').value,
                            country: document.getElementById('wh_country').value,
                            manager_id: document.getElementById('wh_manager').value || null,
                            address: document.getElementById('wh_address').value,
                        })
                    }).then(r => r.json()).then(d => {
                        if (d.success) location.reload();
                        else alert(d.message);
                    });
                }

                function editWarehouse(id, data) {
                    editWhId = id;
                    document.getElementById('e_code').value = data.code;
                    document.getElementById('e_name').value = data.name;
                    document.getElementById('e_city').value = data.city ?? '';
                    document.getElementById('e_country').value = data.country ?? '';
                    document.getElementById('e_manager').value = data.manager_id ?? '';
                    document.getElementById('e_active').value = data.is_active ? '1' : '0';
                    new bootstrap.Modal(document.getElementById('editWarehouseModal')).show();
                }

                document.getElementById('saveEditBtn').addEventListener('click', function() {
                    fetch(`/admin/warehouses/${editWhId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            _method: 'PUT',
                            code: document.getElementById('e_code').value,
                            name: document.getElementById('e_name').value,
                            city: document.getElementById('e_city').value,
                            country: document.getElementById('e_country').value,
                            manager_id: document.getElementById('e_manager').value || null,
                            is_active: document.getElementById('e_active').value,
                        })
                    }).then(r => r.json()).then(d => {
                        if (d.success) location.reload();
                        else alert(d.message);
                    });
                });
            </script>
        @endpush
    @endsection
