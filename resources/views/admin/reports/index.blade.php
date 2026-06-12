@extends('layouts.app')

@section('title', 'Reports | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Reports')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="page-title mb-1">Reports</h1>
        <p class="text-muted mb-0">Generate, view, and export system reports.</p>
    </div>
    <a href="{{ route('admin.reports.create') }}" class="btn btn-primary-custom text-white">
        <i class="fas fa-plus me-2"></i>Generate Report
    </a>
</div>

@if ($reports->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
            <h5>No reports generated yet</h5>
            <p class="text-muted">Create your first report to see it listed here.</p>
            <a href="{{ route('admin.reports.create') }}" class="btn btn-primary-custom text-white">
                <i class="fas fa-plus me-2"></i>Create Report
            </a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Report</th>
                            <th>Type</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Generated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>
                                    <strong>{{ $report->title }}</strong>
                                    <div class="text-muted small">By {{ $report->generatedBy?->name ?? 'System' }}</div>
                                </td>
                                <td>{{ $report->getTypeLabel() }}</td>
                                <td>{{ $report->start_date->format('Y-m-d') }} to {{ $report->end_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-success">{{ ucfirst($report->status) }}</span></td>
                                <td>{{ $report->created_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.reports.export-csv', $report) }}" class="btn btn-outline-success">
                                            CSV
                                        </a>
                                        <a href="{{ route('admin.reports.export-word', $report) }}" class="btn btn-outline-secondary">
                                            Word
                                        </a>
                                    </div>
                                    <form action="{{ route('admin.reports.destroy', $report) }}" method="POST" class="d-inline-block ms-1" onsubmit="return confirm('Delete this report?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $reports->links() }}
    </div>
@endif
@endsection
