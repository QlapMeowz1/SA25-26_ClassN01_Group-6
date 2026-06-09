@extends('layout')

@section('title', 'Edit Profile - BadNet')

@section('content')
@php
    $user = auth()->user();
    $avatarUrl = $user->avatar ? asset('avatars/' . $user->avatar) : null;
@endphp

<div class="page-shell profile-edit-page">
    <section class="profile-edit-hero">
        <div>
            <p class="home-eyebrow">Profile studio</p>
            <h1>Edit Profile</h1>
            <p class="page-subtitle">Keep your player card fresh so teammates can recognize you quickly.</p>
        </div>

        <div class="profile-edit-preview">
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="{{ $user->name }}" class="profile-edit-avatar">
            @else
                <span class="profile-edit-avatar profile-edit-avatar-fallback">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
            <div>
                <strong>{{ $user->name }}</strong>
                <span>{{ $user->rank ?? 'Beginner' }} - {{ number_format($user->elo_rating ?? 0) }} ELO</span>
            </div>
        </div>
    </section>

    <div class="profile-edit-layout">
        <main class="profile-edit-main">
            <section class="profile-edit-panel">
                <div class="profile-edit-heading">
                    <div>
                        <p class="home-eyebrow">Identity</p>
                        <h2>Player Details</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-edit-form">
                    @csrf

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name') <span class="error-text">{{ $message }}</span> @enderror
                    </div>

                    <div class="profile-edit-grid">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Optional contact number">
                            @error('phone') <span class="error-text">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="avatar">Profile Picture</label>
                            <input type="file" id="avatar" name="avatar" accept="image/*" class="profile-file-input">
                            <p class="form-help">Use a square image for the cleanest avatar crop.</p>
                            @error('avatar') <span class="error-text">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="profile-edit-grid">
                        <div class="form-group">
                            <label for="handedness">Handedness</label>
                            <select id="handedness" name="handedness">
                                <option value="">Not set</option>
                                @foreach(['Right', 'Left', 'Ambidextrous'] as $option)
                                    <option value="{{ $option }}" @selected(old('handedness', $user->handedness) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('handedness') <span class="error-text">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="playing_style">Playing Style</label>
                            <select id="playing_style" name="playing_style">
                                <option value="">Use rank-based default</option>
                                @foreach(['All-rounder', 'Attacking', 'Defensive', 'Counter attacker', 'Net player', 'Explosive net player'] as $option)
                                    <option value="{{ $option }}" @selected(old('playing_style', $user->playing_style) === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('playing_style') <span class="error-text">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio" name="bio" rows="6" maxlength="500" placeholder="Share your playing style, favorite court, or training goals.">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio') <span class="error-text">{{ $message }}</span> @enderror
                    </div>

                    @if($avatarUrl)
                        <div class="profile-current-avatar">
                            <img src="{{ $avatarUrl }}" alt="{{ $user->name }}">
                            <div>
                                <strong>Current avatar</strong>
                                <span>Choose a new file above to replace it.</span>
                            </div>
                        </div>
                    @endif

                    <div class="profile-edit-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </section>
        </main>

        <aside class="profile-edit-side">
            <section class="profile-edit-panel profile-security-panel">
                <p class="home-eyebrow">Security</p>
                <h2>Password</h2>
                <form method="POST" action="{{ route('settings.password.update') }}" class="profile-edit-form">
                    @csrf
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm Password</label>
                        <input type="password" id="new_password_confirmation" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Change Password</button>
                </form>
                <p>Forgot the current password? Send a reset link to <strong>{{ $user->email }}</strong>. It expires after 15 minutes.</p>
                <a href="{{ route('password.request', ['email' => $user->email]) }}" class="btn btn-secondary btn-block">Reset Password</a>
            </section>

            <section class="profile-edit-panel profile-security-panel">
                <p class="home-eyebrow">Sessions</p>
                <h2>Login Activity</h2>
                <div class="profile-login-list">
                    @forelse($loginActivities as $activity)
                        <div>
                            <strong>{{ $activity->successful ? 'Successful login' : 'Failed attempt' }}</strong>
                            <span>{{ $activity->ip_address ?: 'Unknown IP' }} · {{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                    @empty
                        <p>No login history yet.</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('settings.sessions.logout-others') }}" class="profile-edit-form">
                    @csrf
                    <div class="form-group">
                        <label for="session_password">Current Password</label>
                        <input type="password" id="session_password" name="current_password" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-danger btn-block">Sign Out Other Devices</button>
                </form>
            </section>

            <section class="profile-edit-panel">
                <p class="home-eyebrow">Checklist</p>
                <h2>Profile Polish</h2>
                <div class="profile-edit-tips">
                    <div>
                        <strong>Readable name</strong>
                        <span>Use the name other players will recognize in match cards.</span>
                    </div>
                    <div>
                        <strong>Clear avatar</strong>
                        <span>A centered square image keeps nav, cards, and comments consistent.</span>
                    </div>
                    <div>
                        <strong>Short bio</strong>
                        <span>Focus on level, location, and what kind of matches you enjoy.</span>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
