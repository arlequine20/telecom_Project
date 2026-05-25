@extends('layouts.app')

@section('title', 'My SIM Cards')

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
    <a class="nav-link active" href="{{ route('user.sims') }}">
        <i class="fas fa-sim-card"></i> My SIM Cards
    </a>
    <a class="nav-link" href="{{ route('user.profile') }}">
        <i class="fas fa-user"></i> My Profile
    </a>
@endsection

@section('header-title', 'My SIM Cards')
@section('user-name', auth()->user()->name ?? 'User')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Wallet Balance</h5>
                <p class="mb-0">RWF {{ number_format(auth()->user()->wallet->balance ?? 0, 2) }}</p>
            </div>
            <div class="text-end">
                <a href="{{ route('user.wallet.topup') }}" class="btn btn-primary">Top Up Wallet</a>
            </div>
        </div>
        <div class="alert alert-info mt-3 mb-0">
            Use the <strong>Top Up Wallet</strong> button to add money to your wallet first, then recharge your SIM card.
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>SIM Number</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Balance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($simCards as $sim)
                <tr>
                    <td>{{ $sim->sim_number }}</td>
                    <td>{{ $sim->phone_number }}</td>
                    <td>{{ ucfirst($sim->status) }}</td>
                    <td>RWF {{ number_format($sim->balance, 2) }}</td>
                    <td>
                        <a href="{{ route('user.sim.recharge', $sim) }}" class="btn btn-sm btn-outline-success">Recharge</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No SIM cards found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
