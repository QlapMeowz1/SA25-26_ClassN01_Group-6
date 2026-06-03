@extends('layout')

@section('title', 'Dashboard - BadNet')

@section('content')
@php
    $user = auth()->user();
    $winRate = $user->getWinRate();
    $rank = $user->rank ?? 'Unranked';
    $postCount = $user->posts()->count();
    $matchCount = $upcomingMatches->count() + $recentMatches->count();
    $leaderboardPosition = $leaderboard->search(fn ($player) => $player->id === $user->id);
    $newPostCount = $communityPosts->filter(fn ($post) => $post->created_at->greaterThan(now()->subHours(3)))->count();
@endphp

<div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 py-8 sm:px-6 lg:px-8">
    <section class="dashboard-hero-panel overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.08)]">
        <div class="h-2 bg-gradient-to-r from-sky-500 via-cyan-400 to-emerald-400"></div>
        <div class="grid gap-8 p-6 sm:p-8 xl:grid-cols-[minmax(0,1.4fr)_360px] xl:items-start">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    {{ __('ui.dashboard.live') }} · {{ __('ui.dashboard.community_feed') }}
                </p>

                <h1 class="mt-4 text-3xl font-black leading-tight text-slate-900 sm:text-5xl">
                    {{ __('ui.dashboard.welcome_back', ['name' => $user->name]) }}
                </h1>

                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
                    {{ __('ui.dashboard.subtitle') }}
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <form action="{{ route('matches.quick') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/15 transition hover:-translate-y-0.5 hover:bg-slate-800">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 2L4 14h6l-1 8 11-14h-6l-1-6z" />
                            </svg>
                            {{ __('ui.match.quick_match') }}
                        </button>
                    </form>

                    <a href="#composer" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50 hover:text-sky-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9" />
                            <path d="M16.5 3.5a2.1 2.1 0 113 3L7 19l-4 1 1-4 12.5-12.5z" />
                        </svg>
                        {{ __('ui.dashboard.create_post') }}
                    </a>

                    <a href="{{ route('matches.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                        {{ __('ui.dashboard.match') }}
                    </a>
                </div>

                <div class="mt-6 flex flex-wrap gap-3 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 font-medium text-slate-700">
                        {{ $communityPosts->total() }} recent posts
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-2 font-medium text-sky-700">
                        {{ $openMatches->count() }} open matches
                    </span>
                    @if($newPostCount > 0)
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2 font-medium text-emerald-700">
                            {{ $newPostCount }} {{ __('ui.dashboard.new') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="dashboard-snapshot rounded-[24px] border border-slate-200 bg-slate-50 p-5">
                <div class="flex items-center gap-4">
                    <div class="relative h-20 w-20 shrink-0 overflow-hidden rounded-full border-4 border-white bg-white shadow">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        <span class="absolute bottom-1 right-1 h-4 w-4 rounded-full border-2 border-white bg-emerald-500"></span>
                    </div>

                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-700">Player snapshot</p>
                        <h2 class="mt-1 truncate text-2xl font-black text-slate-900">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $rank }}</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">ELO</div>
                        <div class="mt-2 text-2xl font-black text-slate-900">{{ $user->elo_rating }}</div>
                    </div>
                    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Win rate</div>
                        <div class="mt-2 text-2xl font-black text-slate-900">{{ $winRate }}%</div>
                    </div>
                    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Posts</div>
                        <div class="mt-2 text-2xl font-black text-slate-900">{{ $postCount }}</div>
                    </div>
                    <div class="rounded-2xl bg-white px-4 py-3 shadow-sm">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Matches</div>
                        <div class="mt-2 text-2xl font-black text-slate-900">{{ $matchCount }}</div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-sky-100 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">
                    @if($leaderboardPosition !== false)
                        You are ranked <strong class="text-slate-900">#{{ $leaderboardPosition + 1 }}</strong> on the live board.
                    @else
                        Keep playing to climb onto the live board.
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-stat-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="dashboard-stat-card rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="rounded-2xl bg-sky-50 px-3 py-2 text-sm font-semibold text-sky-700">ELO</span>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Rating</span>
            </div>
            <div class="mt-5 text-3xl font-black text-slate-900">{{ $user->elo_rating }}</div>
            <p class="mt-2 text-sm text-slate-500">{{ $rank }}</p>
        </div>

        <div class="dashboard-stat-card rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="rounded-2xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">Win rate</span>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Performance</span>
            </div>
            <div class="mt-5 text-3xl font-black text-slate-900">{{ $winRate }}%</div>
            <p class="mt-2 text-sm text-slate-500">{{ $user->wins }} wins / {{ $user->losses }} losses</p>
        </div>

        <div class="dashboard-stat-card rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="rounded-2xl bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-700">Posts</span>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Feed</span>
            </div>
            <div class="mt-5 text-3xl font-black text-slate-900">{{ $postCount }}</div>
            <p class="mt-2 text-sm text-slate-500">{{ $communityPosts->total() }} community updates</p>
        </div>

        <div class="dashboard-stat-card rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="rounded-2xl bg-cyan-50 px-3 py-2 text-sm font-semibold text-cyan-700">Matches</span>
                <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Active</span>
            </div>
            <div class="mt-5 text-3xl font-black text-slate-900">{{ $matchCount }}</div>
            <p class="mt-2 text-sm text-slate-500">{{ $openMatches->count() }} open invitations</p>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[280px_minmax(0,1fr)_320px]">
        <aside class="space-y-6">
            <section class="dashboard-sidebar-card rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="h-14 w-14 overflow-hidden rounded-full border border-slate-200 bg-slate-100">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    </div>
                    <div class="min-w-0">
                        <h3 class="truncate text-lg font-black text-slate-900">{{ $user->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $rank }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 px-3 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">ELO</div>
                        <div class="mt-1 text-xl font-black text-slate-900">{{ $user->elo_rating }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-3 py-3">
                        <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Rank</div>
                        <div class="mt-1 text-xl font-black text-slate-900">{{ $rank }}</div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                    Stay sharp. Your next match should feel one tap away.
                </div>
            </section>

            <section class="dashboard-sidebar-card rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">{{ __('ui.dashboard.online_now') }}</h3>
                        <p class="text-sm text-slate-500">{{ $onlinePlayers->count() }} players to challenge</p>
                    </div>
                    <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                </div>

                @if($onlinePlayers->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                        {{ __('ui.dashboard.no_friends_online') }}
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($onlinePlayers->take(6) as $player)
                            <a href="{{ route('profile.show', $player->id) }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 transition hover:-translate-y-0.5 hover:border-sky-200 hover:bg-white hover:shadow-sm">
                                <div class="relative h-11 w-11 shrink-0 overflow-hidden rounded-full bg-slate-200">
                                    <img src="{{ $player->avatar_url }}" alt="{{ $player->name }}" class="h-full w-full object-cover">
                                    <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-sm font-semibold text-slate-900">{{ $player->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $player->rank ?? 'Unranked' }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>

        <main class="space-y-6">
            <section id="composer" class="dashboard-composer rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex gap-4">
                    <div class="h-12 w-12 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-slate-100">
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    </div>

                    <form action="{{ route('posts.store') }}" method="POST" class="min-w-0 flex-1" enctype="multipart/form-data">
                        @csrf
                        <textarea name="content" rows="4" maxlength="1000" placeholder="{{ __('ui.dashboard.share_match_story', ['name' => $user->name]) }}" class="w-full rounded-[20px] border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-7 text-slate-900 placeholder:text-slate-400 shadow-sm transition focus:border-sky-400 focus:bg-white focus:ring-4 focus:ring-sky-100"></textarea>

                        <div class="mt-4 flex flex-wrap gap-3">
                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100" for="post-images-input">
                                <input type="file" id="post-images-input" name="images[]" accept="image/*" multiple hidden>
                                <span aria-hidden="true">🖼️</span>
                                {{ __('ui.dashboard.image') }}
                            </label>

                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 transition hover:bg-amber-100" for="post-videos-input">
                                <input type="file" id="post-videos-input" name="videos[]" accept="video/*" multiple hidden>
                                <span aria-hidden="true">🎞️</span>
                                {{ __('ui.dashboard.video') }}
                            </label>
                        </div>

                        <div class="composer-media-preview mt-4 flex flex-wrap gap-3" id="composer-media-preview" aria-live="polite"></div>

                        <div class="mt-5 flex flex-col gap-3 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                                <span aria-hidden="true">@</span>
                                {{ __('ui.dashboard.tag') }}
                            </button>

                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-sky-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14" />
                                    <path d="M12 5l7 7-7 7" />
                                </svg>
                                {{ __('ui.dashboard.post_update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="dashboard-feed-card rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-orange-700">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                            {{ __('ui.dashboard.community_feed') }}
                        </p>
                        <h2 class="mt-3 text-2xl font-black text-slate-900">{{ __('ui.dashboard.community_feed') }}</h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            {{ __('ui.dashboard.live') }}
                        </span>
                        @if($newPostCount > 0)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-700">
                                {{ $newPostCount }} {{ __('ui.dashboard.new') }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($communityPosts->isEmpty())
                    <div class="rounded-[20px] border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
                        <h3 class="text-lg font-black text-slate-900">{{ __('ui.dashboard.no_posts_title') }}</h3>
                        <p class="mt-2 text-sm text-slate-600">{{ __('ui.dashboard.no_posts_body') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($communityPosts as $post)
                            @include('partials.post_card', ['post' => $post])
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-200 hover:bg-sky-50 hover:text-sky-800">
                            {{ __('ui.home.see_all') }}
                        </a>
                    </div>
                @endif
            </section>
        </main>

        <aside class="space-y-6">
            <section class="dashboard-sidebar-card rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">{{ __('ui.dashboard.your_matches') }}</h3>
                        <p class="text-sm text-slate-500">{{ __('ui.dashboard.no_upcoming_matches') }}</p>
                    </div>
                    <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="3" />
                        <path d="M8 3v4M16 3v4M3 11h18" />
                    </svg>
                </div>

                @if($upcomingMatches->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center">
                        <p class="text-sm text-slate-600">{{ __('ui.dashboard.no_upcoming_matches') }}</p>
                        <a href="{{ route('challenges.create') }}" class="mt-4 inline-flex items-center justify-center rounded-full bg-sky-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                            {{ __('ui.dashboard.find_match') }}
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($upcomingMatches as $match)
                            @php $isLive = $match->status === 'in_progress'; @endphp
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-slate-900">
                                            {{ $match->player1->name }} <span class="text-slate-400">vs</span> {{ $match->player2?->name ?? __('ui.dashboard.waiting') }}
                                        </div>
                                        <div class="mt-2 inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold {{ $isLive ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                            <span class="h-2 w-2 rounded-full {{ $isLive ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                                            {{ ucfirst($match->status) }}
                                        </div>
                                    </div>

                                    <a href="{{ route('matches.show', $match->id) }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                        {{ __('ui.dashboard.view') }}
                                    </a>
                                </div>

                                <div class="mt-4 space-y-2 text-sm text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <span aria-hidden="true">🕒</span>
                                        {{ $match->match_date->format('M d, h:i A') }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span aria-hidden="true">📍</span>
                                        {{ $match->location ?? __('ui.dashboard.tbd') }}
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="dashboard-sidebar-card rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Open matches</h3>
                        <p class="text-sm text-slate-500">Join someone waiting on the sideline.</p>
                    </div>
                    <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.5-3.5" />
                    </svg>
                </div>

                @if($openMatches->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-600">
                        No open matches right now.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($openMatches as $match)
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="truncate text-sm font-semibold text-slate-900">{{ $match->player1->name }}</div>
                                <div class="mt-2 text-sm text-slate-600">{{ $match->location ?? __('ui.dashboard.tbd') }}</div>
                                <div class="mt-3 flex items-center justify-between gap-3">
                                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{{ $match->match_date->format('M d') }}</span>
                                    <a href="{{ route('matches.show', $match->id) }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                        {{ __('ui.dashboard.view') }}
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900">{{ __('ui.dashboard.top_players') }}</h3>
                        <p class="text-sm text-slate-500">{{ __('ui.dashboard.view_full_leaderboard') }}</p>
                    </div>
                    <svg class="h-6 w-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 4h8v4a4 4 0 0 1-8 0V4z" />
                        <path d="M6 6H4a2 2 0 0 0 2 2" />
                        <path d="M18 6h2a2 2 0 0 1-2 2" />
                        <path d="M12 12v4" />
                        <path d="M8 20h8" />
                    </svg>
                </div>

                <div class="space-y-3">
                    @foreach($leaderboard->take(5) as $index => $player)
                        <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 {{ $player->id === $user->id ? 'ring-2 ring-sky-200' : '' }}">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-black {{ $index === 0 ? 'bg-amber-100 text-amber-800' : ($index === 1 ? 'bg-slate-100 text-slate-700' : ($index === 2 ? 'bg-orange-100 text-orange-800' : 'bg-emerald-100 text-emerald-800')) }}">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <a href="{{ route('profile.show', $player->id) }}" class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-900">
                                {{ $player->name }}
                            </a>

                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm">
                                {{ $player->elo_rating }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('challenges.index') }}" class="mt-4 inline-flex text-sm font-semibold text-sky-600 transition hover:text-sky-700">
                    {{ __('ui.dashboard.view_full_leaderboard') }}
                </a>
            </section>
        </aside>
    </div>
</div>

<div class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3 lg:hidden">
    <button id="fabMain" class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-900 text-white shadow-lg shadow-slate-900/20 transition hover:scale-105" aria-label="{{ __('ui.dashboard.quick_actions') }}" title="{{ __('ui.dashboard.quick_actions') }}">
        <svg class="h-5 w-5 transition-transform" id="fabIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 5v14" />
            <path d="M5 12h14" />
        </svg>
    </button>

    <div id="fabOptions" class="hidden flex-col gap-2 pb-2">
        <a href="#composer" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-slate-900/10 ring-1 ring-slate-200">
            <span aria-hidden="true">✍️</span>
            {{ __('ui.dashboard.create_post') }}
        </a>
        <a href="{{ route('matches.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-slate-900/10 ring-1 ring-slate-200">
            <span aria-hidden="true">🔎</span>
            {{ __('ui.dashboard.match') }}
        </a>
        <a href="{{ route('challenges.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-slate-900/10 ring-1 ring-slate-200">
            <span aria-hidden="true">⚔️</span>
            {{ __('ui.dashboard.challenge') }}
        </a>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fabMain = document.getElementById('fabMain');
    const fabOptions = document.getElementById('fabOptions');
    const fabIcon = document.getElementById('fabIcon');
    const imageInput = document.getElementById('post-images-input');
    const videoInput = document.getElementById('post-videos-input');
    const preview = document.getElementById('composer-media-preview');

    if (fabMain && fabOptions && fabIcon) {
        fabMain.addEventListener('click', function () {
            fabOptions.classList.toggle('hidden');
            fabIcon.classList.toggle('rotate-45');
        });
    }

    function renderPreview() {
        if (!preview) return;

        const imageFiles = imageInput ? Array.from(imageInput.files || []) : [];
        const videoFiles = videoInput ? Array.from(videoInput.files || []) : [];
        const cards = [];

        imageFiles.forEach((file) => {
            const url = URL.createObjectURL(file);
            cards.push(`
                <div class="h-20 w-20 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <img src="${url}" alt="Selected image" class="h-full w-full object-cover" />
                </div>
            `);
        });

        videoFiles.forEach((file) => {
            const url = URL.createObjectURL(file);
            cards.push(`
                <div class="h-20 w-20 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <video src="${url}" muted playsinline class="h-full w-full object-cover"></video>
                </div>
            `);
        });

        preview.innerHTML = cards.join('');
    }

    if (imageInput) imageInput.addEventListener('change', renderPreview);
    if (videoInput) videoInput.addEventListener('change', renderPreview);
});
</script>
@endpush
@endsection
