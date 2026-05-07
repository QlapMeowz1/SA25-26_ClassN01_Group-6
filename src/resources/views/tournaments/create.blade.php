@extends('layout')

@section('title', 'Create Tournament - BadNet')

@section('content')
<div class="form-container">
    <h2>Create New Tournament</h2>
    
    <form method="POST" action="{{ route('tournaments.store') }}">
        @csrf

        <div class="form-group">
            <label for="name">Tournament Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" maxlength="1000">{{ old('description') }}</textarea>
            @error('description') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="start_date">Start Date & Time</label>
            <input type="datetime-local" id="start_date" name="start_date" required>
            @error('start_date') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="end_date">End Date & Time (Optional)</label>
            <input type="datetime-local" id="end_date" name="end_date">
            @error('end_date') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="max_participants">Max Participants</label>
            <input type="number" id="max_participants" name="max_participants" value="16" min="4" max="100" required>
            @error('max_participants') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="prize_pool">Prize Pool (Optional)</label>
            <input type="number" id="prize_pool" name="prize_pool" min="0">
            @error('prize_pool') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Create Tournament</button>
        <a href="{{ route('tournaments.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
