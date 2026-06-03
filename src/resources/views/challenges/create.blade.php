@extends('layout')

@section('title', 'Send Challenge - BadNet')

@section('content')
<div class="page-shell challenge-create-page">
    <section class="challenge-hero-panel challenge-create-hero">
        <div class="challenge-hero-copy">
            <p class="home-eyebrow">{{ __('ui.challenge.send') }}</p>
            <h1>Send Challenge</h1>
            <p class="page-subtitle">Invite a specific player or publish an open call for the community.</p>
        </div>

        <div class="challenge-hero-actions">
            <a href="{{ route('challenges.index') }}" class="btn btn-secondary">Back to Arena</a>
            <form action="{{ route('challenges.quick') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary">Quick Challenge</button>
            </form>
        </div>
    </section>

    <div class="challenge-create-layout">
        <section class="challenge-form-panel">
            <form method="POST" action="{{ route('challenges.store') }}" class="challenge-form">
                @csrf

                <div class="form-group">
                    <label for="opponent_id">Opponent</label>
                    <select id="opponent_id" name="opponent_id">
                        <option value="">Open challenge, let players request to join</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('opponent_id', $selectedOpponentId) == $user->id)>
                                {{ $user->name }} ({{ $user->rank }} - {{ $user->elo_rating }} ELO)
                            </option>
                        @endforeach
                    </select>
                    @error('opponent_id') <span class="error-text">{{ $message }}</span> @enderror
                    <p class="form-help">Leave this blank to create an open challenge and review join requests later.</p>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="6" maxlength="500" placeholder="Looking for intermediate players for a friendly 3-set match this weekend at Central Court">{{ old('message') }}</textarea>
                    @error('message') <span class="error-text">{{ $message }}</span> @enderror
                    <p class="form-help">Keep it short: level, format, location, and preferred time are enough.</p>
                </div>

                <div class="challenge-form-actions">
                    <button type="submit" class="btn btn-primary">Send Challenge</button>
                    <a href="{{ route('challenges.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>

        <aside class="challenge-create-sidebar">
            <section class="challenge-section">
                <div class="challenge-section-heading">
                    <div>
                        <p class="home-eyebrow">Players</p>
                        <h2>Available Opponents</h2>
                    </div>
                </div>

                <div class="challenge-opponent-list">
                    @foreach($users->getCollection()->take(6) as $user)
                        <a href="{{ route('challenges.create') }}?opponent_id={{ $user->id }}" class="challenge-opponent-row">
                            <span class="challenge-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <span class="challenge-opponent-body">
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->rank }} - {{ $user->elo_rating }} ELO</small>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <div class="challenge-pagination">
        {{ $users->links() }}
    </div>
</div>
@endsection
