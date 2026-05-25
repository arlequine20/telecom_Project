@extends('layouts.app')

@section('title', ($sim->customer_id ? 'Update SIM Assignment' : 'Assign SIM Card') . ' | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', $sim->customer_id ? 'Update SIM Assignment' : 'Assign SIM Card')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-4">{{ $sim->customer_id ? 'Update assignment for' : 'Assign' }} SIM {{ $sim->sim_number }}</h5>

        <div class="mb-3">
            <p><strong>Phone:</strong> {{ $sim->phone_number }}</p>
            <p><strong>Current Status:</strong> {{ ucfirst($sim->status) }}</p>
            <p><strong>Current Customer:</strong> {{ $sim->customer->full_name ?? 'Unassigned' }}</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $sim->customer_id ? route('admin.sim-cards.assign.update', $sim) : route('admin.sim-cards.assign.submit', $sim) }}">
            @csrf
            @if($sim->customer_id)
                @method('PUT')
            @endif
            <div class="mb-3">
                <label for="customer_id" class="form-label">Select Customer</label>
                <select name="customer_id" id="customer_id" class="form-select" required>
                    <option value="">Choose a customer</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((int) old('customer_id', $sim->customer_id) === $customer->id)>{{ $customer->full_name }} - {{ $customer->email }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">{{ $sim->customer_id ? 'Update Assignment' : 'Assign SIM' }}</button>
            <a href="{{ route('admin.sim-cards') }}" class="btn btn-secondary ms-2">Back to SIM Cards</a>
        </form>
    </div>
</div>
@endsection
