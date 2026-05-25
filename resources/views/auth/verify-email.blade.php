@extends('layouts.auth')

@section('title', 'Verify Email | Telecom System')

@section('content')
    <div class="text-center mb-4">
        <h2 class="auth-header">Verify Your Email</h2>
        <p class="text-muted mb-0">We sent a verification link to {{ auth()->user()->email }}.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <p class="text-muted">
        Open that email and click the verification link to continue using your account.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-primary w-100">Resend Verification Email</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">Logout</button>
    </form>
@endsection
