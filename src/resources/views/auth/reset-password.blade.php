@extends('layout')

@section('title', 'Reset Password - BadNet')

@section('content')
<div class="auth-page-shell">
    <section class="auth-intro-panel">
        <p class="home-eyebrow">Secure recovery</p>
        <h1>Choose a new password</h1>
        <p>Use at least eight characters and avoid reusing a password from another account.</p>

        <div class="auth-benefit-list">
            <span>Minimum 8 characters</span>
            <span>Use a unique password</span>
            <span>You will sign in again after reset</span>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-heading">
            <p class="home-eyebrow">New credentials</p>
            <h2>Reset Password</h2>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">Account Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email">
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required autofocus autocomplete="new-password">
                @error('password') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Update Password</button>
        </form>
    </section>
</div>
@endsection
