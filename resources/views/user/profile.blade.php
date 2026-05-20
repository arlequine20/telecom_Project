@extends('layouts.app')

@section('title', 'My Profile')

@section('sidebar')
    <a class="nav-link" href="{{ route('user.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link" href="{{ route('user.transfer') }}">
        <i class="fas fa-exchange-alt"></i> Send Money
    </a>
    <a class="nav-link" href="{{ route('user.history') }}">
        <i class="fas fa-history"></i> Transaction History
    </a>
    <a class="nav-link" href="{{ route('user.sims') }}">
        <i class="fas fa-sim-card"></i> My SIM Cards
    </a>
    <a class="nav-link active" href="{{ route('user.profile') }}">
        <i class="fas fa-user"></i> My Profile
    </a>
@endsection

@section('header-title', 'My Profile')
@section('user-name', auth()->user()->name ?? 'User')

@section('content')
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card p-4">
            <h5>Personal Information</h5>
            <p><strong>Name:</strong> {{ $customer->full_name }}</p>
            <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
            <p><strong>Phone:</strong> {{ $customer->phone }}</p>
            <p><strong>National ID:</strong> {{ $customer->national_id }}</p>
            <p><strong>Address:</strong> {{ $customer->address }}</p>
            <p><strong>Date of Birth:</strong> {{ $customer->date_of_birth->format('Y-m-d') }}</p>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card p-4">
            <h5>SIM Card Summary</h5>
            <p><strong>SIM Count:</strong> {{ $simCards->count() }}</p>
            <p><strong>Total Balance:</strong> RWF {{ number_format($simCards->sum('balance'), 2) }}</p>
            <p><strong>Data Balance:</strong> {{ $wallet->data_balance ?? 0 }} MB</p>
            <p><strong>Wallet Balance:</strong> RWF {{ number_format($wallet->balance ?? 0, 2) }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5>Assigned SIM Cards</h5>
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                @forelse($simCards as $sim)
                <tr>
                    <td>{{ $sim->phone_number }}</td>
                    <td>RWF {{ number_format($sim->balance, 2) }}</td>
                    <td>{{ ucfirst($sim->status) }}</td>
                    <td>{{ number_format($sim->data_balance, 2) }} MB</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No SIM cards assigned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
