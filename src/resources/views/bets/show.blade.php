@extends('layout')

@section('title', 'Bet Details - BadNet')

@section('content')
@php
    $insights = app(\App\Services\BetService::class)->getMatchInsights($bet->gameMatch, $bet->bet_on_user_id, $bet->amount);
    $stake = $bet->amount ?? 0;
    $payout = $bet->payout ?? 0;
    $expectedReturn = $insights['expected_return'] ?? round($stake * ($insights['selected_odds'] ?? 1));
    $profit = $bet->status === 'won' ? $payout - $stake : ($bet->status === 'lost' ? -$stake : $expectedReturn - $stake);
    $statusClass = $bet->status === 'won' ? 'is-won' : ($bet->status === 'lost' ? 'is-lost' : 'is-pending');
    $match = $bet->gameMatch;
@endphp

<div class="page-shell betting-page betting-detail-page">
    <section class="betting-hero">
        <div>
            <p class="home-eyebrow">Ticket Detail</p>
            <h1>Bet #{{ str_pad($bet->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p class="page-subtitle">Placed {{ $bet->created_at->diffForHumans() }} on {{ $bet->betOnUser?->name ?? 'Unknown player' }}.</p>
        </div>

        <div class="betting-wallet-card {{ $statusClass }}">
            <span>Status</span>
            <strong>{{ ucfirst($bet->status) }}</strong>
            <small>{{ $bet->status === 'pending' ? 'Waiting for match settlement' : 'Ticket settled' }}</small>
        </div>
    </section>

    <nav class="betting-desk-nav" aria-label="Betting sections">
        <a href="{{ route('bets.index') }}">Ledger</a>
        <a href="{{ $match ? route('matches.show', $match->id) : route('matches.index') }}">Match</a>
        <a href="#receipt" class="is-active">Receipt</a>
        <a href="#risk">Risk Read</a>
    </nav>

    <section class="betting-health-strip">
        <div><span>Stake</span><strong>{{ number_format($stake) }}</strong></div>
        <div><span>Expected</span><strong>{{ number_format($expectedReturn) }}</strong></div>
        <div><span>Payout</span><strong>{{ number_format($payout) }}</strong></div>
        <div><span>{{ $bet->status === 'pending' ? 'Potential' : 'Profit' }}</span><strong class="{{ $profit >= 0 ? 'text-positive' : 'text-negative' }}">{{ $profit >= 0 ? '+' : '' }}{{ number_format($profit) }}</strong></div>
    </section>

    <div class="betting-layout">
        <main class="betting-main-column">
            <section class="betting-panel betting-receipt" id="receipt">
                <div class="betting-panel-heading">
                    <div>
                        <p class="home-eyebrow">Receipt</p>
                        <h2>Bet Summary</h2>
                    </div>
                    <span class="betting-status-pill {{ $statusClass }}">{{ ucfirst($bet->status) }}</span>
                </div>

                <div class="betting-matchup-card">
                    <div>
                        <span>Match</span>
                        <strong>{{ $match?->player1?->name ?? 'Player 1' }} vs {{ $match?->player2?->name ?? 'TBD' }}</strong>
                        <small>{{ $match?->match_date ? $match->match_date->format('M d, h:i A') : 'Time TBD' }} · {{ $match?->location ?? __('ui.match.court_tbd') }}</small>
                    </div>
                    <a href="{{ $match ? route('matches.show', $match->id) : route('matches.index') }}" class="betting-inline-link">View match</a>
                </div>

                <div class="betting-receipt-code">
                    <span>Ticket reference</span>
                    <strong>BADNET-{{ str_pad($bet->id, 5, '0', STR_PAD_LEFT) }}</strong>
                    <small>Use this reference when reviewing betting activity in admin.</small>
                </div>

                <div class="betting-ticket-metrics betting-detail-metrics">
                    <div>
                        <span>Selection</span>
                        <strong>{{ $bet->betOnUser?->name ?? 'Unknown' }}</strong>
                    </div>
                    <div>
                        <span>Stake</span>
                        <strong>{{ number_format($stake) }}</strong>
                    </div>
                    <div>
                        <span>Odds</span>
                        <strong>x{{ number_format($insights['selected_odds'] ?? 1, 2) }}</strong>
                    </div>
                    <div>
                        <span>Expected Return</span>
                        <strong>{{ number_format($expectedReturn) }}</strong>
                    </div>
                    <div>
                        <span>Payout</span>
                        <strong>{{ number_format($payout) }}</strong>
                    </div>
                    <div>
                        <span>{{ $bet->status === 'pending' ? 'Potential Profit' : 'Profit' }}</span>
                        <strong class="{{ $profit >= 0 ? 'text-positive' : 'text-negative' }}">{{ $profit >= 0 ? '+' : '' }}{{ number_format($profit) }}</strong>
                    </div>
                </div>
            </section>

            <section class="betting-panel" id="risk">
                <div class="betting-panel-heading">
                    <div>
                        <p class="home-eyebrow">Market Read</p>
                        <h2>Risk and Probability</h2>
                    </div>
                </div>

                <div class="betting-insight-grid">
                    <div>
                        <span>Risk Level</span>
                        <strong>{{ $insights['risk_level'] ?? 'Balanced' }}</strong>
                    </div>
                    <div>
                        <span>Implied Probability</span>
                        <strong>{{ round(($insights['selected_probability'] ?? 0) * 100) }}%</strong>
                    </div>
                    <div>
                        <span>Player 1 Odds</span>
                        <strong>x{{ number_format($insights['player1_odds'] ?? 1, 2) }}</strong>
                    </div>
                    <div>
                        <span>Player 2 Odds</span>
                        <strong>x{{ number_format($insights['player2_odds'] ?? 1, 2) }}</strong>
                    </div>
                </div>
            </section>
        </main>

        <aside class="betting-side-column">
            <section class="betting-panel betting-timeline-card">
                <p class="home-eyebrow">Timeline</p>
                <h2>Ticket Flow</h2>
                <div class="betting-timeline">
                    <div class="is-complete"><span></span><strong>Placed</strong><small>{{ $bet->created_at->format('M d, h:i A') }}</small></div>
                    <div class="{{ $bet->status !== 'pending' ? 'is-complete' : '' }}"><span></span><strong>Match Settled</strong><small>{{ $bet->status === 'pending' ? 'Pending result' : ucfirst($bet->status) }}</small></div>
                    <div class="{{ $bet->status === 'won' ? 'is-complete' : '' }}"><span></span><strong>Payout</strong><small>{{ $bet->status === 'won' ? number_format($payout) . ' coins' : 'No payout yet' }}</small></div>
                </div>
            </section>

            <section class="betting-panel betting-tip-card">
                <p class="home-eyebrow">Settlement</p>
                <h2>How this resolves</h2>
                <p>Pending tickets settle after the match winner is recorded. Won tickets receive payout, lost tickets return no stake.</p>
            </section>

            <a href="{{ route('bets.index') }}" class="btn btn-primary btn-block">Back to Bet Desk</a>
        </aside>
    </div>
</div>
@endsection
