@extends('layout')

@section('title', 'Register - BadNet')

@section('content')
<div class="auth-page-shell">
    <section class="auth-intro-panel">
        <p class="home-eyebrow">Join BadNet</p>
        <h1>Create your player card</h1>
        <p>Build your badminton identity, find players near your level, and start tracking your match journey from day one.</p>

        <div class="auth-benefit-list">
            <span>5,000 starter coins</span>
            <span>Player profile and ELO</span>
            <span>Access teams and tournaments</span>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-heading">
            <p class="home-eyebrow">New account</p>
            <h2>Register</h2>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
                @error('name') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="phone">Phone <span class="field-optional">Optional</span></label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
            </div>

            <div class="auth-form-grid">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    @error('password') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="auth-link">Already have an account? <a href="{{ route('login') }}">Login here</a></p>
    </section>
</div>
@endsection
