@extends('layout')

@section('title', 'Create Match - BadNet')

@section('content')
<div class="page-shell match-create-page">
    <section class="match-hero-panel match-create-hero">
        <div class="match-hero-copy">
            <p class="home-eyebrow">Match setup</p>
            <h1>Create Match</h1>
            <p class="page-subtitle">Schedule a fixture with a player or leave it open for join requests.</p>
        </div>

        <div class="matches-header-actions">
            <a href="{{ route('matches.index') }}" class="btn btn-secondary">Back to arena</a>
            <form action="{{ route('matches.quick') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary">Quick Match</button>
            </form>
        </div>
    </section>

    <div class="match-create-layout">
        <section class="match-form-panel">
            @if ($errors->any())
                <div class="match-form-errors">
                    <strong>Match could not be created</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('matches.store') }}" class="match-create-form">
                @csrf

                <div class="form-group">
                    <label for="player2_id">Opponent</label>
                    <select id="player2_id" name="player2_id">
                        <option value="">Open match, let players request to join</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('player2_id', request('player2_id')) == $user->id)>
                                {{ $user->name }} ({{ $user->rank }} - {{ $user->elo_rating }} ELO)
                            </option>
                        @endforeach
                    </select>
                    <p class="form-help">Leave blank if you want a public match queue.</p>
                    @error('player2_id') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="match-form-grid">
                    <div class="form-group">
                        <label for="match_date">Match Date & Time</label>
                        <input
                            type="datetime-local"
                            id="match_date"
                            name="match_date"
                            value="{{ old('match_date', $defaultMatchDate ?? now()->addHour()->format('Y-m-d\TH:i')) }}"
                            min="{{ $minMatchDate ?? now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                            required>
                        @error('match_date') <span class="error-text">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}" placeholder="Community Court">
                        @error('location') <span class="error-text">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="match-form-actions">
                    <button type="submit" class="btn btn-primary">Create Match</button>
                    <a href="{{ route('matches.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>

        <aside class="match-create-sidebar">
            <section class="match-section">
                <div class="match-section-heading">
                    <div>
                        <p class="home-eyebrow">Players</p>
                        <h2>Suggested opponents</h2>
                    </div>
                </div>

                <div class="match-opponent-list">
                    @foreach($users->getCollection()->take(6) as $user)
                        <a href="{{ route('matches.create') }}?player2_id={{ $user->id }}" class="match-opponent-row">
                            <span class="match-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <span class="match-opponent-body">
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->rank }} - {{ $user->elo_rating }} ELO</small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <div class="match-pagination">
        {{ $users->links() }}
    </div>
</div>
@endsection
