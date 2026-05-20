@extends('layouts.app')

@section('title', 'SIM Cards | Telecom')

@section('sidebar')
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link" href="{{ route('admin.customers') }}">
        <i class="fas fa-users"></i> Customers
    </a>
    <a class="nav-link active" href="{{ route('admin.sim-cards') }}">
        <i class="fas fa-sim-card"></i> SIM Cards
    </a>
    <a class="nav-link" href="{{ route('admin.pending') }}">
        <i class="fas fa-clock"></i> Pending Approvals
    </a>
    <a class="nav-link" href="{{ route('admin.history') }}">
        <i class="fas fa-history"></i> Transaction History
    </a>
@endsection

@section('header-title', 'SIM Cards')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">SIM Card Inventory</h5>
            <a href="{{ route('admin.sim-cards.create') }}" class="btn btn-primary btn-sm">Add New SIM</a>
        </div>
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>SIM Number</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Customer</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($simCards as $sim)
                <tr>
                    <td>{{ $sim->sim_number }}</td>
                    <td>{{ $sim->phone_number }}</td>
                    <td>RWF {{ number_format($sim->balance, 2) }}</td>
                    <td>{{ number_format($sim->data_balance ?? 0, 2) }} MB</td>
                    <td>{{ ucfirst($sim->status) }}</td>
                    <td>{{ $sim->customer->full_name ?? 'Unassigned' }}</td>
                    <td>
                        <a href="{{ route('admin.sim-cards.buy-data', $sim) }}" class="btn btn-sm btn-outline-success mb-1">
                            Buy Data
                        </a>
                        @if($sim->customer === null)
                            <a href="{{ route('admin.sim-cards.assign', $sim) }}" class="btn btn-sm btn-outline-primary">
                                Assign
                            </a>
                        @else
                            <span class="text-muted d-block">Assigned</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $simCards->links() }}
    </div>
</div>
@endsection
