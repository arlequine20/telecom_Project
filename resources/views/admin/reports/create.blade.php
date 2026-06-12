@extends('layouts.app')

@section('title', 'Generate Report | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Generate Report')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Generate New Report</h1>
        <p class="text-muted mb-0">Choose a report type and date range.</p>
    </div>
    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.reports.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="type" class="form-label">Report Type</label>
                        <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required onchange="updateFilterOptions()">
                            <option value="">Select a report type...</option>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="form-control @error('start_date') is-invalid @enderror" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control @error('end_date') is-invalid @enderror" required>
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div id="filters-section" class="border rounded p-3 mb-4 d-none">
                        <h6 class="mb-3">Optional Filters</h6>

                        <div id="status-filter" class="mb-3 d-none">
                            <label for="filters_status" class="form-label">Status</label>
                            <select id="filters_status" name="filters[status]" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="failed">Failed</option>
                                <option value="reversed">Reversed</option>
                            </select>
                        </div>

                        <div id="tariff-filter" class="mb-3 d-none">
                            <label for="filters_tariff_plan" class="form-label">Tariff Plan</label>
                            <select id="filters_tariff_plan" name="filters[tariff_plan]" class="form-select">
                                <option value="">All Plans</option>
                                <option value="prepaid">Prepaid</option>
                                <option value="postpaid">Postpaid</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom text-white">
                            <i class="fas fa-chart-line me-2"></i>Generate Report
                        </button>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Quick Presets</h5>
                <div class="row g-2">
                    <div class="col-6"><button type="button" onclick="setPreset('today')" class="btn btn-outline-primary w-100 text-start">Today</button></div>
                    <div class="col-6"><button type="button" onclick="setPreset('week')" class="btn btn-outline-primary w-100 text-start">Last 7 Days</button></div>
                    <div class="col-6"><button type="button" onclick="setPreset('month')" class="btn btn-outline-primary w-100 text-start">Last 30 Days</button></div>
                    <div class="col-6"><button type="button" onclick="setPreset('year')" class="btn btn-outline-primary w-100 text-start">Last Year</button></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateFilterOptions() {
    const type = document.getElementById('type').value;
    const filtersSection = document.getElementById('filters-section');
    const statusFilter = document.getElementById('status-filter');
    const tariffFilter = document.getElementById('tariff-filter');

    filtersSection.classList.add('d-none');
    statusFilter.classList.add('d-none');
    tariffFilter.classList.add('d-none');

    if (type === 'transaction') {
        filtersSection.classList.remove('d-none');
        statusFilter.classList.remove('d-none');
    } else if (type === 'sim_card') {
        filtersSection.classList.remove('d-none');
        tariffFilter.classList.remove('d-none');
    }
}

function setPreset(preset) {
    const endDate = new Date();
    const startDate = new Date();

    if (preset === 'week') {
        startDate.setDate(endDate.getDate() - 7);
    } else if (preset === 'month') {
        startDate.setDate(endDate.getDate() - 30);
    } else if (preset === 'year') {
        startDate.setFullYear(endDate.getFullYear() - 1);
    }

    document.getElementById('start_date').value = formatDate(startDate);
    document.getElementById('end_date').value = formatDate(endDate);
}

function formatDate(date) {
    return date.toISOString().slice(0, 10);
}

document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('end_date').value) {
        document.getElementById('end_date').value = formatDate(new Date());
    }
    updateFilterOptions();
});
</script>
@endsection
