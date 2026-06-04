@extends('layout')

@section('title', 'New Tournament - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>New Tournament</h1>
            <p class="page-subtitle">Set up tournament details, prize pool, and capacity.</p>
        </div>
        <a href="{{ route('admin.tournaments') }}" class="btn btn-secondary">Back</a>
    </section>

    <section class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.tournaments.store') }}" class="admin-record-form">
            @csrf
            <div class="admin-form-grid">
                <label class="is-wide">
                    <span>Name</span>
                    <input name="name" value="{{ old('name') }}" required>
                    @error('name') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Start Date</span>
                    <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required>
                    @error('start_date') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>End Date</span>
                    <input type="date" name="end_date" value="{{ old('end_date', now()->addDays(7)->toDateString()) }}" required>
                    @error('end_date') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Max Participants</span>
                    <input type="number" name="max_participants" min="2" value="{{ old('max_participants', 64) }}" required>
                    @error('max_participants') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Prize Pool</span>
                    <input type="number" name="prize_pool" min="0" step="1000" value="{{ old('prize_pool', 0) }}">
                    @error('prize_pool') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Status</span>
                    <select name="status" required>
                        <option value="upcoming" @selected(old('status', 'upcoming') === 'upcoming')>Upcoming</option>
                        <option value="in_progress" @selected(old('status') === 'in_progress')>Ongoing</option>
                        <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                    </select>
                    @error('status') <small>{{ $message }}</small> @enderror
                </label>
                <label class="is-wide">
                    <span>Description / Venue</span>
                    <textarea name="description" rows="4">{{ old('description') }}</textarea>
                    @error('description') <small>{{ $message }}</small> @enderror
                </label>
            </div>
            <div class="admin-form-actions">
                <button type="submit" class="btn btn-primary">Create Tournament</button>
                <a href="{{ route('admin.tournaments') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
@endsection
