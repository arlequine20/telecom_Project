@extends('layouts.app')

@section('title', 'My Dashboard')

@section('sidebar')
    <a class="nav-link active" href="{{ route('user.dashboard') }}">
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

@section('header-title', 'My Dashboard')
@section('user-name', auth()->user()->name)

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="stat-card">
            <i class="fas fa-wallet fa-2x mb-3" style="color: #FF6B00"></i>
            <h3>RWF {{ number_format($mainBalance, 2) }}</h3>
            <p class="text-muted mb-0">Total Balance</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="stat-card">
            <i class="fas fa-sim-card fa-2x mb-3" style="color: #00B894"></i>
            <h3>{{ $simCards->count() }}</h3>
            <p class="text-muted mb-0">My SIM Cards</p>
            <a href="{{ route('user.sims') }}" class="btn btn-sm btn-success mt-3">Recharge SIM</a>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="stat-card">
            <i class="fas fa-exchange-alt fa-2x mb-3" style="color: #FFA502"></i>
            <a href="{{ route('user.transfer') }}" class="btn btn-primary-custom w-100">
                Send Money <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>My SIM Cards</h5>
            </div>
            <div class="card-body">
                @if(count($simCards) > 0)
                    @foreach($simCards as $sim)
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>📱 {{ $sim->phone_number }}</strong>
                                <p class="mb-0 text-muted">Balance: RWF {{ number_format($sim->balance, 2) }}</p>
                                <span class="badge bg-{{ $sim->status == 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($sim->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-center text-muted">No SIM cards found</p>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Activity</h5>
            </div>
            <div class="card-body">
                @if(count($recentTransactions) > 0)
                    @php
                        $mySimIds = $simCards->pluck('id')->toArray();
                    @endphp
                    @foreach($recentTransactions as $transaction)
                    @php
                        $isSent = in_array($transaction->from_sim_id, $mySimIds);
                    @endphp
                    <div class="mb-3 p-2 border-bottom">
                        <div class="d-flex justify-content-between">
                            <div>
                                @if($isSent)
                                    <span class="text-danger">📤 Sent</span>
                                    <small class="text-muted d-block">To: {{ $transaction->toSim->phone_number ?? 'N/A' }}</small>
                                @else
                                    <span class="text-success">📥 Received</span>
                                    <small class="text-muted d-block">From: {{ $transaction->fromSim->phone_number ?? 'N/A' }}</small>
                                @endif
                            </div>
                            <div class="text-end">
                                <strong class="{{ $isSent ? 'text-danger' : 'text-success' }}">
                                    {{ $isSent ? '-' : '+' }}RWF {{ number_format($transaction->amount, 2) }}
                                </strong>
                                <br>
                                <span class="badge bg-{{ $transaction->status == 'approved' ? 'success' : ($transaction->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($transaction->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <p class="text-center text-muted">No transactions yet</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection