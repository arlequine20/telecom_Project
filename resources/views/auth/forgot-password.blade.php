@extends('layouts.auth')

@section('title', 'Reset Password | Telecom System')

@section('content')
    <div class="text-center mb-4">
        <h2 class="auth-header">Forgot Password</h2>
        <p class="text-muted">Enter your email and we will send a password reset link.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
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

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
    </form>

    <div class="text-center mt-4">
        <p class="text-muted mb-0">Remembered your password? <a href="{{ route('login') }}">Login here</a></p>
    </div>
@endsection
