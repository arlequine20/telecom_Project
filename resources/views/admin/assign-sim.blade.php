@extends('layouts.app')

@section('title', 'Assign SIM Card | Telecom')

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

@section('header-title', 'Assign SIM Card')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-4">Assign SIM {{ $sim->sim_number }}</h5>

        <div class="mb-3">
            <p><strong>Phone:</strong> {{ $sim->phone_number }}</p>
            <p><strong>Current Status:</strong> {{ ucfirst($sim->status) }}</p>
            <p><strong>Current Customer:</strong> {{ $sim->customer->full_name ?? 'Unassigned' }}</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.sim-cards.assign.submit', $sim) }}">
            @csrf
            <div class="mb-3">
                <label for="customer_id" class="form-label">Select Customer</label>
                <select name="customer_id" id="customer_id" class="form-select" required>
                    <option value="">Choose a customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->full_name }} — {{ $customer->email }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Assign SIM</button>
            <a href="{{ route('admin.sim-cards') }}" class="btn btn-secondary ms-2">Back to SIM Cards</a>
        </form>
    </div>
</div>
@endsection
