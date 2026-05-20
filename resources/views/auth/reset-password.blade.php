@extends('layouts.auth')

@section('title', 'Reset Password | Telecom System')

@section('content')
    <div class="text-center mb-4">
        <h2 class="auth-header">Reset Your Password</h2>
        <p class="text-muted">Choose a new password for your account.</p>
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

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Reset password</button>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted mb-0">Remembered your password? <a href="{{ route('login') }}">Login here</a></p>
    </div>
@endsection
