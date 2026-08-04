@extends('layouts.app')
@section('title', 'ASDairy Enterprise Reports Center')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-primary"><i class="bi bi-file-earmark-bar-graph-fill me-2"></i>Module 17 — ASDairy Reports Center</h1>
            <p class="text-muted mb-0">Consolidated analytics & enterprise reports across all 20 modules.</p>
        </div>
    </div>

    <!-- 10 Dedicated Report Cards Grid -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary me-3"><i class="bi bi-calendar-event fs-3"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">1. Daily Farm Report</h6>
                        <span class="text-muted small">Cattle count, milk yield, feed & labor summary.</span>
                    </div>
                </div>
                <a href="{{ route('admin.reports.transactions') }}" class="btn btn-sm btn-outline-primary mt-auto">Generate Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3"><i class="bi bi-droplet-fill fs-3"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">2. Milk Production Report</h6>
                        <span class="text-muted small">Shift-wise yield, fat/SNF averages, loss analysis.</span>
                    </div>
                </div>
                <a href="{{ route('admin.milk.index') }}" class="btn btn-sm btn-outline-success mt-auto">View Milk Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info me-3"><i class="bi bi-card-checklist fs-3"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">3. Animal & Population Report</h6>
                        <span class="text-muted small">Herd breakdown, lactation cycles, calf birth rate.</span>
                    </div>
                </div>
                <a href="{{ route('admin.animals.index') }}" class="btn btn-sm btn-outline-info mt-auto">View Herd Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3"><i class="bi bi-graph-up-arrow fs-3"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">4. Financial P&L Report</h6>
                        <span class="text-muted small">Revenue, operational expenses, gross margin.</span>
                    </div>
                </div>
                <a href="{{ route('admin.reports.financial-summary') }}" class="btn btn-sm btn-outline-warning text-dark mt-auto">View Financial P&L</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 bg-danger bg-opacity-10 text-danger me-3"><i class="bi bi-heart-pulse-fill fs-3"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">5. Breeding & Fertility Report</h6>
                        <span class="text-muted small">AI success rate, repeat breeders, expected calving.</span>
                    </div>
                </div>
                <a href="{{ route('admin.breeding.index') }}" class="btn btn-sm btn-outline-danger mt-auto">View Breeding Report</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 bg-secondary bg-opacity-10 text-secondary me-3"><i class="bi bi-boxes fs-3"></i></div>
                    <div>
                        <h6 class="fw-bold mb-0">6. Inventory & Stock Report</h6>
                        <span class="text-muted small">Available balances, stock movement audit history.</span>
                    </div>
                </div>
                <a href="{{ route('admin.stock.export.pdf') }}" class="btn btn-sm btn-outline-secondary mt-auto">Download Stock PDF</a>
            </div>
        </div>
    </div>
</div>
@endsection
