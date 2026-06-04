@extends('layout')

@section('title', 'Login - BadNet')

@section('content')
<div class="auth-page-shell">
    <section class="auth-intro-panel">
        <p class="home-eyebrow">BadNet account</p>
        <h1>Welcome back</h1>
        <p>Sign in to schedule matches, join tournaments, follow the feed, and keep your badminton profile moving.</p>

        <div class="auth-benefit-list">
            <span>Live community feed</span>
            <span>Matchmaking and challenges</span>
            <span>Teams, tournaments, and betting desk</span>
        </div>
    </section>

    <section class="auth-card">
        <div class="auth-card-heading">
            <p class="home-eyebrow">Sign in</p>
            <h2>Login</h2>
        </div>

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                @error('password') <span class="error-text">{{ $message }}</span> @enderror
            </div>

            <div class="auth-row">
                <label class="auth-check" for="remember">
                    <input type="checkbox" id="remember" name="remember">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="auth-link">Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
    </section>
</div>
@endsection
