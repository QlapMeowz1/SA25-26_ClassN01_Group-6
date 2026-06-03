@extends('layout')

@section('title', 'Bet Details - BadNet')

@section('content')
@php
    $insights = app(\App\Services\BetService::class)->getMatchInsights($bet->gameMatch, $bet->bet_on_user_id, $bet->amount);
    $statusTone = $bet->status === 'won'
        ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20'
        : ($bet->status === 'lost'
            ? 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20'
            : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20');
    $stake = $bet->amount ?? 0;
    $payout = $bet->payout ?? 0;
@endphp

<div class="min-h-screen bg-slate-50 px-4 py-8 text-slate-900 transition-colors dark:bg-slate-950 dark:text-slate-100 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Bet Details</p>
                    <h1 class="mt-3 font-heading text-3xl font-extrabold text-slate-900 dark:text-slate-50">{{ $bet->betOnUser?->name ?? 'Player' }}</h1>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Placed {{ $bet->created_at->diffForHumans() }}</p>
                </div>

                <span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-semibold {{ $statusTone }}">
                    {{ ucfirst($bet->status) }}
                </span>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-heading text-xl font-bold text-slate-900 dark:text-slate-50">Bet Summary</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Match</div>
                        <div class="mt-2 font-heading text-lg font-bold text-slate-900 dark:text-slate-50">
                            {{ $bet->gameMatch?->player1?->name }} vs {{ $bet->gameMatch?->player2?->name ?? 'TBD' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Picked Player</div>
                        <div class="mt-2 font-heading text-lg font-bold text-slate-900 dark:text-slate-50">
                            {{ $bet->betOnUser?->name ?? 'Unknown' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Stake</div>
                        <div class="mt-2 font-heading text-lg font-bold text-slate-900 dark:text-slate-50">{{ number_format($stake) }} 🪙</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Payout</div>
                        <div class="mt-2 font-heading text-lg font-bold text-slate-900 dark:text-slate-50">{{ number_format($payout) }} 🪙</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Risk</div>
                        <div class="mt-2 inline-flex rounded-full border px-2.5 py-1 text-sm font-semibold {{ ($insights['risk_tone'] ?? 'amber') === 'emerald' ? 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/20' : (($insights['risk_tone'] ?? 'amber') === 'rose' ? 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:border-rose-500/20' : 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20') }}">
                            {{ $insights['risk_level'] ?? 'Balanced' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">Odds</div>
                        <div class="mt-2 font-heading text-lg font-bold text-slate-900 dark:text-slate-50">x{{ number_format($insights['selected_odds'] ?? 1, 2) }}</div>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="font-heading text-lg font-bold text-slate-900 dark:text-slate-50">Bet Info</h3>
                    <div class="mt-4 space-y-3 text-sm text-slate-600 dark:text-slate-300">
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Match time</span><strong class="text-slate-900 dark:text-slate-50">{{ $bet->gameMatch?->match_date?->format('M d, h:i A') }}</strong></div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Placed by</span><strong class="text-slate-900 dark:text-slate-50">{{ $bet->user?->name ?? 'You' }}</strong></div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Status</span><strong class="text-slate-900 dark:text-slate-50">{{ ucfirst($bet->status) }}</strong></div>
                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 dark:bg-slate-800/60"><span>Expected return</span><strong class="text-slate-900 dark:text-slate-50">{{ number_format($insights['expected_return'] ?? 0) }} 🪙</strong></div>
                    </div>
                </section>

                <a href="{{ route('bets.index') }}" class="inline-flex w-full items-center justify-center rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 dark:bg-sky-500 dark:text-slate-950 dark:hover:bg-sky-400">
                    Back to bet history
                </a>
            </aside>
        </div>
    </div>
</div>
@endsection
