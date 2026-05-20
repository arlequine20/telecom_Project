@extends('layouts.app')

@section('title', 'Transaction History')

@section('sidebar')
    <a class="nav-link" href="{{ route('user.dashboard') }}">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a class="nav-link" href="{{ route('user.transfer') }}">
        <i class="fas fa-exchange-alt"></i> Send Money
    </a>
    <a class="nav-link active" href="{{ route('user.history') }}">
        <i class="fas fa-history"></i> Transaction History
    </a>
    <a class="nav-link" href="{{ route('user.sims') }}">
        <i class="fas fa-sim-card"></i> My SIM Cards
    </a>
    <a class="nav-link" href="{{ route('user.profile') }}">
        <i class="fas fa-user"></i> My Profile
    </a>
@endsection

@section('header-title', 'My Transaction History')
@section('user-name', auth()->user()->name)

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="card">
    <div class="card-body">
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Type</th>
                    <th>From/To</th>
                    <th>Amount</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $mySimIds = $simCards->pluck('id')->toArray();
                @endphp
                @foreach($transactions as $transaction)
                @php
                    $isSent = in_array($transaction->from_sim_id, $mySimIds);
                @endphp
                <tr>
                    <td>{{ $transaction->transaction_reference }}</td>
                    <td>
                        @if($isSent)
                            <span class="text-danger">Sent</span>
                        @else
                            <span class="text-success">Received</span>
                        @endif
                    </td>
                    <td>
                        @if($isSent)
                            To: {{ $transaction->toSim->phone_number }}
                        @else
                            From: {{ $transaction->fromSim->phone_number }}
                        @endif
                    </td>
                    <td>RWF {{ number_format($transaction->amount, 2) }}</td>
                    <td>RWF {{ number_format($transaction->fee, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $transaction->status == 'approved' ? 'success' : ($transaction->status == 'pending' || $transaction->status == 'reversal_requested' ? 'warning' : 'danger') }}">
                            {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                        </span>
                    </td>
                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        @if($isSent && $transaction->status === 'approved')
                            <form action="{{ route('user.transactions.requestReversal', $transaction) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Request reversal for this transfer?')">
                                    Request Reversal
                                </button>
                            </form>
                        @elseif($transaction->status === 'reversal_requested')
                            <span class="text-warning">Reversal requested</span>
                        @elseif($transaction->status === 'reversed')
                            <span class="text-success">Reversed</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $transactions->links() }}
    </div>
</div>
@endsection