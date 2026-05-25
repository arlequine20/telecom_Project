@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Dashboard Overview')
@section('user-name', 'Admin')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-users fa-2x mb-3" style="color: var(--primary)"></i>
            <h3>{{ $totalCustomers }}</h3>
            <p class="text-muted mb-0">Total Customers</p>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-sim-card fa-2x mb-3" style="color: var(--success)"></i>
            <h3>{{ $activeSims }}</h3>
            <p class="text-muted mb-0">Active SIM Cards</p>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-money-bill-wave fa-2x mb-3" style="color: var(--warning)"></i>
            <h3>RWF {{ number_format($totalBalance, 2) }}</h3>
            <p class="text-muted mb-0">Total Balance</p>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="stat-card">
            <i class="fas fa-hourglass-half fa-2x mb-3" style="color: var(--danger)"></i>
            <h3>{{ $pendingTransactions }}</h3>
            <p class="text-muted mb-0">Pending Transfers</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5>Recent Transactions</h5>
    </div>
    <div class="card-body">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTransactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_reference }}</td>
                    <td>{{ $transaction->fromSim->phone_number ?? 'N/A' }}</td>
                    <td>{{ $transaction->toSim->phone_number ?? 'N/A' }}</td>
                    <td>RWF {{ number_format($transaction->amount, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $transaction->status }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </td>
                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection