@extends('layout')

@section('title', 'Match - BadNet')

@section('content')
<div class="page-shell match-detail-shell">
    <div class="matches-header">
        <div>
            <p class="home-eyebrow">Match Detail</p>
            <h1>Match Details</h1>
            <p class="page-subtitle">Track the pairings, scoreline, and action controls for this fixture.</p>
        </div>
        <span class="match-status-pill">{{ ucfirst($match->status) }}</span>
    </div>

    <div class="dashboard-section match-box">
        <div class="match-header">
            <div class="player-card">
                <a href="{{ route('profile.show', $match->player1_id) }}" class="player-avatar">
                    {{ strtoupper(substr($match->player1->name, 0, 1)) }}
                </a>
                <div class="player-info">
                    <h3>{{ $match->player1->name }}</h3>
                    <p>{{ $match->player1->rank }} - {{ $match->player1->elo_rating }} ELO</p>
                </div>
            </div>

            <div class="match-center">
                <div class="match-status-badge">{{ ucfirst($match->status) }}</div>
                <div class="match-date">{{ $match->match_date->format('M d, Y h:i A') }}</div>
                @if($match->location)
                    <div class="match-location">📍 {{ $match->location }}</div>
                @endif
                @if($match->isCompleted())
                    <div class="match-score-display">
                        <span class="score">{{ $match->player1_score }}</span>
                        <span class="separator">-</span>
                        <span class="score">{{ $match->player2_score }}</span>
                    </div>
                    <div class="match-winner">🏆 {{ $match->winner?->name ?? 'TBD' }} Won</div>
                @endif
            </div>

            <div class="player-card">
                @if($match->player2)
                    <a href="{{ route('profile.show', $match->player2_id) }}" class="player-avatar">
                        {{ strtoupper(substr($match->player2->name, 0, 1)) }}
                    </a>
                    <div class="player-info">
                        <h3>{{ $match->player2->name }}</h3>
                        <p>{{ $match->player2->rank }} - {{ $match->player2->elo_rating }} ELO</p>
                    </div>
                @else
                    <div class="player-avatar">?</div>
                    <div class="player-info">
                        <h3>Waiting for player</h3>
                        <p>Open match, players can request to join.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="match-actions-grid">
            @auth
                @if($match->isOpen() && auth()->id() === $match->player1_id)
                    <div class="dashboard-section">
                        <h3>Join Requests</h3>
                        @if($match->joinRequests->where('status', 'pending')->isEmpty())
                            <p class="empty-message">No join requests yet</p>
                        @else
                            <div class="challenge-list">
                                @foreach($match->joinRequests->where('status', 'pending') as $joinRequest)
                                    <div class="challenge-card">
                                        <div class="challenge-from">Request from <strong>{{ $joinRequest->requester->name }}</strong></div>
                                        <div class="challenge-actions">
                                            <form action="{{ route('matches.requests.accept', [$match->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success">Accept</button>
                                            </form>
                                            <form action="{{ route('matches.requests.reject', [$match->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif(auth()->id() === $match->player1_id || auth()->id() === $match->player2_id)
                    <div class="dashboard-section">
                        @if($match->status === 'scheduled' && auth()->id() === $match->player1_id)
                            <form action="{{ route('matches.start', $match->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">Start Match</button>
                            </form>
                        @elseif($match->canSubmitResult() && auth()->id() === $match->player1_id)
                            <form action="{{ route('matches.submitResult', $match->id) }}" method="POST" class="match-form">
                                @csrf
                                <div class="form-row">
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
                                    <button type="submit" class="btn btn-success">Submit Result</button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endif

                @if($match->isOpen() && auth()->id() !== $match->player1_id)
                    <div class="dashboard-section">
                        <h3>Join Match</h3>
                        <form action="{{ route('matches.requestJoin', $match->id) }}" method="POST" class="bet-form">
                            @csrf
                            <p>This is an open match. Send a join request to the creator.</p>
                            <button type="submit" class="btn btn-primary">Request to Join</button>
                        </form>
                    </div>
                @elseif($match->status !== 'completed' && auth()->id() !== $match->player1_id && auth()->id() !== $match->player2_id)
                    <div class="dashboard-section">
                        <h3>Place Bet</h3>
                        <form action="{{ route('matches.placeBet', $match->id) }}" method="POST" class="bet-form">
                            @csrf
                            <div class="form-group">
                                <label>Bet on:</label>
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
                    </div>
                @endif
            @endauth
        </div>
    </div>
</div>
@endsection
