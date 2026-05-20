@extends('layouts.app')

@section('title', 'Transaction History | Telecom')

@section('sidebar')
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link" href="{{ route('admin.customers') }}">
        <i class="fas fa-users"></i> Customers
    </a>
    <a class="nav-link" href="{{ route('admin.sim-cards') }}">
        <i class="fas fa-sim-card"></i> SIM Cards
    </a>
    <a class="nav-link" href="{{ route('admin.pending') }}">
        <i class="fas fa-clock"></i> Pending Approvals
    </a>
    <a class="nav-link active" href="{{ route('admin.history') }}">
        <i class="fas fa-history"></i> Transaction History
    </a>
@endsection

@section('header-title', 'All Transactions')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Amount</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_reference }}</td>
                    <td>{{ $transaction->fromSim->phone_number ?? 'N/A' }}</td>
                    <td>{{ $transaction->toSim->phone_number ?? 'N/A' }}</td>
                    <td>RWF {{ number_format($transaction->amount, 2) }}</td>
                    <td>RWF {{ number_format($transaction->fee, 2) }}</td>
                    <td>{{ ucfirst($transaction->status) }}</td>
                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $transactions->links() }}
    </div>
</div>
@endsection
