@extends('layouts.app')

@section('title', 'Buy Data for SIM')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Buy Data for SIM')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h5 class="mb-4">Buy Data for SIM: {{ $sim->sim_number }}</h5>
        <p><strong>Phone Number:</strong> {{ $sim->phone_number }}</p>
        <p><strong>Current Data Balance:</strong> {{ number_format($sim->data_balance ?? 0, 2) }} MB</p>
        <p><strong>Current SIM Balance:</strong> RWF {{ number_format($sim->balance, 2) }}</p>

        <form method="POST" action="{{ route('admin.sim-cards.buy-data.submit', $sim) }}">
            @csrf

            <div class="mb-3">
                <label for="amount" class="form-label">Data Amount (MB)</label>
                <input type="number" step="0.01" min="1" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                @error('amount')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-success">Buy Data</button>
            <a href="{{ route('admin.sim-cards') }}" class="btn btn-secondary ms-2">Back to SIM Cards</a>
        </form>
    </div>
</div>
@endsection
