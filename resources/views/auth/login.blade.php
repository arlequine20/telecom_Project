@extends('layouts.auth')

@section('title', 'Login | Telecom System')

@section('content')
    <div class="text-center mb-4">
        <h2 class="auth-header">Telecom System</h2>
        <p class="text-muted">Login to your account and access your dashboard.</p>
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

    <form method="POST" action="{{ route('login.submit') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('password.request') }}">Forgot password?</a>
    </div>

    <div class="text-center mt-4">
        <p class="text-muted mb-0">Don't have an account? <a href="{{ route('register.show') }}">Register now</a></p>
    </div>
@endsection
