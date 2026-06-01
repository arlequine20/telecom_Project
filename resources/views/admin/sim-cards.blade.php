@extends('layouts.app')

@section('title', 'SIM Cards | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
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
                    <td>{{ ucfirst($sim->status) }}</td>
                    <td>{{ $sim->customer->full_name ?? 'Unassigned' }}</td>
                    <td>
                        @if($sim->customer === null)
                            <a href="{{ route('admin.sim-cards.assign', $sim) }}" class="btn btn-sm btn-outline-primary">
                                Assign
                            </a>
                        @else
                            <a href="{{ route('admin.sim-cards.assign', $sim) }}" class="btn btn-sm btn-outline-primary">
                                Update
                            </a>
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
