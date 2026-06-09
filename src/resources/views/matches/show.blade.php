@extends('layout')

@section('title', 'Match - BadNet')

@php
    $isParticipant = auth()->id() === $match->player1_id || auth()->id() === $match->player2_id;
    $isCreator = auth()->id() === $match->player1_id;
    $canManageOdds = auth()->check() && (auth()->user()->isAdmin() || $isCreator);
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
                        @elseif($match->canSubmitResult())
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
                                <button type="submit" class="btn btn-success">Submit Result for Confirmation</button>
                            </form>
                        @elseif($match->status === 'pending_confirmation' && (int) auth()->id() !== (int) $match->result_submitted_by)
                            <div class="match-result-review">
                                <p><strong>Proposed score:</strong> {{ $match->player1_score }} - {{ $match->player2_score }}</p>
                                <p><strong>Winner:</strong> {{ $match->winner?->name }}</p>
                                <div class="challenge-actions">
                                    <form action="{{ route('matches.confirmResult', $match) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success">Confirm Result</button>
                                    </form>
                                    <form action="{{ route('matches.disputeResult', $match) }}" method="POST" class="match-compact-form">
                                        @csrf
                                        <input type="text" name="reason" maxlength="1000" placeholder="Explain what is incorrect" required>
                                        <button type="submit" class="btn btn-danger">Dispute</button>
                                    </form>
                                </div>
                            </div>
                        @elseif($match->status === 'pending_confirmation')
                            <p class="empty-message">Waiting for the opponent to confirm the submitted result.</p>
                        @elseif($match->status === 'disputed')
                            <p class="empty-message">This result is disputed and awaiting admin review.</p>
                        @else
                            <p class="empty-message">No action is needed right now.</p>
                        @endif
                    </section>
                @endif
            @endauth
        </main>

        <aside class="match-side-column">
            @auth
                @if($match->player2_id)
                    <section class="match-action-panel match-live-pool-panel" data-pool-match-id="{{ $match->id }}">
                        <div class="match-section-heading">
                            <div>
                                <p class="home-eyebrow">Pool Movement</p>
                                <h2>Live betting split</h2>
                            </div>
                            <span class="app-status app-status--{{ $poolData['market_state'] ?? 'open' }}" data-pool-state>{{ ucfirst($poolData['market_state'] ?? 'open') }}</span>
                        </div>

                        <div class="match-live-pool-teams">
                            <span>{{ $poolData['player_a'] }}</span>
                            <strong data-pool-total>{{ number_format($poolData['total_pool'] ?? 0) }} pts</strong>
                            <span>{{ $poolData['player_b'] }}</span>
                        </div>
                        <div class="pulse-pool-bar match-live-pool-bar">
                            <span data-pool-bar-a style="width: {{ $poolData['percent_a'] ?? 50 }}%"></span>
                            <i data-pool-bar-b style="width: {{ $poolData['percent_b'] ?? 50 }}%"></i>
                        </div>
                        <div class="match-live-pool-meta">
                            <span data-pool-split>{{ $poolData['percent_a'] ?? 50 }}% / {{ $poolData['percent_b'] ?? 50 }}%</span>
                            <span data-pool-bettors>{{ $poolData['bettor_count'] ?? 0 }} bettors</span>
                        </div>
                    </section>
                @endif

                @if($canManageOdds && $match->status !== 'completed' && $match->player2_id)
                    <section class="match-action-panel">
                        <div class="match-section-heading">
                            <div>
                                <p class="home-eyebrow">Betting Control</p>
                                <h2>Odds Manager</h2>
                            </div>
                        </div>
                        <form action="{{ route('matches.odds.update', $match->id) }}" method="POST" class="match-compact-form">
                            @csrf
                            @error('player1_odds') <span class="error-text">{{ $message }}</span> @enderror
                            @error('player2_odds') <span class="error-text">{{ $message }}</span> @enderror
                            <div class="match-form-grid">
                                <div class="form-group">
                                    <label>{{ $match->player1?->name ?? 'Player 1' }} Odds</label>
                                    <input type="number" name="player1_odds" min="1.01" max="50" step="0.01" value="{{ old('player1_odds', number_format($odds['player1_odds'] ?? 1, 2, '.', '')) }}" required>
                                </div>
                                <div class="form-group">
                                    <label>{{ $match->player2?->name ?? 'Player 2' }} Odds</label>
                                    <input type="number" name="player2_odds" min="1.01" max="50" step="0.01" value="{{ old('player2_odds', number_format($odds['player2_odds'] ?? 1, 2, '.', '')) }}" required>
                                </div>
                            </div>
                            <p class="empty-message">{{ ($odds['is_manual'] ?? false) ? 'Manual odds are active for new bets.' : 'System odds are active. Submit to override them.' }}</p>
                            <button type="submit" class="btn btn-primary">Update Odds</button>
                        </form>
                        <form action="{{ route('matches.odds.delete', $match->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-small">Remove Manual Odds</button>
                        </form>
                    </section>
                @endif

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
                @elseif($match->status !== 'completed' && $match->player2_id)
                    <section class="match-action-panel">
                        <div class="match-section-heading">
                            <div>
                                <p class="home-eyebrow">Coins</p>
                                <h2>Place Bet</h2>
                            </div>
                            <a href="{{ route('bets.slip', $match->id) }}" class="text-sm font-semibold text-sky-600 dark:text-sky-300">Open Bet Slip →</a>
                        </div>
                        <form action="{{ route('matches.placeBet', $match->id) }}" method="POST" class="match-compact-form" onsubmit="return confirm('Confirm this bet? Coins will be locked immediately and cannot be changed after submission.');">
                            @csrf
                            @error('bet_on_user_id') <span class="error-text">{{ $message }}</span> @enderror
                            @error('amount') <span class="error-text">{{ $message }}</span> @enderror

                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                                <div class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-300">
                                    <span>Virtual coins</span>
                                    <strong class="text-slate-900 dark:text-slate-50">{{ auth()->user()->virtual_coins }}</strong>
                                </div>
                                <div class="mt-3 grid gap-3 text-sm">
                                    @foreach($betSlip['players'] ?? [] as $player)
                                        <label class="block cursor-pointer">
                                            <input type="radio" name="bet_on_user_id" value="{{ $player['id'] }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }}>
                                            <div class="rounded-xl bg-white px-3 py-3 shadow-sm ring-1 ring-transparent transition peer-checked:ring-sky-400 dark:bg-slate-900 dark:peer-checked:ring-sky-500">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $player['name'] }}</span>
                                                    <strong class="text-slate-900 dark:text-slate-50">x{{ number_format($player['odds'], 2) }}</strong>
                                                </div>
                                                <div class="mt-2 flex flex-wrap gap-2 text-[11px] font-semibold">
                                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Confidence {{ $player['confidence'] }}%</span>
                                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ $player['risk_level'] }}</span>
                                                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">Form {{ $player['form_label'] }}</span>
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $player['community_pick_ratio'] }}% picked</span>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $betSlip['odds']['selected_probability'] ?? 50 }}% confidence</span>
                                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">Potential return = stake × odds</span>
                                </div>
                                <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">Open the dedicated Bet Slip for a fuller breakdown of confidence, form, and community pick ratio.</p>
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
