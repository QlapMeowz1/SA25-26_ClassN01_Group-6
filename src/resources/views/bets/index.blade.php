@extends('layout')

@section('title', 'Your Bets - BadNet')

@section('content')
@php
    $net = ($stats['payout'] ?? 0) - ($stats['wagered'] ?? 0);
    $settled = ($stats['won'] ?? 0) + ($stats['lost'] ?? 0);
    $winRate = $settled > 0 ? round((($stats['won'] ?? 0) / $settled) * 100) : 0;
    $biggestWin = $history->where('status', 'won')->max('payout') ?? 0;
    $roi = ($stats['wagered'] ?? 0) > 0 ? round(($net / max(1, $stats['wagered'])) * 100) : 0;
    $statusTabs = [
        'all' => ['label' => 'All', 'count' => $stats['total'] ?? 0],
        'pending' => ['label' => 'Pending', 'count' => $stats['pending'] ?? 0],
        'won' => ['label' => 'Won', 'count' => $stats['won'] ?? 0],
        'lost' => ['label' => 'Lost', 'count' => $stats['lost'] ?? 0],
    ];
@endphp

<div class="page-shell betting-page betting-ledger-page">
    <section class="betting-hero">
        <div>
            <p class="home-eyebrow">Sportsbook Ledger</p>
            <h1>Your Betting Desk</h1>
            <p class="page-subtitle">Track wallet health, open exposure, ticket outcomes, and live markets from one polished betting board.</p>
        </div>

        <div class="betting-wallet-card">
            <span>Available Coins</span>
            <strong>{{ number_format($stats['coins'] ?? 0) }}</strong>
            <small>{{ $net >= 0 ? 'Positive' : 'Negative' }} net form: {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }} coins · ROI {{ $roi >= 0 ? '+' : '' }}{{ $roi }}%</small>
        </div>
    </section>

    <nav class="betting-desk-nav" aria-label="Betting sections">
        <a href="{{ route('bets.index') }}" class="is-active">Ledger</a>
        <a href="{{ route('matches.index') }}">Markets</a>
        <a href="#tickets">Tickets</a>
        <a href="#discipline">Stake Plan</a>
    </nav>

    <section class="betting-health-strip">
        <div><span>Pending Exposure</span><strong>{{ number_format($stats['pending_exposure'] ?? 0) }}</strong></div>
        <div><span>Average Stake</span><strong>{{ number_format($stats['avg_stake'] ?? 0) }}</strong></div>
        <div><span>Settled Tickets</span><strong>{{ number_format($stats['settled'] ?? 0) }}</strong></div>
        <div><span>ROI</span><strong class="{{ $roi >= 0 ? 'text-positive' : 'text-negative' }}">{{ $roi >= 0 ? '+' : '' }}{{ $roi }}%</strong></div>
    </section>

    <section class="betting-kpi-grid">
        <div class="betting-kpi-card">
            <span>Total Bets</span>
            <strong>{{ $stats['total'] ?? 0 }}</strong>
            <small>{{ $stats['pending'] ?? 0 }} still pending</small>
        </div>
        <div class="betting-kpi-card">
            <span>Win Rate</span>
            <strong>{{ $winRate }}%</strong>
            <small>{{ $stats['won'] ?? 0 }} won / {{ $stats['lost'] ?? 0 }} lost</small>
        </div>
        <div class="betting-kpi-card">
            <span>Total Wagered</span>
            <strong>{{ number_format($stats['wagered'] ?? 0) }}</strong>
            <small>Coins committed</small>
        </div>
        <div class="betting-kpi-card {{ $net >= 0 ? 'is-positive' : 'is-negative' }}">
            <span>Net Result</span>
            <strong>{{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}</strong>
            <small>Biggest win {{ number_format($biggestWin) }}</small>
        </div>
    </section>

    <div class="betting-layout">
        <main class="betting-main-column">
            <section class="betting-panel" id="tickets">
                <div class="betting-panel-heading">
                    <div>
                        <p class="home-eyebrow">Tickets</p>
                        <h2>Bet History</h2>
                    </div>
                    <div class="betting-filter-tabs" data-bet-filter-tabs>
                        @foreach($statusTabs as $key => $tab)
                            <button type="button" class="{{ $loop->first ? 'is-active' : '' }}" data-bet-filter="{{ $key }}">
                                {{ $tab['label'] }}
                                <span>{{ $tab['count'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="betting-ticket-list">
                    @forelse($history as $bet)
                        @php
                            $insights = app(\App\Services\BetService::class)->getMatchInsights($bet->gameMatch, $bet->bet_on_user_id, $bet->amount);
                            $statusClass = 'is-pending';
                            if ($bet->status === 'won') {
                                $statusClass = 'is-won';
                            } elseif ($bet->status === 'lost') {
                                $statusClass = 'is-lost';
                            }

                            $ticketOdds = $bet->odds ?: ($insights['selected_odds'] ?? 1);
                            $expectedReturn = $bet->amount * $ticketOdds;
                            $profit = ($bet->status === 'won' ? ($bet->payout ?? 0) : 0) - ($bet->amount ?? 0);
                        @endphp

                        <article class="betting-ticket {{ $statusClass }}" data-bet-ticket data-status="{{ $bet->status }}">
                            <div class="betting-ticket-id">
                                <span>Ticket</span>
                                <strong>#{{ str_pad($bet->id, 5, '0', STR_PAD_LEFT) }}</strong>
                                <small>{{ $bet->created_at->format('M d') }}</small>
                            </div>

                            <div class="betting-ticket-body">
                                <div class="betting-ticket-title">
                                    <div>
                                        <h3>{{ $bet->gameMatch?->player1?->name ?? 'Player 1' }} vs {{ $bet->gameMatch?->player2?->name ?? 'TBD' }}</h3>
                                        <p>Pick: <strong>{{ $bet->betOnUser?->name ?? 'Unknown' }}</strong> · {{ $bet->created_at->diffForHumans() }}</p>
                                    </div>
                                    <span class="betting-status-pill">{{ ucfirst($bet->status) }}</span>
                                </div>

                                <div class="betting-ticket-metrics">
                                    <div>
                                        <span>Stake</span>
                                        <strong>{{ number_format($bet->amount) }}</strong>
                                    </div>
                                    <div>
                                        <span>Odds</span>
                                        <strong>x{{ number_format($ticketOdds, 2) }}</strong>
                                    </div>
                                    <div>
                                        <span>Expected</span>
                                        <strong>{{ number_format($expectedReturn) }}</strong>
                                    </div>
                                    <div>
                                        <span>{{ $bet->status === 'pending' ? 'Risk' : 'Profit' }}</span>
                                        <strong class="{{ $profit >= 0 ? 'text-positive' : 'text-negative' }}">
                                            {{ $bet->status === 'pending' ? ($insights['risk_level'] ?? 'Balanced') : (($profit >= 0 ? '+' : '') . number_format($profit)) }}
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            <div class="betting-ticket-actions">
                                @if($bet->gameMatch)
                                    <a href="{{ route('matches.show', $bet->gameMatch->id) }}" class="betting-inline-link">Match</a>
                                @endif
                                <a href="{{ route('bets.show', $bet->id) }}" class="betting-ticket-link">Details</a>
                            </div>
                        </article>
                    @empty
                        <div class="betting-empty-state">
                            <h3>No tickets yet</h3>
                            <p>Your betting history will appear here after you place a stake on a match.</p>
                            <a href="{{ route('matches.index') }}" class="btn btn-primary">Browse Matches</a>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="betting-side-column">
            <section class="betting-panel betting-performance-card">
                <p class="home-eyebrow">Performance</p>
                <h2>Form Read</h2>
                <div class="betting-ring" style="--value: {{ $winRate }}%">
                    <strong>{{ $winRate }}%</strong>
                    <span>Win rate</span>
                </div>
                <div class="betting-side-stats">
                    <div><span>Pending</span><strong>{{ $stats['pending'] ?? 0 }}</strong></div>
                    <div><span>Payout</span><strong>{{ number_format($stats['payout'] ?? 0) }}</strong></div>
                    <div><span>Biggest Win</span><strong>{{ number_format($biggestWin) }}</strong></div>
                </div>
            </section>

            <section class="betting-panel betting-market-watch">
                <div class="betting-panel-heading">
                    <div>
                        <p class="home-eyebrow">Market Watch</p>
                        <h2>Open Markets</h2>
                    </div>
                    <a href="{{ route('matches.index') }}" class="betting-inline-link">Browse</a>
                </div>

                <div class="betting-watch-list">
                    @forelse($openMarkets as $market)
                        <a href="{{ route('bets.slip', $market->id) }}" class="betting-watch-row">
                            <strong>{{ $market->player1?->name ?? 'Player 1' }} vs {{ $market->player2?->name ?? 'Player 2' }}</strong>
                            <span>{{ ucfirst($market->status) }} · {{ $market->match_date ? $market->match_date->format('M d, h:i A') : 'Time TBD' }}</span>
                        </a>
                    @empty
                        <div class="empty-inline">No open betting markets right now.</div>
                    @endforelse
                </div>
            </section>

            <section class="betting-panel betting-favorites-card">
                <p class="home-eyebrow">Patterns</p>
                <h2>Favorite Picks</h2>
                <div class="betting-watch-list">
                    @forelse($favoritePicks as $pick)
                        <div class="betting-watch-row">
                            <strong>{{ $pick['name'] }}</strong>
                            <span>{{ $pick['count'] }} tickets · {{ $pick['won'] }} wins</span>
                        </div>
                    @empty
                        <div class="empty-inline">Place a few bets to build pick patterns.</div>
                    @endforelse
                </div>
            </section>

            <section class="betting-panel betting-tip-card" id="discipline">
                <p class="home-eyebrow">Discipline</p>
                <h2>Stake Plan</h2>
                <p>Use small stakes for balanced markets, reserve bigger bets for high-confidence picks, and avoid chasing losses.</p>
                <div class="betting-stake-guide">
                    <span>Safe 1-3%</span>
                    <span>Standard 5%</span>
                    <span>High risk 10% max</span>
                </div>
            </section>
        </aside>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('[data-bet-filter]');
    const tickets = document.querySelectorAll('[data-bet-ticket]');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const status = tab.getAttribute('data-bet-filter');
            tabs.forEach((item) => item.classList.toggle('is-active', item === tab));

            tickets.forEach(function (ticket) {
                const shouldShow = status === 'all' || ticket.getAttribute('data-status') === status;
                ticket.hidden = !shouldShow;
            });
        });
    });
});
</script>
@endsection
