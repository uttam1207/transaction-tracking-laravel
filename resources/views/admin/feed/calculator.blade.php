@extends('layouts.app')

@section('title', 'Feed Auto Calculation & Shortage Alerts')

@section('content')
<div class="container-fluid py-3">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-success"><i class="bi bi-cpu me-2"></i>Feed Auto Calculation & Alerts</h1>
            <p class="text-muted mb-0">Automatic daily feed requirement calculation, real-time stock depletion forecast & proactive alerts.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.stock.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-boxes me-1"></i> View Stock Warehouse
            </a>
        </div>
    </div>

    <!-- Active Alerts Banners -->
    @if(count($data['alerts']) > 0)
        <div class="mb-4">
            <h5 class="fw-bold mb-2 text-danger"><i class="bi bi-bell-fill me-2"></i>Proactive Feed Shortage Alerts</h5>
            @foreach($data['alerts'] as $alert)
                <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show shadow-sm border-0 d-flex align-items-center mb-2" role="alert">
                    <i class="{{ $alert['icon'] }} fs-4 me-3"></i>
                    <div class="fw-bold fs-6">{{ $alert['message'] }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Feed Stock Optimal!</strong> All required feed items have sufficient stock for upcoming feeding cycles.
            </div>
        </div>
    @endif

    <!-- Animal Population Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Total Cattle</div>
                <div class="fs-3 fw-bold text-primary">{{ $data['total_animals'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Milking Buffaloes</div>
                <div class="fs-3 fw-bold text-success">{{ $data['milking_animals'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Pregnant Animals</div>
                <div class="fs-3 fw-bold text-info">{{ $data['pregnant_animals'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Dry Animals</div>
                <div class="fs-3 fw-bold text-secondary">{{ $data['dry_animals'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="text-muted small">Calves</div>
                <div class="fs-3 fw-bold text-warning">{{ $data['calves'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm text-center p-3 bg-success bg-opacity-10">
                <div class="text-muted small">Daily Feed Need</div>
                <div class="fs-5 fw-bold text-success">{{ number_format(array_sum($data['totals']), 1) }} kg</div>
            </div>
        </div>
    </div>

    <!-- Feed Depletion Forecast & Stock Comparison -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold"><i class="bi bi-graph-down me-2 text-primary"></i>Feed Depletion & Stock Forecast</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary">Auto-Calculated Real-Time</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Feed Item</th>
                        <th class="text-end">Available Stock</th>
                        <th class="text-end">Daily Requirement</th>
                        <th class="text-end">Weekly Requirement</th>
                        <th class="text-end">Monthly Requirement</th>
                        <th class="text-center">Days of Stock Remaining</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['stock_comparison'] as $feedName => $row)
                        <tr>
                            <td class="fw-bold fs-6">{{ $feedName }}</td>
                            <td class="text-end fw-bold text-primary">{{ number_format($row['available'], 1) }} {{ $row['unit'] }}</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($row['daily_need'], 1) }} {{ $row['unit'] }}</td>
                            <td class="text-end text-muted">{{ number_format($row['weekly_need'], 1) }} {{ $row['unit'] }}</td>
                            <td class="text-end text-muted">{{ number_format($row['monthly_need'], 1) }} {{ $row['unit'] }}</td>
                            <td class="text-center fs-5 fw-bold">
                                @if($row['days_left'] <= 3)
                                    <span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $row['days_left'] }} Days</span>
                                @elseif($row['days_left'] <= 7)
                                    <span class="text-warning"><i class="bi bi-exclamation-circle-fill me-1"></i> {{ $row['days_left'] }} Days</span>
                                @else
                                    <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> {{ $row['days_left'] }} Days</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($row['days_left'] <= 3)
                                    <span class="badge bg-danger">Critical Shortage</span>
                                @elseif($row['available'] <= $row['min_stock'])
                                    <span class="badge bg-warning text-dark">Below Reorder Level</span>
                                @else
                                    <span class="badge bg-success">Sufficient</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <!-- Animal Group Head Count Management -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-people-fill me-2 text-primary"></i>Animal Group Head Counts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Group Name</th>
                                    <th class="text-center">Head Count</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($animalGroups as $ag)
                                    <tr>
                                        <td><strong>{{ $ag->name }}</strong></td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.feed.groups.update', $ag) }}" method="POST" class="d-inline-flex gap-2">
                                                @csrf
                                                <input type="number" min="0" name="head_count" class="form-control form-control-sm text-center" value="{{ $ag->head_count }}" style="width: 80px;">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Update Count"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                        </td>
                                        <td class="text-end"><span class="badge bg-light text-dark border">{{ $ag->group_key }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feed Plan Configuration -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-sliders2 me-2 text-success"></i>Feed Plan (Daily kg per Animal)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.feed.plans.update') }}" method="POST">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Group</th>
                                        <th>Feed Item</th>
                                        <th class="text-end">Daily kg / Head</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $planIdx = 0; @endphp
                                    @foreach($animalGroups as $ag)
                                        @foreach($ag->feedPlans as $fp)
                                            <tr>
                                                <td class="small text-muted">{{ $ag->name }}</td>
                                                <td><strong>{{ $fp->feed_item_name }}</strong></td>
                                                <td class="text-end">
                                                    <input type="hidden" name="plans[{{ $planIdx }}][id]" value="{{ $fp->id }}">
                                                    <input type="number" step="0.01" min="0" name="plans[{{ $planIdx }}][quantity_per_animal_kg]" class="form-control form-control-sm text-end d-inline-block" value="{{ $fp->quantity_per_animal_kg }}" style="width: 90px;">
                                                </td>
                                            </tr>
                                            @php $planIdx++; @endphp
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Save Feed Plan Allocations</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
