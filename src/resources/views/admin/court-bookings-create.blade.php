@extends('layout')

@section('title', 'New Booking - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>New Booking</h1>
            <p class="page-subtitle">Create a court booking by scheduling a match slot.</p>
        </div>
        <a href="{{ route('admin.court-bookings') }}" class="btn btn-secondary">Back</a>
    </section>

    <section class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.court-bookings.store') }}" class="admin-record-form">
            @csrf
            <div class="admin-form-grid">
                <label>
                    <span>Date</span>
                    <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
                    @error('date') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Time</span>
                    <input type="time" name="time" value="{{ old('time', '09:00') }}" required>
                    @error('time') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Court</span>
                    <select name="court" required>
                        @foreach($courts as $court)
                            <option value="{{ $court }}" @selected(old('court') === $court)>{{ $court }}</option>
                        @endforeach
                    </select>
                    @error('court') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Status</span>
                    <select name="status" required>
                        <option value="scheduled" @selected(old('status', 'scheduled') === 'scheduled')>Scheduled</option>
                        <option value="open" @selected(old('status') === 'open')>Open</option>
                        <option value="in_progress" @selected(old('status') === 'in_progress')>In Progress</option>
                    </select>
                    @error('status') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Player 1</span>
                    <select name="player1_id" required>
                        <option value="">Select player</option>
                        @foreach($players as $player)
                            <option value="{{ $player->id }}" @selected((string) old('player1_id') === (string) $player->id)>{{ $player->name }}</option>
                        @endforeach
                    </select>
                    @error('player1_id') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Player 2</span>
                    <select name="player2_id">
                        <option value="">Open slot</option>
                        @foreach($players as $player)
                            <option value="{{ $player->id }}" @selected((string) old('player2_id') === (string) $player->id)>{{ $player->name }}</option>
                        @endforeach
                    </select>
                    @error('player2_id') <small>{{ $message }}</small> @enderror
                </label>
            </div>
            <div class="admin-form-actions">
                <button type="submit" class="btn btn-primary">Create Booking</button>
                <a href="{{ route('admin.court-bookings') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
@endsection
