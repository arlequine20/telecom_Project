@extends('layouts.app')

@section('title', 'Pending Approvals')

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
    <a class="nav-link active" href="{{ route('admin.pending') }}">
        <i class="fas fa-clock"></i> Pending Approvals
    </a>
    <a class="nav-link" href="{{ route('admin.history') }}">
        <i class="fas fa-history"></i> Transaction History
    </a>
@endsection

@section('header-title', 'Pending Transaction Approvals')
@section('user-name', 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        @if($transactions->count() > 0)
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Total</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_reference }}</td>
                        <td>{{ $transaction->fromSim->phone_number }}</td>
                        <td>{{ $transaction->toSim->phone_number }}</td>
                        <td>RWF {{ number_format($transaction->amount, 2) }}</td>
                        <td>RWF {{ number_format($transaction->fee, 2) }}</td>
                        <td>RWF {{ number_format($transaction->amount + $transaction->fee, 2) }}</td>
                        <td>{{ $transaction->description ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $transaction->status === 'pending' ? 'warning' : ($transaction->status === 'reversal_requested' ? 'info' : 'secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                            </span>
                        </td>
                        <td>
                            @if($transaction->status === 'pending')
                                <form action="{{ route('admin.transactions.approve', $transaction) }}" 
                                      method="POST" style="display: inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" 
                                            onclick="return confirm('Approve this transfer?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form action="{{ route('admin.transactions.cancel', $transaction) }}" 
                                      method="POST" style="display: inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Cancel this transfer?')">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </form>
                            @elseif($transaction->status === 'reversal_requested')
                                <form action="{{ route('admin.transactions.approve', $transaction) }}" 
                                      method="POST" style="display: inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" 
                                            onclick="return confirm('Approve reversal for this transfer?')">
                                        <i class="fas fa-undo"></i> Approve Reversal
                                    </button>
                                </form>
                                <form action="{{ route('admin.transactions.cancel', $transaction) }}" 
                                      method="POST" style="display: inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Deny this reversal request?')">
                                        <i class="fas fa-times"></i> Deny Reversal
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $transactions->links() }}
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>No pending transactions</h5>
                <p class="text-muted">All transfers have been processed</p>
            </div>
        @endif
    </div>
</div>

<script>
// For API calls
document.querySelectorAll('form[action*="approve"], form[action*="cancel"]').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const url = form.action;
        const method = form.method;
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        if(result.success) {
            location.reload();
        } else {
            alert(result.message);
        }
    });
});
</script>
@endsection