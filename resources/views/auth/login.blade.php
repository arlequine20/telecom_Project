@extends('layouts.auth')

@section('title', 'Login | Telecom System')

@section('hero')
    <div class="text-start h-100 d-flex flex-column justify-content-center">
        <div class="d-inline-flex align-items-center justify-content-center rounded-4 p-4 mb-4 shadow-sm" style="background: rgba(255, 107, 0, 0.14); width: 96px; height: 96px;">
            <i class="fas fa-mobile-screen-button fa-2x"></i>
        </div>
        <h1 class="display-5 fw-bold mb-3">Welcome to Telecom System</h1>
        <p class="lead text-muted mb-4">Modern telecom payments with QR scanning, SIM wallet transfers, and instant transaction tracking.</p>
        <div class="row g-3">
            <div class="col-6">
                <div class="feature-pill">
                    <i class="fas fa-qrcode"></i>
                    QR Pay
                </div>
            </div>
            <div class="col-6">
                <div class="feature-pill">
                    <i class="fas fa-wallet"></i>
                    Wallets
                </div>
            </div>
            <div class="col-6">
                <div class="feature-pill">
                    <i class="fas fa-history"></i>
                    History
                </div>
            </div>
            <div class="col-6">
                <div class="feature-pill">
                    <i class="fas fa-shield-alt"></i>
                    Secure
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="text-center mb-4">
        <h2 class="auth-header">Login</h2>
        <p class="text-muted mb-0">Sign in to your account and start sending money.</p>
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
