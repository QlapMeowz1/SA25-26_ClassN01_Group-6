@extends('layout')

@section('title', 'Forgot Password - BadNet')

@section('content')
<div class="auth-page-shell">
    <section class="auth-intro-panel">
        <p class="home-eyebrow">Account recovery</p>
        <h1>Reset your password</h1>
        <p>Enter the email linked to your BadNet account. We will send a secure, time-limited reset link.</p>

        <div class="auth-benefit-list">
            <span>Reset link expires after 15 minutes</span>
            <span>Your current password remains hidden</span>
            <span>The link can only be used once</span>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-heading">
            <p class="home-eyebrow">Password help</p>
            <h2>Send Reset Link</h2>
        </div>

        @if(session('status'))
            <div class="auth-status-message">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email">Account Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="email">
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block">Email Reset Link</button>
        </form>

        <p class="auth-link">
            @auth
                <a href="{{ route('profile.edit') }}">Back to Settings</a>
            @else
                Remembered your password? <a href="{{ route('login') }}">Back to Login</a>
            @endauth
        </p>
    </section>
</div>
@endsection
