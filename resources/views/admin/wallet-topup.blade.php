@extends('layouts.app')

@section('title', 'Admin Wallet')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Admin Wallet')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-1">Admin Wallet</h5>
                <p class="mb-0 text-muted">This wallet funds customer wallet top-ups.</p>
            </div>
            <div class="text-end">
                <div class="text-muted small">Current Balance</div>
                <div class="fs-4 fw-semibold">RWF {{ number_format($wallet->balance ?? 0, 2) }}</div>
            </div>
        </div>

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

        <form method="POST" action="{{ route('admin.wallet.topup.submit') }}">
            @csrf
            <div class="mb-3">
                <label for="amount" class="form-label">Amount to add</label>
                <input type="number" step="0.01" min="1" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Funds
            </button>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary ms-2">Back</a>
        </form>
    </div>
</div>
@endsection
