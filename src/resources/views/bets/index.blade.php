@extends('layout')

@section('title', 'Your Bets - BadNet')

@section('content')
@php
    $net = ($stats['payout'] ?? 0) - ($stats['wagered'] ?? 0);
    $winRate = ($stats['won'] ?? 0) + ($stats['lost'] ?? 0) > 0
        ? round((($stats['won'] ?? 0) / max(1, ($stats['won'] ?? 0) + ($stats['lost'] ?? 0))) * 100)
        : 0;
    $favoriteLabel = collect($favoritePicks ?? [])->keys()->take(3)->join(', ');
@endphp

<div class="min-h-screen bg-slate-50 px-4 py-8 text-slate-900 transition-colors dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Coins</p>
                    <h1 class="mt-3 font-heading text-3xl font-extrabold text-slate-900 dark:text-slate-50">Your Bets</h1>
                    <p class="mt-2 max-w-2xl text-sm text-slate-600 dark:text-slate-300">Track your picks, payouts, and performance in one clean view.</p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-center shadow-sm dark:border-slate-800 dark:bg-slate-800/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Coins</div>
                        <div class="mt-1 font-heading text-xl font-bold text-slate-900 dark:text-slate-50">{{ number_format($stats['coins'] ?? 0) }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-emerald-50 px-4 py-3 text-center shadow-sm dark:border-slate-800 dark:bg-emerald-500/10">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Won</div>
                        <div class="mt-1 font-heading text-xl font-bold text-slate-900 dark:text-slate-50">{{ $stats['won'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-amber-50 px-4 py-3 text-center shadow-sm dark:border-slate-800 dark:bg-amber-500/10">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Win Rate</div>
                        <div class="mt-1 font-heading text-xl font-bold text-slate-900 dark:text-slate-50">{{ $winRate }}%</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-sky-50 px-4 py-3 text-center shadow-sm dark:border-slate-800 dark:bg-sky-500/10">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-700 dark:text-sky-300">Net</div>
                        <div class="mt-1 font-heading text-xl font-bold {{ $net >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">{{ $net >= 0 ? '+' : '' }}{{ number_format($net) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-heading text-lg font-bold text-slate-900 dark:text-slate-50">Betting Snapshot</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">A quick read on your current betting form.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Net {{ $net >= 0 ? '+' : '' }}{{ number_format($net) }} 🪙</span>
                    <span class="rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/10 dark:text-sky-300">{{ $winRate }}% win rate</span>
                    @if(!empty($favoritePicks) && $favoritePicks->count())
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Top picks: {{ collect($favoritePicks)->values()->sum() }} bets</span>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="font-heading text-xl font-bold text-slate-900 dark:text-slate-50">Bet History</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Latest bets placed from the app.</p>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ $stats['total'] ?? 0 }} total</span>
                </div>

                <div class="space-y-4">
                    @forelse($history as $bet)
                        @php
                            $insights = app(\App\Services\BetService::class)->getMatchInsights($bet->gameMatch, $bet->bet_on_user_id, $bet->amount);
                            $statusTone = $bet->status === 'won'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20'
                                : ($bet->status === 'lost'
                                    ? 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20'
                                    : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20');
                            $riskTone = $insights['risk_tone'] ?? 'amber';
                            $riskClass = $riskTone === 'emerald'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20'
                                : ($riskTone === 'rose'
                                    ? 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20'
                                    : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20');
                        @endphp
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-800/60">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="truncate font-heading text-base font-bold text-slate-900 dark:text-slate-50">
                                            Bet on: {{ $bet->betOnUser?->name ?? 'Player' }}
                                        </h3>
                                        <span class="rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusTone }}">
                                            {{ ucfirst($bet->status) }}
                                        </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-3 text-sm text-slate-600 dark:text-slate-300">
                                        <span>Match: {{ $bet->gameMatch?->player1?->name }} vs {{ $bet->gameMatch?->player2?->name ?? 'TBD' }}</span>
                                        <span>•</span>
                                        <span>{{ $bet->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                                        <span class="rounded-full border px-2.5 py-1 {{ $riskClass }}">{{ $insights['risk_level'] ?? 'Balanced' }}</span>
                                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">Payout x{{ number_format($insights['selected_odds'] ?? 1, 2) }}</span>
                                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">Return {{ number_format($insights['expected_return'] ?? 0) }} 🪙</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-right shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Stake</div>
                                        <div class="font-heading text-lg font-bold text-slate-900 dark:text-slate-50">{{ number_format($bet->amount) }} 🪙</div>
                                    </div>
                                    <a href="{{ route('bets.show', $bet->id) }}" class="inline-flex items-center justify-center rounded-full bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                                        Details
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center dark:border-slate-700 dark:bg-slate-800/60">
                            <h3 class="font-heading text-lg font-bold text-slate-900 dark:text-slate-50">No bets yet</h3>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">When you place bets, they will appear here with status and payout details.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="font-heading text-lg font-bold text-slate-900 dark:text-slate-50">Performance</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Pending</span><strong class="text-slate-900 dark:text-slate-50">{{ $stats['pending'] ?? 0 }}</strong></div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Wagered</span><strong class="text-slate-900 dark:text-slate-50">{{ number_format($stats['wagered'] ?? 0) }} 🪙</strong></div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Payout</span><strong class="text-slate-900 dark:text-slate-50">{{ number_format($stats['payout'] ?? 0) }} 🪙</strong></div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-gradient-to-br from-sky-50 to-emerald-50 p-5 shadow-sm dark:border-slate-800 dark:from-slate-900 dark:to-slate-900">
                    <p class="inline-flex rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Tip</p>
                    <h3 class="mt-3 font-heading text-lg font-bold text-slate-900 dark:text-slate-50">Bet smart, not loud</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Use smaller stakes for uncertain matches and keep your biggest bets for strong-form players.</p>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
