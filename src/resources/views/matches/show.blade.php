@extends('layout')

@section('title', 'Match - BadNet')

@php
    $isParticipant = auth()->id() === $match->player1_id || auth()->id() === $match->player2_id;
    $isCreator = auth()->id() === $match->player1_id;
    $pendingRequests = $match->joinRequests->where('status', 'pending');
@endphp

@section('content')
<div class="page-shell match-detail-shell match-detail-page">
    <section class="match-hero-panel">
        <div class="match-hero-copy">
            <p class="home-eyebrow">Match Detail</p>
            <h1>Match Details</h1>
            <p class="page-subtitle">Track the pairing, time, scoreline, and available actions for this fixture.</p>
        </div>

        <span class="match-status-pill">{{ ucfirst($match->status) }}</span>
    </section>

    <section class="match-scoreboard-panel">
        <div class="match-player-card">
            <a href="{{ route('profile.show', $match->player1_id) }}" class="match-avatar match-avatar-large">
                {{ strtoupper(substr($match->player1->name, 0, 1)) }}
            </a>
            <div class="match-player-info">
                <h2>{{ $match->player1->name }}</h2>
                <p>{{ $match->player1->rank }} - {{ $match->player1->elo_rating }} ELO</p>
            </div>
            @if($match->isCompleted())
                <span class="match-player-score">{{ $match->player1_score }}</span>
            @endif
        </div>

        <div class="match-center-panel">
            <span class="match-status-badge">{{ ucfirst($match->status) }}</span>
            <strong>{{ $match->match_date->format('M d, Y h:i A') }}</strong>
            <small>{{ $match->location ?? __('ui.match.court_tbd') }}</small>
            @if($match->isCompleted())
                <div class="match-winner">{{ $match->winner?->name ?? 'TBD' }} won</div>
            @else
                <div class="vs-center">VS</div>
            @endif
        </div>

        <div class="match-player-card match-player-card-right">
            @if($match->player2)
                @if($match->player2_id)
                    <a href="{{ route('profile.show', $match->player2_id) }}" class="match-avatar match-avatar-large">
                        {{ strtoupper(substr($match->player2->name, 0, 1)) }}
                    </a>
                @else
                    <span class="match-avatar match-avatar-large">{{ strtoupper(substr($match->player2->name, 0, 1)) }}</span>
                @endif
                <div class="match-player-info">
                    <h2>{{ $match->player2->name }}</h2>
                    <p>{{ $match->player2->rank }} - {{ $match->player2->elo_rating }} ELO</p>
                </div>
                @if($match->isCompleted())
                    <span class="match-player-score">{{ $match->player2_score }}</span>
                @endif
            @else
                <span class="match-avatar match-avatar-large match-avatar-waiting">?</span>
                <div class="match-player-info">
                    <h2>Waiting for player</h2>
                    <p>Open match, players can request to join.</p>
                </div>
            @endif
        </div>
    </section>

    <div class="match-detail-layout">
        <main class="match-main-column">
            @auth
                @if($match->isOpen() && $isCreator)
                    <section class="match-action-panel">
                        <div class="match-section-heading">
                            <div>
                                <p class="home-eyebrow">Open match</p>
                                <h2>Join Requests</h2>
                            </div>
                        </div>

                        @if($pendingRequests->isEmpty())
                            <p class="empty-message">No join requests yet</p>
                        @else
                            <div class="match-request-list">
                                @foreach($pendingRequests as $joinRequest)
                                    <article class="match-request-card">
                                        <div class="challenge-from">
                                            <span class="match-avatar">{{ strtoupper(substr($joinRequest->requester->name, 0, 1)) }}</span>
                                            <div>
                                                <span class="challenge-label">Request from</span>
                                                <strong>{{ $joinRequest->requester->name }}</strong>
                                            </div>
                                        </div>
                                        <div class="challenge-actions">
                                            <form action="{{ route('matches.requests.accept', [$match->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-small">Accept</button>
                                            </form>
                                            <form action="{{ route('matches.requests.reject', [$match->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-small">Reject</button>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @elseif($isParticipant)
                    <section class="match-action-panel">
                        <div class="match-section-heading">
                            <div>
                                <p class="home-eyebrow">Controls</p>
                                <h2>Match Actions</h2>
                            </div>
                        </div>

                        @if($match->status === 'scheduled' && $isCreator)
                            <form action="{{ route('matches.start', $match->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">Start Match</button>
                            </form>
                        @elseif($match->canSubmitResult() && $isCreator)
                            <form action="{{ route('matches.submitResult', $match->id) }}" method="POST" class="match-result-form">
                                @csrf
                                <div class="match-form-grid">
                                    <div class="form-group">
                                        <label>Player 1 Score</label>
                                        <input type="number" name="player1_score" min="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Player 2 Score</label>
                                        <input type="number" name="player2_score" min="0" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Winner</label>
                                        <select name="winner_id" required>
                                            <option value="">Select Winner</option>
                                            <option value="{{ $match->player1_id }}">{{ $match->player1->name }}</option>
                                            <option value="{{ $match->player2_id }}">{{ $match->player2->name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success">Submit Result</button>
                            </form>
                        @else
                            <p class="empty-message">No action is needed right now.</p>
                        @endif
                    </section>
                @endif
            @endauth
        </main>

        <aside class="match-side-column">
            @auth
                @if($match->isOpen() && !$isCreator)
                    <section class="match-action-panel">
                        <div class="match-section-heading">
                            <div>
                                <p class="home-eyebrow">Open match</p>
                                <h2>Join Match</h2>
                            </div>
                        </div>
                        <form action="{{ route('matches.requestJoin', $match->id) }}" method="POST" class="match-compact-form">
                            @csrf
                            <p>This match is open. Send a request to the creator.</p>
                            <button type="submit" class="btn btn-primary">Request to Join</button>
                        </form>
                    </section>
                @elseif($match->status !== 'completed' && !$isParticipant)
                    <section class="match-action-panel">
                        <div class="match-section-heading">
                            <div>
                                <p class="home-eyebrow">Coins</p>
                                <h2>Place Bet</h2>
                            </div>
                        </div>
                        <form action="{{ route('matches.placeBet', $match->id) }}" method="POST" class="match-compact-form">
                            @csrf
                            <div class="form-group">
                                <label>Bet on</label>
                                <select name="bet_on_user_id" required>
                                    <option value="">Select Player</option>
                                    <option value="{{ $match->player1_id }}">{{ $match->player1->name }}</option>
                                    @if($match->player2)
                                        <option value="{{ $match->player2_id }}">{{ $match->player2->name }}</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Amount (Max: {{ auth()->user()->virtual_coins }} coins)</label>
                                <input type="number" name="amount" min="10" max="{{ auth()->user()->virtual_coins }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Place Bet</button>
                        </form>
                    </section>
                @endif
            @endauth

            <section class="match-action-panel">
                <div class="match-section-heading">
                    <div>
                        <p class="home-eyebrow">Fixture</p>
                        <h2>Details</h2>
                    </div>
                </div>
                <div class="match-detail-facts">
                    <div>
                        <span>Status</span>
                        <strong>{{ ucfirst($match->status) }}</strong>
                    </div>
                    <div>
                        <span>Date</span>
                        <strong>{{ $match->match_date->format('M d, Y') }}</strong>
                    </div>
                    <div>
                        <span>Time</span>
                        <strong>{{ $match->match_date->format('h:i A') }}</strong>
                    </div>
                    <div>
                        <span>Location</span>
                        <strong>{{ $match->location ?? __('ui.match.court_tbd') }}</strong>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
