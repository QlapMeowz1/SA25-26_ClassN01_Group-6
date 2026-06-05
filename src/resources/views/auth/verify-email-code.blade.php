@extends('layout')

@section('title', 'Verify Email - BadNet')

@section('content')
<div class="auth-page-shell">
    <section class="auth-intro-panel">
        <p class="home-eyebrow">Email verification</p>
        <h1>Check your inbox</h1>
        <p>Enter the 6-digit code sent to {{ auth()->user()->email }} to activate your BadNet account.</p>

        <div class="auth-benefit-list">
            <span>Code expires in 10 minutes</span>
            <span>5 attempts per code</span>
            <span>Protects player accounts</span>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-heading">
            <p class="home-eyebrow">Verification code</p>
            <h2>Confirm Email</h2>
        </div>

        <form method="POST" action="{{ route('verification.verify') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="code">6-digit code</label>
                <input type="text" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="one-time-code" placeholder="123456">
                @error('code') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-block">Verify Email</button>
        </form>

        <div class="auth-secondary-actions">
            <form method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn btn-secondary btn-block">Send a new code</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="auth-link-button">Use another account</button>
            </form>
        </div>
    </section>
</div>
@endsection
