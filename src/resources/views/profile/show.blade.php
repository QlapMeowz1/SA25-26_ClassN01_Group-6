@extends('layout')

@section('title', $user->name . ' - BadNet')

@section('content')
@php
    $avatarInitial = strtoupper(substr($user->name, 0, 1));
    $winRate = method_exists($user, 'getWinRate') ? $user->getWinRate() : 0;
    $chartAngle = max(0, min(100, $betStats['win_rate'])) * 3.6;
@endphp

<div class="page-shell profile-pro-shell">
    <section class="profile-player-card">
        <div class="profile-player-cover"></div>

        <div class="profile-player-main">
            <div class="profile-avatar-stack">
                <div class="profile-avatar-xl">
                    @if($user->avatar)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    @else
                        <span>{{ $avatarInitial }}</span>
                    @endif
                </div>
                <div class="profile-xp">
                    <div class="profile-xp-top">
                        <span>Level {{ $xp['level'] }}</span>
                        <span>{{ $xp['current'] }}/{{ $xp['target'] }} XP</span>
                    </div>
                    <div class="profile-xp-track">
                        <span style="width: {{ $xp['percent'] }}%"></span>
                    </div>
                </div>
            </div>

            <div class="profile-player-info">
                <p class="home-eyebrow">Player Card</p>
                <h1>{{ $user->name }}</h1>
                <p class="profile-skill-line">
                    Handedness: {{ $sportProfile['handedness'] }} | Playing Style: {{ $sportProfile['playing_style'] }} | Skill Level: {{ $sportProfile['skill_level'] }} - {{ $sportProfile['form'] }}
                </p>
                @if($user->bio)
                    <p class="profile-bio">{{ $user->bio }}</p>
                @else
                    <p class="profile-bio muted">This player has not written a profile bio yet.</p>
                @endif

                <div class="profile-quick-stats">
                    <span><strong>{{ number_format($user->elo_rating ?? 0) }}</strong> ELO</span>
                    <span><strong>{{ $user->wins ?? 0 }}</strong> Wins</span>
                    <span><strong>{{ $winRate }}%</strong> Win Rate</span>
                    <span><strong>{{ $user->posts_count }}</strong> Posts</span>
                    <span><strong>{{ $user->teams_count }}</strong> Teams</span>
                    <span><strong>{{ $user->tournaments_count }}</strong> Tournaments</span>
                </div>

                <div class="profile-cta-row">
                    @if($isOwner)
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
                        <a href="{{ route('bets.index') }}" class="btn btn-secondary">Recharge / Request Points</a>
                    @else
                        <a href="{{ route('challenges.create', ['opponent_id' => $user->id]) }}" class="profile-btn-challenge">Challenge</a>
                        <a href="{{ route('matches.create', ['player2_id' => $user->id]) }}" class="btn btn-primary">Invite to play</a>
                        <a href="mailto:{{ $user->email }}" class="btn btn-secondary">Message</a>
                    @endif
                </div>
            </div>

            <aside class="profile-wallet-module {{ $walletTier['class'] }}">
                <span class="profile-wallet-label">Virtual Points</span>
                <strong>{{ number_format($user->virtual_coins ?? 0) }}</strong>
                <span class="profile-tier-badge">{{ $walletTier['label'] }}</span>
                @if($walletTier['next'])
                    <small>{{ number_format($walletTier['next']) }} points to next tier</small>
                @else
                    <small>Top wallet tier unlocked</small>
                @endif
            </aside>
        </div>
    </section>

    <section class="profile-tab-shell">
        <div class="profile-tabs" role="tablist" aria-label="Profile sections">
            <button type="button" class="profile-tab-button is-active" data-profile-tab-target="timeline">Timeline</button>
            <button type="button" class="profile-tab-button" data-profile-tab-target="betting">Betting History</button>
            <button type="button" class="profile-tab-button" data-profile-tab-target="real-matches">Real Match</button>
            <button type="button" class="profile-tab-button" data-profile-tab-target="achievements">Achievements</button>
        </div>

        <div class="profile-tab-panel is-active" data-profile-tab-panel="timeline">
            <div class="profile-grid-2">
                <section class="dashboard-section">
                    <div class="feed-heading">
                        <div>
                            <p class="home-eyebrow">Timeline</p>
                            <h2>Posts and highlights</h2>
                        </div>
                    </div>

                    @if($posts->isEmpty())
                        <div class="profile-empty-state">
                            <div class="profile-empty-icon">RKT</div>
                            <h3>No posts yet</h3>
                            <p>Share a match result, smash video, or court check-in to start building this timeline.</p>
                        </div>
                    @else
                        <div class="post-list">
                            @foreach($posts as $post)
                                @include('partials.post_card', ['post' => $post, 'showCommentPreview' => false])
                            @endforeach
                        </div>
                        {{ $posts->links() }}
                    @endif
                </section>

                <aside class="dashboard-section">
                    <div class="feed-heading">
                        <div>
                            <p class="home-eyebrow">Match History</p>
                            <h2>Confirmed matches</h2>
                        </div>
                    </div>

                    @if($matches->isEmpty())
                        <div class="profile-empty-state profile-empty-state-small">
                            <div class="profile-empty-icon">VS</div>
                            <h3>No matches recorded</h3>
                            <p>Challenge this player or set up a manual match.</p>
                        </div>
                    @else
                        <div class="profile-match-list">
                            @foreach($matches as $match)
                                @php
                                    $opponent = $match->player1_id === $user->id ? $match->player2 : $match->player1;
                                    $result = $match->winner_id === $user->id ? 'Win' : (($match->status === 'completed' && $match->winner_id) ? 'Loss' : ucfirst(str_replace('_', ' ', $match->status)));
                                    $resultClass = $result === 'Win' ? 'win' : ($result === 'Loss' ? 'loss' : 'pending');
                                @endphp
                                <article class="profile-match-card">
                                    <div>
                                        <strong>{{ $opponent->name ?? 'Waiting for player' }}</strong>
                                        <span>{{ optional($match->match_date)->format('M d, Y H:i') ?? 'Date TBD' }}</span>
                                        <span>{{ $match->location ?: 'Court TBD' }}</span>
                                    </div>
                                    <span class="profile-result-badge {{ $resultClass }}">{{ $result }}</span>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </aside>
            </div>
        </div>

        <div id="betting" class="profile-tab-panel" data-profile-tab-panel="betting">
            <div class="profile-betting-layout">
                <section class="dashboard-section profile-betting-summary">
                    <div class="feed-heading">
                        <div>
                            <p class="home-eyebrow">Predictions</p>
                            <h2>Betting performance</h2>
                        </div>
                        <a href="{{ route('bets.index') }}" class="btn btn-secondary btn-small">Open Betting Table</a>
                    </div>

                    <div class="profile-bet-stats">
                        <div class="profile-pie" style="--profile-chart-angle: {{ $chartAngle }}deg">
                            <span>{{ $betStats['win_rate'] }}%</span>
                        </div>
                        <div class="profile-bet-metrics">
                            <span><strong>{{ $betStats['total'] }}</strong> Predictions</span>
                            <span><strong>{{ $betStats['won'] }}</strong> Won</span>
                            <span><strong>{{ $betStats['lost'] }}</strong> Lost</span>
                            <span><strong>{{ number_format($betStats['staked']) }}</strong> Staked</span>
                            <span><strong>{{ number_format($betStats['payout']) }}</strong> Payout</span>
                        </div>
                    </div>

                    <div class="profile-progress-pair">
                        <div>
                            <span>Won</span>
                            <strong>{{ $betStats['win_rate'] }}%</strong>
                        </div>
                        <div class="profile-progress-track">
                            <span style="width: {{ $betStats['win_rate'] }}%"></span>
                        </div>
                    </div>
                    <div class="profile-progress-pair loss">
                        <div>
                            <span>Lost</span>
                            <strong>{{ $betStats['loss_rate'] }}%</strong>
                        </div>
                        <div class="profile-progress-track">
                            <span style="width: {{ $betStats['loss_rate'] }}%"></span>
                        </div>
                    </div>

                    <div class="profile-market-block">
                        <div class="profile-mini-heading">
                            <span>Database Markets</span>
                            <strong>{{ $playerBetMarkets->count() }} matches</strong>
                        </div>

                        @if($playerBetMarkets->isEmpty())
                            <div class="profile-empty-state profile-empty-state-small">
                                <div class="profile-empty-icon">ODDS</div>
                                <h3>No market data</h3>
                                <p>No one has placed bets on this player's matches yet.</p>
                            </div>
                        @else
                            <div class="profile-market-list">
                                @foreach($playerBetMarkets as $market)
                                    @php
                                        $match = $market['match'];
                                        $opponent = $match->player1_id === $user->id ? $match->player2 : $match->player1;
                                        $ownOdds = $match->player1_id === $user->id ? $match->player1_odds : $match->player2_odds;
                                    @endphp
                                    <article class="profile-market-row">
                                        <div>
                                            <strong>{{ $user->name }} vs {{ $opponent->name ?? 'TBD' }}</strong>
                                            <span>{{ ucfirst(str_replace('_', ' ', $match->status)) }} · {{ optional($match->match_date)->format('M d, H:i') ?? 'Time TBD' }}</span>
                                        </div>
                                        <div class="profile-market-meter">
                                            <span style="width: {{ $market['pool_share'] }}%"></span>
                                        </div>
                                        <div class="profile-market-meta">
                                            <span>{{ number_format($market['player_pool']) }} / {{ number_format($market['total_pool']) }} pts</span>
                                            <span>{{ $market['player_tickets'] }}/{{ $market['total_tickets'] }} tickets · {{ $ownOdds ? number_format($ownOdds, 2) . 'x' : 'auto odds' }}</span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                <section class="dashboard-section">
                    <div class="feed-heading">
                        <div>
                            <p class="home-eyebrow">Ledger</p>
                            <h2>Recent predictions</h2>
                        </div>
                    </div>

                    @if($bets->isEmpty())
                        <div class="profile-empty-state">
                            <div class="profile-empty-icon">COIN</div>
                            <h3>You have not predicted any matches yet.</h3>
                            <p>Go to the Betting Table now to receive your first 500 bonus points.</p>
                            <a href="{{ route('bets.index') }}" class="btn btn-primary">Go to Betting Table</a>
                        </div>
                    @else
                        <div class="profile-bet-list">
                            @foreach($bets as $bet)
                                @php
                                    $match = $bet->gameMatch;
                                    $matchLabel = $match
                                        ? (($match->player1->name ?? 'Player 1') . ' vs ' . ($match->player2->name ?? 'Player 2'))
                                        : 'Deleted match';
                                    $status = ucfirst(str_replace('_', ' ', $bet->status));
                                @endphp
                                <article class="profile-bet-row">
                                    <div>
                                        <strong>{{ $matchLabel }}</strong>
                                        <span>Pick: {{ $bet->betOnUser->name ?? 'Unknown' }}</span>
                                    </div>
                                    <div class="profile-bet-numbers">
                                        <span>{{ number_format($bet->amount) }} pts</span>
                                        <span>{{ number_format($bet->odds, 2) }}x</span>
                                    </div>
                                    <span class="profile-bet-status status-{{ strtolower($bet->status) }}">{{ $status }}</span>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <section class="dashboard-section profile-wallet-ledger">
                <div class="section-header">
                    <div>
                        <p class="home-eyebrow">Wallet Integrity</p>
                        <h2>Balance Ledger</h2>
                    </div>
                </div>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Time</th><th>Type</th><th>Change</th><th>Balance</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            @forelse($walletTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->created_at->format('M d, H:i') }}</td>
                                    <td>{{ \Illuminate\Support\Str::headline($transaction->type) }}</td>
                                    <td class="{{ $transaction->amount >= 0 ? 'text-positive' : 'text-negative' }}">
                                        {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount) }}
                                    </td>
                                    <td>{{ number_format($transaction->balance_after) }}</td>
                                    <td>{{ $transaction->description ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No wallet ledger entries yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="profile-tab-panel" data-profile-tab-panel="real-matches">
            <section class="dashboard-section">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Real Match</p>
                        <h2>Win/loss history</h2>
                    </div>
                    <a href="{{ route('matches.index') }}" class="btn btn-secondary btn-small">Find More Matches</a>
                </div>

                @if($matches->isEmpty())
                    <div class="profile-empty-state">
                        <div class="profile-empty-icon">VS</div>
                        <h3>No real matches recorded</h3>
                        <p>Create a match or accept a challenge to build a visible competitive history.</p>
                    </div>
                @else
                    <div class="profile-match-list profile-match-list-wide">
                        @foreach($matches as $match)
                            @php
                                $opponent = $match->player1_id === $user->id ? $match->player2 : $match->player1;
                                $result = $match->winner_id === $user->id ? 'Win' : (($match->status === 'completed' && $match->winner_id) ? 'Loss' : ucfirst(str_replace('_', ' ', $match->status)));
                                $resultClass = $result === 'Win' ? 'win' : ($result === 'Loss' ? 'loss' : 'pending');
                            @endphp
                            <article class="profile-match-card">
                                <div>
                                    <strong>{{ $user->name }} vs {{ $opponent->name ?? 'Waiting for player' }}</strong>
                                    <span>{{ optional($match->match_date)->format('M d, Y H:i') ?? 'Date TBD' }}</span>
                                    <span>{{ $match->location ?: 'Court TBD' }}</span>
                                </div>
                                <div class="profile-bet-numbers">
                                    <span>{{ $match->player1_score ?? '-' }} - {{ $match->player2_score ?? '-' }}</span>
                                </div>
                                <span class="profile-result-badge {{ $resultClass }}">{{ $result }}</span>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <div class="profile-tab-panel" data-profile-tab-panel="achievements">
            <section class="dashboard-section">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Cup Cabinet</p>
                        <h2>Achievements</h2>
                    </div>
                </div>

                <div class="profile-achievement-grid">
                    @foreach($achievements as $achievement)
                        <article
                            class="profile-achievement-card {{ $achievement['unlocked'] ? 'is-unlocked' : 'is-locked' }}"
                            data-profile-tooltip="{{ $achievement['hint'] }}"
                        >
                            <span>{{ $achievement['icon'] }}</span>
                            <strong>{{ $achievement['title'] }}</strong>
                            <small>{{ $achievement['unlocked'] ? 'Unlocked' : 'Locked' }}</small>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('[data-profile-tab-target]');
    const panels = document.querySelectorAll('[data-profile-tab-panel]');

    const activateTab = (target) => {
        buttons.forEach((item) => item.classList.toggle('is-active', item.dataset.profileTabTarget === target));
        panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.profileTabPanel === target);
        });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const target = button.dataset.profileTabTarget;
            activateTab(target);
            history.replaceState(null, '', '#' + target);
        });
    });

    const requestedTab = window.location.hash.replace('#', '');
    if (requestedTab && document.querySelector(`[data-profile-tab-panel="${requestedTab}"]`)) {
        activateTab(requestedTab);
    }
});
</script>
@endsection
