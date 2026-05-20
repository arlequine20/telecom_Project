@extends('layouts.app')

@section('title', 'Recharge SIM | Telecom')

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
    <a class="nav-link" href="{{ route('user.profile') }}">
        <i class="fas fa-user"></i> My Profile
    </a>
@endsection

@section('header-title', 'Recharge SIM')
@section('user-name', auth()->user()->name ?? 'User')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-4">Recharge SIM {{ $sim->sim_number }}</h5>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p><strong>Phone:</strong> {{ $sim->phone_number }}</p>
        <p><strong>Current Balance:</strong> RWF {{ number_format($sim->balance, 2) }}</p>
        @if($wallet)
            <p><strong>Wallet Balance:</strong> RWF {{ number_format($wallet->balance, 2) }}</p>
            <div class="alert alert-info">
                Amount will be deducted from your wallet and added to this SIM card.
            </div>
        @endif

        <form method="POST" action="{{ route('user.sim.recharge.submit', $sim) }}">
            @csrf
            <div class="mb-3">
                <label for="amount" class="form-label">Amount to Recharge</label>
                <input type="number" step="0.01" min="1" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
            </div>

            <button type="submit" class="btn btn-success">Recharge Now</button>
            <a href="{{ route('user.sims') }}" class="btn btn-secondary ms-2">Back</a>
        </form>
    </div>
</div>
@endsection
