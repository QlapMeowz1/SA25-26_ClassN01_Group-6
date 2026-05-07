@extends('layout')

@section('title', 'Create Match - BadNet')

@section('content')
<div class="form-container">
    <h2>Create Match</h2>

    <div class="create-match-actions">
        <a href="{{ route('matches.index') }}" class="btn btn-secondary">Back to Arena</a>
        <form action="{{ route('matches.quick') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="btn btn-primary">Quick Match</button>
        </form>
    </div>
    
    <form method="POST" action="{{ route('matches.store') }}">
        @csrf

        <div class="form-group">
            <label for="player2_id">Select Opponent</label>
            <select id="player2_id" name="player2_id">
                <option value="">-- Open match, let players request to join --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" @selected(old('player2_id') == $user->id)>{{ $user->name }} ({{ $user->rank }})</option>
                @endforeach
            </select>
            @error('player2_id') <span class="error-text">{{ $message }}</span> @enderror
            <p class="form-help">Leave blank to create an open match. Everyone can request to join and you approve one player later.</p>
        </div>

        <div class="form-group">
            <label for="match_date">Match Date & Time</label>
            <input type="datetime-local" id="match_date" name="match_date" required>
            @error('match_date') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="location">Location (Optional)</label>
            <input type="text" id="location" name="location" placeholder="E.g., Community Court">
            @error('location') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Create Match</button>
        <a href="{{ route('matches.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
