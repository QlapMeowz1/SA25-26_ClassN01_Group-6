@extends('layout')

@section('title', 'Create Team - BadNet')

@section('content')
<div class="form-container">
    <h2>Create New Team</h2>
    
    <form method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="name">Team Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" maxlength="1000">{{ old('description') }}</textarea>
            @error('description') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="logo">Team Logo</label>
            <input type="file" id="logo" name="logo" accept="image/*">
            @error('logo') <span class="error-text">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Create Team</button>
        <a href="{{ route('teams.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
