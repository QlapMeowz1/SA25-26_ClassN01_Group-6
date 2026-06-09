@extends('layout')

@section('title', 'Bet Slip - BadNet')

@section('content')
@php
    $players = collect($betSlip['players'] ?? []);
    $selected = $players->firstWhere('selected', true) ?? $players->first();
    $wallet = auth()->user()->virtual_coins ?? 0;
    $defaultStake = min(100, max(10, (int) $wallet));
    $matchTime = $match->match_date ? $match->match_date->format('M d, h:i A') : 'Time TBD';
    $maxRecommended = max(10, (int) floor($wallet * 0.1));
@endphp

<div class="page-shell betting-page betting-slip-page">
    <section class="betting-hero betting-slip-hero">
        <div>
            <p class="home-eyebrow">Bet Slip</p>
            <h1>{{ $match->player1->name }} vs {{ $match->player2?->name ?? 'TBD' }}</h1>
            <p class="page-subtitle">Choose a side, size your stake, and review the return before locking the ticket.</p>
        </div>

        <div class="betting-match-meta">
            <span>{{ ucfirst($match->status) }}</span>
            <span>{{ $matchTime }}</span>
            <span>{{ $match->location ?? __('ui.match.court_tbd') }}</span>
        </div>
    </section>

    <section class="betting-market-board">
        <div>
            <span>Wallet</span>
            <strong>{{ number_format($wallet) }}</strong>
            <small>Available coins</small>
        </div>
        <div>
            <span>Recommended Cap</span>
            <strong>{{ number_format($maxRecommended) }}</strong>
            <small>10% discipline limit</small>
        </div>
        <div>
            <span>Market Type</span>
            <strong>Winner</strong>
            <small>Settles after final result</small>
        </div>
        <div>
            <span>Minimum Stake</span>
            <strong>10</strong>
            <small>Coins per ticket</small>
        </div>
    </section>

    <form action="{{ route('matches.placeBet', $match->id) }}" method="POST" class="betting-slip-layout" id="betSlipForm" onsubmit="return confirm('Confirm this bet? Your stake will be deducted and locked immediately.');">
        @csrf

        <main class="betting-main-column">
            <section class="betting-panel">
                <div class="betting-panel-heading">
                    <div>
                        <p class="home-eyebrow">Market</p>
                        <h2>Match Winner</h2>
                    </div>
                    <a href="{{ route('matches.show', $match->id) }}" class="betting-inline-link">Back to match</a>
                </div>

                <div class="betting-market-grid">
                    @forelse($players as $player)
                        @php
                            $toneClass = $player['risk_tone'] === 'emerald'
                                ? 'is-low-risk'
                                : ($player['risk_tone'] === 'rose' ? 'is-high-risk' : 'is-balanced');
                        @endphp
                        <label class="betting-market-card {{ $toneClass }}">
                            <input
                                type="radio"
                                name="bet_on_user_id"
                                value="{{ $player['id'] }}"
                                class="sr-only"
                                {{ $player['selected'] ? 'checked' : '' }}
                                data-player-choice>

                            <div class="betting-market-top">
                                <div class="betting-player-avatar">{{ strtoupper(substr($player['name'], 0, 1)) }}</div>
                                <div>
                                    <h3>{{ $player['name'] }}</h3>
                                    <p>{{ $player['rank'] }} · {{ number_format($player['elo']) }} ELO</p>
                                </div>
                                <strong class="betting-odds-pill">x{{ number_format($player['odds'], 2) }}</strong>
                            </div>

                            <div class="betting-player-comparison">
                                <div><span>ELO</span><strong>{{ number_format($player['elo']) }}</strong></div>
                                <div><span>Rank</span><strong>{{ $player['rank'] }}</strong></div>
                                <div><span>Market</span><strong>{{ $player['risk_level'] }}</strong></div>
                            </div>

                            <div class="betting-probability-bar">
                                <span style="width: {{ $player['probability'] }}%"></span>
                            </div>

                            <div class="betting-market-metrics">
                                <div><span>Probability</span><strong>{{ $player['probability'] }}%</strong></div>
                                <div><span>Confidence</span><strong>{{ $player['confidence'] }}%</strong></div>
                                <div><span>Form</span><strong>{{ $player['form_label'] }}</strong></div>
                                <div><span>Crowd</span><strong>{{ $player['community_pick_ratio'] }}%</strong></div>
                            </div>

                            <span class="betting-risk-badge">{{ $player['risk_level'] }}</span>
                        </label>
                    @empty
                        <div class="betting-empty-state">
                            <h3>This market is not ready</h3>
                            <p>The match needs two confirmed players before betting can open.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="betting-panel">
                <div class="betting-panel-heading">
                    <div>
                        <p class="home-eyebrow">Stake</p>
                        <h2>Size Your Bet</h2>
                    </div>
                </div>

                <div class="betting-stake-layout">
                    <div>
                        <label for="stakeAmount">Stake amount</label>
                        <input
                            type="number"
                            min="10"
                            max="{{ $wallet }}"
                            name="amount"
                            id="stakeAmount"
                            value="{{ $defaultStake }}"
                            data-wallet="{{ $wallet }}"
                            required>
                        <p class="betting-input-help">Wallet balance: {{ number_format($wallet) }} coins</p>
                    </div>

                    <div class="betting-quick-stakes">
                        <button type="button" data-quick-add="100">+100</button>
                        <button type="button" data-quick-add="500">+500</button>
                        <button type="button" data-quick-stake="{{ $wallet }}">All-in</button>
                    </div>
                </div>

                <div class="betting-discipline-note">
                    <strong>Stake discipline</strong>
                    <span>Keeping this ticket under {{ number_format($maxRecommended) }} coins protects the wallet from oversized exposure.</span>
                </div>
            </section>
        </main>

        <aside class="betting-side-column">
            <section class="betting-panel betting-slip-card">
                <div class="betting-panel-heading">
                    <div>
                        <p class="home-eyebrow">Ticket</p>
                        <h2>Bet Slip</h2>
                    </div>
                    <span class="betting-status-pill">Open</span>
                </div>

                <div class="betting-slip-selection">
                    <span>Selection</span>
                    <strong id="selectionName">{{ $selected['name'] ?? 'Choose player' }}</strong>
                    <small id="selectionMeta">{{ $selected ? 'x' . number_format($selected['odds'], 2) . ' · ' . $selected['risk_level'] : 'No market selected' }}</small>
                </div>

                <div class="betting-ticket-divider"></div>

                <div class="betting-slip-preview">
                    <div><span>Stake</span><strong id="stakePreview">{{ number_format($defaultStake) }}</strong></div>
                    <div><span>Odds</span><strong id="oddsPreview">x{{ number_format($selected['odds'] ?? 1, 2) }}</strong></div>
                    <div><span>Return</span><strong id="returnPreview">{{ number_format(round($defaultStake * ($selected['odds'] ?? 1))) }}</strong></div>
                    <div><span>Profit</span><strong id="profitPreview">{{ number_format(round($defaultStake * (($selected['odds'] ?? 1) - 1))) }}</strong></div>
                </div>

                <div class="betting-risk-meter">
                    <div>
                        <span>Risk Meter</span>
                        <strong id="riskPreview">{{ $selected['risk_level'] ?? 'Balanced' }}</strong>
                    </div>
                    <div class="betting-probability-bar">
                        <span id="confidenceMeter" style="width: {{ $selected['confidence'] ?? 50 }}%"></span>
                    </div>
                    <small id="confidencePreview">Confidence {{ $selected['confidence'] ?? 0 }}%</small>
                </div>

                <p class="betting-slip-error" id="stakeError" hidden></p>

                <button type="submit" class="btn btn-primary btn-block" id="placeBetButton">Place Bet</button>
                <a href="{{ route('bets.index') }}" class="btn btn-secondary btn-block">Back to Desk</a>
            </section>
        </aside>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const players = @json($players->values());
    const radios = document.querySelectorAll('[data-player-choice]');
    const stakeInput = document.getElementById('stakeAmount');
    const quickStakeButtons = document.querySelectorAll('[data-quick-stake], [data-quick-add]');
    const wallet = parseInt(stakeInput?.dataset.wallet || '0', 10) || 0;
    const formatter = new Intl.NumberFormat('en-US');

    const els = {
        selectionName: document.getElementById('selectionName'),
        selectionMeta: document.getElementById('selectionMeta'),
        stakePreview: document.getElementById('stakePreview'),
        oddsPreview: document.getElementById('oddsPreview'),
        returnPreview: document.getElementById('returnPreview'),
        profitPreview: document.getElementById('profitPreview'),
        riskPreview: document.getElementById('riskPreview'),
        confidencePreview: document.getElementById('confidencePreview'),
        confidenceMeter: document.getElementById('confidenceMeter'),
        stakeError: document.getElementById('stakeError'),
        placeBetButton: document.getElementById('placeBetButton'),
    };

    function selectedPlayer() {
        const selected = Array.from(radios).find((radio) => radio.checked);
        return players.find((player) => String(player.id) === String(selected?.value));
    }

    function updatePreview() {
        const player = selectedPlayer();
        const stake = Math.max(0, parseInt(stakeInput?.value || '0', 10) || 0);
        const odds = parseFloat(player?.odds || 1);
        const expectedReturn = Math.round(stake * odds);
        const profit = Math.max(0, expectedReturn - stake);
        const invalid = stake < 10 || stake > wallet || !player;

        if (els.selectionName) els.selectionName.textContent = player?.name || 'Choose player';
        if (els.selectionMeta) els.selectionMeta.textContent = player ? `x${odds.toFixed(2)} · ${player.risk_level}` : 'No market selected';
        if (els.stakePreview) els.stakePreview.textContent = formatter.format(stake);
        if (els.oddsPreview) els.oddsPreview.textContent = `x${odds.toFixed(2)}`;
        if (els.returnPreview) els.returnPreview.textContent = formatter.format(expectedReturn);
        if (els.profitPreview) els.profitPreview.textContent = formatter.format(profit);
        if (els.riskPreview) els.riskPreview.textContent = player?.risk_level || 'Balanced';
        if (els.confidencePreview) els.confidencePreview.textContent = `Confidence ${player?.confidence || 0}%`;
        if (els.confidenceMeter) els.confidenceMeter.style.width = `${player?.confidence || 0}%`;

        if (els.stakeError) {
            els.stakeError.hidden = !invalid;
            els.stakeError.textContent = stake > wallet
                ? 'Stake exceeds your wallet balance.'
                : (stake < 10 ? 'Minimum stake is 10 coins.' : 'Choose a market before placing the bet.');
        }

        if (els.placeBetButton) els.placeBetButton.disabled = invalid;
    }

    radios.forEach((radio) => radio.addEventListener('change', updatePreview));
    if (stakeInput) stakeInput.addEventListener('input', updatePreview);
    quickStakeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            if (!stakeInput) return;
            const add = parseInt(button.getAttribute('data-quick-add') || '0', 10) || 0;
            const fixed = parseInt(button.getAttribute('data-quick-stake') || '0', 10) || 0;
            const current = parseInt(stakeInput.value || '0', 10) || 0;
            stakeInput.value = add > 0 ? Math.min(wallet, current + add) : Math.min(wallet, fixed);
            updatePreview();
        });
    });

    updatePreview();
});
</script>
@endsection
