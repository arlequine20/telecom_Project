@extends('layouts.app')

@section('title', 'My Profile')

@section('sidebar')
    <a class="nav-link" href="{{ route('user.dashboard') }}">
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
    <a class="nav-link active" href="{{ route('user.profile') }}">
        <i class="fas fa-user"></i> My Profile
    </a>
@endsection

@section('header-title', 'My Profile')
@section('user-name', auth()->user()->name ?? 'User')

@section('content')
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card p-4">
            <h5>Personal Information</h5>

            @if($errors->any())
                <div class="alert alert-danger">
                    Please fix the highlighted profile fields.
                </div>
            @endif

            <form method="POST" action="{{ route('user.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $customer->last_name) }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone">Phone</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="address">Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $customer->address) }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label" for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', optional($customer->date_of_birth)->format('Y-m-d')) }}">
                    @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <p class="mb-3"><strong>National ID:</strong> {{ $customer->national_id }}</p>

                <button type="submit" class="btn btn-primary-custom">Update Profile</button>
            </form>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card p-4">
            <h5>SIM Card Summary</h5>
            <p><strong>SIM Count:</strong> {{ $simCards->count() }}</p>
            <p><strong>Total Balance:</strong> RWF {{ number_format($simCards->sum('balance'), 2) }}</p>
            <p><strong>Wallet Balance:</strong> RWF {{ number_format($wallet->balance ?? 0, 2) }}</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5>Assigned SIM Cards</h5>
        <table class="table table-custom">
            <thead>
                <tr>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                @forelse($simCards as $sim)
                <tr>
                    <td>{{ $sim->phone_number }}</td>
                    <td>RWF {{ number_format($sim->balance, 2) }}</td>
                    <td>{{ ucfirst($sim->status) }}</td>
                    <td>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=96x96&data={{ urlencode($sim->phone_number) }}" alt="QR code for {{ $sim->phone_number }}" width="72" height="72">
                        <small class="d-block text-muted">{{ $sim->phone_number }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No SIM cards assigned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
