@extends('layout')

@section('title', 'Bet Slip - BadNet')

@section('content')
@php
    $players = collect($betSlip['players'] ?? []);
    $selected = $players->firstWhere('selected', true) ?? $players->first();
@endphp

<div class="min-h-screen bg-slate-50 px-4 py-8 text-slate-900 transition-colors dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Bet Slip</p>
                    <h1 class="mt-3 font-heading text-3xl font-extrabold text-slate-900 dark:text-slate-50">{{ $match->player1->name }} vs {{ $match->player2?->name ?? 'TBD' }}</h1>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Choose your side, check confidence, and place the bet in one clean flow.</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">{{ ucfirst($match->status) }}</span>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ $match->match_date->format('M d, h:i A') }}</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $match->location ?? __('ui.match.court_tbd') }}</span>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-heading text-xl font-bold text-slate-900 dark:text-slate-50">Pick a player</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Pill control keeps the choice clear and mobile-friendly.</p>
                    </div>
                    <a href="{{ route('matches.show', $match->id) }}" class="text-sm font-semibold text-sky-600 dark:text-sky-300">Back to match</a>
                </div>

                <form action="{{ route('matches.placeBet', $match->id) }}" method="POST" class="mt-6 space-y-6" id="betSlipForm">
                    @csrf

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($players as $player)
                            <label class="group block cursor-pointer">
                                <input type="radio" name="bet_on_user_id" value="{{ $player['id'] }}" class="peer sr-only" {{ $player['selected'] ? 'checked' : '' }} data-player-choice>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:-translate-y-0.5 peer-checked:border-sky-400 peer-checked:bg-sky-50 peer-checked:shadow-sm dark:border-slate-700 dark:bg-slate-800/60 dark:peer-checked:border-sky-500 dark:peer-checked:bg-slate-800">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-heading text-lg font-bold text-slate-900 dark:text-slate-50">{{ $player['name'] }}</div>
                                            <div class="text-sm text-slate-500 dark:text-slate-400">{{ $player['rank'] }} · {{ $player['elo'] }} ELO</div>
                                        </div>
                                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">x{{ number_format($player['odds'], 2) }}</span>
                                    </div>

                                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs font-semibold">
                                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Confidence {{ $player['confidence'] }}%</span>
                                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ $player['risk_level'] }}</span>
                                        <span class="rounded-full bg-sky-50 px-2.5 py-1 text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">Form {{ $player['form_label'] }}</span>
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700 dark:bg-slate-700/70 dark:text-slate-200">Community {{ $player['community_pick_ratio'] }}%</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Stake amount</label>
                            <input type="number" min="10" max="{{ auth()->user()->virtual_coins }}" name="amount" id="stakeAmount" value="10" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-sky-400 focus:ring-4 focus:ring-sky-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-sky-500 dark:focus:ring-sky-500/20">
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Preview</div>
                            <div class="mt-2 font-heading text-lg font-bold text-slate-900 dark:text-slate-50" id="returnPreview">Expected return: {{ $selected['expected_return'] ?? 0 }} 🪙</div>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300" id="confidencePreview">Confidence: {{ $selected['confidence'] ?? 0 }}%</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Recommendation</div>
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Stick to balanced stakes and watch the community pick ratio before you lock in.</p>
                        </div>

                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                            Place Bet
                        </button>
                    </div>
                </form>
            </section>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="font-heading text-lg font-bold text-slate-900 dark:text-slate-50">Slip Stats</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Virtual coins</span><strong class="text-slate-900 dark:text-slate-50">{{ auth()->user()->virtual_coins }}</strong></div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Best odds</span><strong class="text-slate-900 dark:text-slate-50">x{{ number_format(collect($players)->max('odds') ?? 1, 2) }}</strong></div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Highest confidence</span><strong class="text-slate-900 dark:text-slate-50">{{ collect($players)->max('confidence') ?? 0 }}%</strong></div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-50 to-sky-50 p-5 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-900">
                    <p class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Note</p>
                    <h3 class="mt-3 font-heading text-lg font-bold text-slate-900 dark:text-slate-50">Confidence, form and crowd all matter</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">This slip blends ELO-based odds, recent form, and community picks so the decision feels like a real sportsbook, not a plain form.</p>
                </section>
            </aside>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('[data-player-choice]');
    const stakeInput = document.getElementById('stakeAmount');
    const returnPreview = document.getElementById('returnPreview');
    const confidencePreview = document.getElementById('confidencePreview');
    const players = @json($players->values());

    function updatePreview() {
        const selected = Array.from(radios).find((radio) => radio.checked);
        if (!selected) return;

        const player = players.find((item) => String(item.id) === String(selected.value));
        if (!player) return;

        const stake = parseInt(stakeInput?.value || '0', 10) || 0;
        const expectedReturn = Math.round(stake * parseFloat(player.odds || 1));

        if (returnPreview) {
            returnPreview.textContent = `Expected return: ${expectedReturn} 🪙`;
        }

        if (confidencePreview) {
            confidencePreview.textContent = `Confidence: ${player.confidence}% · ${player.form_label} · Community ${player.community_pick_ratio}%`;
        }
    }

    radios.forEach((radio) => radio.addEventListener('change', updatePreview));
    if (stakeInput) stakeInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>
@endsection