@extends('layouts.app')

@section('title', $report->title . ' | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Report Details')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="fas fa-arrow-left me-2"></i>Back to Reports
        </a>
        <h1 class="page-title mb-2">{{ $report->title }}</h1>
        <div class="text-muted">
            <strong>Type:</strong> {{ $report->getTypeLabel() }}<br>
            <strong>Period:</strong> {{ $report->start_date->format('Y-m-d H:i') }} to {{ $report->end_date->format('Y-m-d H:i') }}<br>
            <strong>Generated:</strong> {{ $report->created_at->format('Y-m-d H:i:s') }}
        </div>
    </div>
    <div class="btn-group" role="group">
        <a href="{{ route('admin.reports.export-csv', $report) }}" class="btn btn-outline-success">
            <i class="fas fa-download me-2"></i>CSV
        </a>
        <a href="{{ route('admin.reports.export-word', $report) }}" class="btn btn-outline-secondary">
            <i class="fas fa-file-word me-2"></i>Word
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @switch($report->type)
            @case('summary')
                @include('admin.reports.partials.summary', ['data' => $report->data])
                @break

            @case('transaction')
                @include('admin.reports.partials.transaction', ['data' => $report->data])
                @break

            @case('customer')
                @include('admin.reports.partials.customer', ['data' => $report->data])
                @break

            @case('sim_card')
                @include('admin.reports.partials.sim-card', ['data' => $report->data])
                @break

            @case('revenue')
                @include('admin.reports.partials.revenue', ['data' => $report->data])
                @break

            @default
                <p class="text-muted mb-0">Unknown report type.</p>
        @endswitch
    </div>
</div>
@endsection
