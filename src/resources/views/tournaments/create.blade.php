@extends('layout')

@section('title', 'Create Tournament - BadNet')

@section('content')
<div class="page-shell tournament-create-page">
    <section class="tournament-studio-hero">
        <div class="tournament-studio-copy">
            <p class="home-eyebrow">Tournament studio</p>
            <h1>Create New Tournament</h1>
            <p class="page-subtitle">Set the bracket, dates, capacity, and prize pool for the next community event.</p>
        </div>

        <div class="tournament-hero-actions">
            <a href="{{ route('tournaments.index') }}" class="btn btn-secondary">Back to circuit</a>
        </div>
    </section>

    <div class="tournament-create-layout">
        <section class="tournament-form-panel">
            <form method="POST" action="{{ route('tournaments.store') }}" class="tournament-create-form">
                @csrf

                <div class="form-group">
                    <label for="name">Tournament Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="6" maxlength="1000" placeholder="Describe format, level, prize, and schedule.">{{ old('description') }}</textarea>
                    @error('description') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="tournament-form-grid">
                    <div class="form-group">
                        <label for="start_date">Start Date & Time</label>
                        <input type="datetime-local" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                        @error('start_date') <span class="error-text">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date & Time</label>
                        <input type="datetime-local" id="end_date" name="end_date" value="{{ old('end_date') }}">
                        <p class="form-help">Optional. Leave blank to keep the window open-ended.</p>
                        @error('end_date') <span class="error-text">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="tournament-form-grid">
                    <div class="form-group">
                        <label for="max_participants">Max Participants</label>
                        <input type="number" id="max_participants" name="max_participants" value="{{ old('max_participants', 16) }}" min="4" max="100" required>
                        @error('max_participants') <span class="error-text">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="prize_pool">Prize Pool</label>
                        <input type="number" id="prize_pool" name="prize_pool" value="{{ old('prize_pool') }}" min="0" placeholder="0">
                        <p class="form-help">Optional. Use virtual coins or leave it at zero.</p>
                        @error('prize_pool') <span class="error-text">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="tournament-form-actions">
                    <button type="submit" class="btn btn-primary">Create Tournament</button>
                    <a href="{{ route('tournaments.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>

        <aside class="tournament-create-sidebar">
            <section class="tournament-section">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Planning</p>
                        <h2>Event Checklist</h2>
                    </div>
                </div>
                <div class="tournament-checklist">
                    <div>
                        <strong>Capacity</strong>
                        <p>Pick a bracket size that your court schedule can actually support.</p>
                    </div>
                    <div>
                        <strong>Prize clarity</strong>
                        <p>Use the prize pool field to make rewards visible before registration.</p>
                    </div>
                    <div>
                        <strong>Schedule</strong>
                        <p>Start and end dates help players understand the event window.</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
