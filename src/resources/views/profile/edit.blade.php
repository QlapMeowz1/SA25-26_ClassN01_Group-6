@extends('layout')

@section('title', 'Edit Profile - BadNet')

@section('content')
<div class="form-container">
    <h2>Edit Profile</h2>
    
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" required>
            @error('name') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" value="{{ auth()->user()->phone }}">
            @error('phone') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="5" maxlength="500">{{ auth()->user()->bio }}</textarea>
            @error('bio') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="avatar">Profile Picture</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
            @error('avatar') <span class="error-text">{{ $message }}</span> @enderror
            @if(auth()->user()->avatar)
                <p class="helper-text">Current: <img src="{{ asset('avatars/' . auth()->user()->avatar) }}" alt="Avatar" style="max-width: 50px; border-radius: 50%;"></p>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="{{ route('profile.show', auth()->id()) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
