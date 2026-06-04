@extends('layout')

@section('title', 'Add Player - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Add Player</h1>
            <p class="page-subtitle">Create a player profile from the operations panel.</p>
        </div>
        <a href="{{ route('admin.players') }}" class="btn btn-secondary">Back</a>
    </section>

    <section class="admin-panel admin-form-panel">
        <form method="POST" action="{{ route('admin.players.store') }}" class="admin-record-form">
            @csrf
            <div class="admin-form-grid">
                <label>
                    <span>Name</span>
                    <input name="name" value="{{ old('name') }}" required>
                    @error('name') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Phone</span>
                    <input name="phone" value="{{ old('phone') }}">
                    @error('phone') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Rank</span>
                    <select name="rank" required>
                        @foreach(['Beginner', 'Intermediate', 'Advanced', 'Professional'] as $rank)
                            <option value="{{ $rank }}" @selected(old('rank', 'Beginner') === $rank)>{{ $rank }}</option>
                        @endforeach
                    </select>
                    @error('rank') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>ELO Rating</span>
                    <input type="number" name="elo_rating" min="0" value="{{ old('elo_rating', 1200) }}">
                    @error('elo_rating') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Wins</span>
                    <input type="number" name="wins" min="0" value="{{ old('wins', 0) }}">
                    @error('wins') <small>{{ $message }}</small> @enderror
                </label>
                <label>
                    <span>Losses</span>
                    <input type="number" name="losses" min="0" value="{{ old('losses', 0) }}">
                    @error('losses') <small>{{ $message }}</small> @enderror
                </label>
            </div>
            <div class="admin-form-actions">
                <button type="submit" class="btn btn-primary">Save Player</button>
                <a href="{{ route('admin.players') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
</div>
@endsection
