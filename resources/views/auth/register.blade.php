@extends('layouts.auth')

@section('title', 'Register | Telecom System')

@section('content')
    <div class="text-center mb-4">
        <h2 class="auth-header">Create Your Account</h2>
        <p class="text-muted">Sign up to manage your telecom services.</p>
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

    <form method="POST" action="{{ route('register.submit') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label class="form-label" for="email">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="phone">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="national_id">National ID</label>
                <input type="text" class="form-control" id="national_id" name="national_id" value="{{ old('national_id') }}" required>
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label class="form-label" for="date_of_birth">Date of Birth</label>
            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="address">Address</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}" required>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-4">Create Account</button>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted mb-0">Already have an account? <a href="{{ route('login') }}">Login here</a></p>
    </div>
@endsection
