@extends('layouts.app')

@section('title', 'Pending Approvals')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Pending Transaction Approvals')
@section('user-name', 'Admin')

@section('content')
<style>
    .pending-table td {
        vertical-align: middle;
    }

    .pending-table th,
    .pending-table td {
        white-space: nowrap;
    }

    .pending-table .description-cell {
        min-width: 180px;
        white-space: normal;
    }

    .pending-table .actions-cell {
        min-width: 210px;
    }

    .pending-actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        max-width: 100%;
    }

    .pending-actions form {
        margin: 0;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 86px;
        min-height: 34px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        border: 1px solid transparent;
        transition: all 0.18s ease;
    }

    .action-btn-approve {
        color: #067a63;
        background: rgba(0, 184, 148, 0.12);
        border-color: rgba(0, 184, 148, 0.28);
    }

    .action-btn-approve:hover,
    .action-btn-approve:focus {
        color: #fff;
        background: var(--success);
        border-color: var(--success);
        box-shadow: 0 6px 14px rgba(0, 184, 148, 0.2);
    }

    .action-btn-cancel {
        color: #b42318;
        background: rgba(255, 59, 48, 0.1);
        border-color: rgba(255, 59, 48, 0.24);
    }

    .action-btn-cancel:hover,
    .action-btn-cancel:focus {
        color: #fff;
        background: var(--danger);
        border-color: var(--danger);
        box-shadow: 0 6px 14px rgba(255, 59, 48, 0.18);
    }

    .action-btn-reversal {
        min-width: 128px;
    }

    .pending-actions .action-btn {
        width: auto;
    }
</style>

<div class="card">
    <div class="card-body">
        @if($transactions->count() > 0)
            <div class="table-responsive">
            <table class="table table-custom pending-table">
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
                        <td class="description-cell">{{ $transaction->description ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $transaction->status === 'pending' ? 'warning' : ($transaction->status === 'reversal_requested' ? 'info' : 'secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                            </span>
                        </td>
                        <td class="actions-cell">
                            @if($transaction->status === 'pending')
                                <div class="pending-actions">
                                    <form action="{{ route('admin.transactions.approve', $transaction) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn action-btn action-btn-approve"
                                                onclick="return confirm('Approve this transfer?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.transactions.cancel', $transaction) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn action-btn action-btn-cancel"
                                                onclick="return confirm('Cancel this transfer?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                </div>
                            @elseif($transaction->status === 'reversal_requested')
                                <div class="pending-actions">
                                    <form action="{{ route('admin.transactions.approve', $transaction) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn action-btn action-btn-approve action-btn-reversal"
                                                onclick="return confirm('Approve reversal for this transfer?')">
                                            <i class="fas fa-undo"></i> Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.transactions.cancel', $transaction) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn action-btn action-btn-cancel action-btn-reversal"
                                                onclick="return confirm('Deny this reversal request?')">
                                            <i class="fas fa-ban"></i> Deny
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
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
