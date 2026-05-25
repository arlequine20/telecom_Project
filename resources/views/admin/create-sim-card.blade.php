@extends('layouts.app')

@section('title', 'Add SIM Card | Telecom')

@section('sidebar')
    @include('admin.partials.sidebar')
@endsection

@section('header-title', 'Add SIM Card')
@section('user-name', auth()->user()->name ?? 'Admin')

@section('content')
<div class="card">
    <div class="card-body">
        <h5 class="mb-4">Add New SIM Card</h5>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.sim-cards.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="sim_number" class="form-label">SIM Number</label>
                    <input type="text" class="form-control" id="sim_number" name="sim_number" value="{{ old('sim_number') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-12">
                    <label for="tariff_plan" class="form-label">Tariff Plan</label>
                    <select name="tariff_plan" id="tariff_plan" class="form-select" required>
                        <option value="">Choose plan</option>
                        <option value="prepaid" @selected(old('tariff_plan') == 'prepaid')>Prepaid</option>
                        <option value="postpaid" @selected(old('tariff_plan') == 'postpaid')>Postpaid</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="alert alert-info mt-3 mb-0">
                        New SIM cards are issued with zero balance. Customers will need to recharge their SIM through their wallet after assignment.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Create SIM Card</button>
            <a href="{{ route('admin.sim-cards') }}" class="btn btn-secondary mt-4 ms-2">Back</a>
        </form>
    </div>
</div>
@endsection
