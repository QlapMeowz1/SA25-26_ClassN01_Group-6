@extends('layout')

@section('title', 'Dashboard - BadNet')

@section('content')
@php
    $user = auth()->user();
    $winRate = $user->getWinRate();
    $winRateLabel = $winRate > 0 ? $winRate . '%' : 'Rookie 🏸';
    $newPostCount = $communityPosts
        ->filter(function ($post) {
            return $post->created_at->greaterThan(now()->subHours(3));
        })
        ->count();
@endphp

<div class="min-h-screen bg-gradient-to-br from-lime-50 via-white to-orange-50 px-4 py-6 text-slate-900 transition-colors duration-300 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950 sm:px-6 lg:px-8">
    <div class="mx-auto flex max-w-7xl flex-col gap-6 font-body">
        <section class="overflow-hidden rounded-3xl border border-emerald-200 bg-white p-6 shadow-md dark:border-emerald-700 dark:bg-zinc-900 dark:shadow-2xl sm:p-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl space-y-3">
                    <p class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
                        <span>🏸</span> {{ __('ui.dashboard.live') }} · {{ __('ui.dashboard.community_feed') }}
                    </p>
                    <h2 class="font-heading text-3xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-4xl lg:text-5xl">
                        <span class="mr-2 text-amber-500">✨</span>{{ __('ui.dashboard.welcome_back', ['name' => $user->name]) }}
                    </h2>
                    <p class="max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300 sm:text-lg">
                        {{ __('ui.dashboard.subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-3 sm:min-w-[320px]">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center shadow-sm dark:border-emerald-700 dark:bg-emerald-900/20">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700 dark:text-emerald-300">Win Rate</div>
                        <div class="mt-1 font-heading text-2xl font-bold text-slate-950 dark:text-white">{{ $winRateLabel }}</div>
                    </div>
                    <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-center shadow-sm dark:border-orange-700 dark:bg-orange-900/20">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-orange-700 dark:text-orange-300">ELO</div>
                        <div class="mt-1 font-heading text-2xl font-bold text-slate-950 dark:text-white">{{ $user->elo_rating }}</div>
                    </div>
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-center shadow-sm dark:border-amber-700 dark:bg-amber-900/20">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700 dark:text-amber-300">Posts</div>
                        <div class="mt-1 font-heading text-2xl font-bold text-slate-950 dark:text-white">{{ $communityPosts->total() }}</div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[300px_minmax(0,1fr)_320px]">
            <aside class="flex flex-col gap-6">
                <section class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-md transition-transform duration-300 hover:-translate-y-1 dark:border-emerald-700 dark:bg-zinc-900 dark:shadow-2xl">
                    <div class="flex flex-col items-center gap-4 text-center">
                        <div class="relative">
                            <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-court via-emerald-700 to-energy text-3xl font-bold text-white shadow-[0_18px_40px_rgba(10,92,10,0.28)] ring-8 ring-amber-100/80 dark:border-slate-900 dark:ring-emerald-500/10">
                                @if($user->avatar)
                                    <img src="{{ asset('avatars/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-full w-full rounded-full object-cover">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                @endif
                            </div>
                            <span class="absolute -right-1 bottom-1 inline-flex h-5 w-5 items-center justify-center rounded-full border-2 border-white bg-emerald-500 shadow dark:border-slate-900">
                                <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            </span>
                        </div>

                        <div>
                            <h3 class="font-heading text-2xl font-extrabold text-slate-950 dark:text-white">{{ $user->name }}</h3>
                            <div class="mt-2 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                                <span>🔥</span> {{ $user->rank }}
                            </div>
                        </div>

                        <div class="flex w-full items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600 dark:bg-zinc-900 dark:text-slate-300">
                            <span class="font-semibold">ELO Rating</span>
                            <span class="font-heading text-lg font-bold text-slate-950 dark:text-white">{{ $user->elo_rating }}</span>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-md transition-transform duration-300 hover:-translate-y-1 dark:border-emerald-700 dark:bg-zinc-900 dark:shadow-2xl">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">●</span>
                        <div>
                            <h4 class="font-heading text-xl font-bold text-slate-950 dark:text-white">{{ __('ui.dashboard.online_now') }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('ui.dashboard.no_friends_online') }}</p>
                        </div>
                    </div>

                    @if($onlinePlayers->isEmpty())
                        <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-700 dark:bg-emerald-900/20">
                            <p class="mb-4 text-sm leading-6 text-slate-600 dark:text-slate-300">{{ __('ui.dashboard.no_friends_online') }}</p>
                            <a href="{{ route('teams.index') }}" class="inline-flex items-center justify-center rounded-full bg-energy px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-orange-500/25 transition hover:-translate-y-0.5 hover:bg-orange-500">
                                {{ __('ui.dashboard.invite_friends') }}
                            </a>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($onlinePlayers->take(8) as $player)
                                <a href="{{ route('profile.show', $player->id) }}" class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-3 transition duration-300 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-white hover:shadow-lg dark:border-slate-700 dark:bg-zinc-900 dark:hover:border-emerald-700 dark:hover:bg-zinc-800">
                                    <div class="relative flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-court font-semibold text-white ring-4 ring-emerald-100 transition group-hover:scale-105 dark:ring-emerald-500/10">
                                        {{ strtoupper(substr($player->name, 0, 1)) }}
                                        <span class="absolute -right-0.5 -bottom-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate font-heading text-sm font-semibold text-slate-950 dark:text-white">{{ $player->name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $player->rank }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>
            </aside>

            <section class="flex flex-col gap-6">
                <section id="status-update" class="overflow-hidden rounded-3xl border border-orange-200 bg-white p-5 shadow-md transition-transform duration-300 hover:-translate-y-1 dark:border-orange-700 dark:bg-zinc-900 dark:shadow-2xl">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-energy to-amber-500 font-heading text-xl font-bold text-white shadow-lg shadow-orange-500/25">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <form action="{{ route('posts.store') }}" method="POST" class="min-w-0 flex-1" enctype="multipart/form-data">
                            @csrf
                            <textarea
                                name="content"
                                rows="3"
                                maxlength="1000"
                                placeholder="{{ __('ui.dashboard.share_match_story', ['name' => $user->name]) }}"
                                class="w-full rounded-3xl border border-slate-200 bg-white px-4 py-4 font-body text-sm leading-7 text-slate-900 placeholder:text-slate-400 shadow-sm outline-none transition focus:border-energy focus:ring-4 focus:ring-orange-200/70 dark:border-slate-700 dark:bg-zinc-900 dark:text-white dark:placeholder:text-slate-400 dark:focus:ring-orange-500/20"
                            ></textarea>

                            <div class="mt-4 flex flex-wrap gap-3">
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 transition hover:-translate-y-0.5 hover:bg-emerald-100 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300 dark:hover:bg-emerald-900/30" for="post-images-input">
                                    <input type="file" id="post-images-input" name="images[]" accept="image/*" multiple hidden>
                                    <span>🖼️</span> {{ __('ui.dashboard.image') }}
                                </label>
                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-800 transition hover:-translate-y-0.5 hover:bg-orange-100 dark:border-orange-700 dark:bg-orange-900/20 dark:text-orange-300 dark:hover:bg-orange-900/30" for="post-videos-input">
                                    <input type="file" id="post-videos-input" name="videos[]" accept="video/*" multiple hidden>
                                    <span>🎥</span> {{ __('ui.dashboard.video') }}
                                </label>
                            </div>

                            <div class="composer-media-preview mt-4 flex flex-wrap gap-3" id="composer-media-preview" aria-live="polite"></div>

                            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <button type="button" class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 transition hover:-translate-y-0.5 hover:shadow-md dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300" title="{{ __('ui.dashboard.tag') }} teammates">
                                    <span>@</span> {{ __('ui.dashboard.tag') }}
                                </button>

                                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-energy px-5 py-3 font-heading text-sm font-bold text-white shadow-lg shadow-orange-500/25 transition hover:-translate-y-0.5 hover:bg-orange-500 hover:shadow-orange-500/30">
                                    {{ __('ui.dashboard.post_update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <section id="live-feed" class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-md dark:border-emerald-700 dark:bg-zinc-900 dark:shadow-2xl">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                                <span>🔥</span> {{ __('ui.dashboard.community_feed') }}
                            </div>
                            <h3 class="mt-3 font-heading text-2xl font-extrabold text-slate-950 dark:text-white">{{ __('ui.dashboard.community_feed') }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> {{ __('ui.dashboard.live') }}
                            </span>
                            @if($newPostCount > 0)
                                <span class="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">{{ $newPostCount }} {{ __('ui.dashboard.new') }}</span>
                            @endif
                        </div>
                    </div>

                    @if($communityPosts->isEmpty())
                        <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center dark:border-slate-700 dark:bg-zinc-900">
                            <h3 class="font-heading text-xl font-bold text-slate-950 dark:text-white">{{ __('ui.dashboard.no_posts_title') }}</h3>
                            <p class="mt-2 text-slate-600 dark:text-slate-300">{{ __('ui.dashboard.no_posts_body') }}</p>
                        </div>
                    @else
                        <div class="posts-feed flex flex-col gap-4" id="posts-feed">
                            @foreach($communityPosts as $post)
                                @include('partials.post_card', ['post' => $post])
                            @endforeach
                        </div>

                        <div id="infinite-loader" class="mt-6 text-center" data-next-page="{{ $communityPosts->currentPage() + 1 }}" data-has-more="{{ $communityPosts->hasMorePages() ? '1' : '0' }}">
                            <button id="load-more-btn" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-3 font-heading text-sm font-bold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 dark:border-slate-700 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800">
                                {{ __('ui.dashboard.load_more') }}
                            </button>
                            <div id="loader-spinner" class="mt-3 hidden text-sm text-slate-500 dark:text-slate-400">{{ __('ui.dashboard.loading') }}</div>
                        </div>

                        <script>
                        (function(){
                            const loader = document.getElementById('infinite-loader');
                            const feed = document.getElementById('posts-feed');
                            const loadMoreBtn = document.getElementById('load-more-btn');
                            const spinner = document.getElementById('loader-spinner');
                            let isLoading = false;

                            function fetchPage(page){
                                if (isLoading) return;
                                isLoading = true;
                                spinner.classList.remove('hidden');
                                const url = '{{ route('posts.loadMore') }}' + '?page=' + page;
                                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.html) {
                                            const tmp = document.createElement('div');
                                            tmp.innerHTML = data.html;
                                            while (tmp.firstChild) feed.appendChild(tmp.firstChild);
                                        }
                                        loader.setAttribute('data-has-more', data.hasMore ? '1' : '0');
                                        loader.setAttribute('data-next-page', data.nextPage);
                                    })
                                    .catch(console.error)
                                    .finally(()=>{ isLoading = false; spinner.classList.add('hidden'); });
                            }

                            loadMoreBtn.addEventListener('click', function(){
                                const next = parseInt(loader.getAttribute('data-next-page')) || 2;
                                fetchPage(next);
                            });

                            window.addEventListener('scroll', function(){
                                if (isLoading) return;
                                const rect = loader.getBoundingClientRect();
                                if (rect.top < window.innerHeight + 200 && loader.getAttribute('data-has-more') === '1') {
                                    const next = parseInt(loader.getAttribute('data-next-page')) || 2;
                                    fetchPage(next);
                                }
                            });
                        })();
                        </script>
                    @endif
                </section>
            </section>

            <aside class="flex flex-col gap-6">
                <section class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-md transition-transform duration-300 hover:-translate-y-1 dark:border-emerald-700 dark:bg-zinc-900 dark:shadow-2xl">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="font-heading text-xl font-bold text-slate-950 dark:text-white">{{ __('ui.dashboard.your_matches') }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('ui.dashboard.no_upcoming_matches') }}</p>
                        </div>
                        <span class="text-2xl">📅</span>
                    </div>

                    @if($upcomingMatches->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-zinc-900">
                            <p class="mb-4 text-sm text-slate-600 dark:text-slate-300">{{ __('ui.dashboard.no_upcoming_matches') }}</p>
                            <a href="{{ route('challenges.create') }}" class="inline-flex items-center justify-center rounded-full bg-court px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-900/15 transition hover:-translate-y-0.5 hover:bg-emerald-800">
                                {{ __('ui.dashboard.find_match') }}
                            </a>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($upcomingMatches->take(2) as $match)
                                @php
                                    $statusTone = $match->status === 'in_progress'
                                        ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300';
                                    $statusDot = $match->status === 'in_progress' ? 'bg-amber-500' : 'bg-emerald-500';
                                @endphp
                                <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-zinc-900">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border {{ $statusTone }} text-xs font-bold uppercase tracking-wider">vs</span>
                                            <div>
                                                <div class="font-heading text-sm font-semibold text-slate-950 dark:text-white">
                                                    @if($match->player2)
                                                        {{ $match->player2->name }}
                                                    @else
                                                        {{ __('ui.dashboard.waiting') }}
                                                    @endif
                                                </div>
                                                <span class="mt-1 inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusTone }}">
                                                    <span class="h-2 w-2 rounded-full {{ $statusDot }}"></span>
                                                    {{ ucfirst($match->status) }}
                                                </span>
                                            </div>
                                        </div>

                                        <a href="{{ route('matches.show', $match->id) }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-900 transition hover:-translate-y-0.5 hover:bg-slate-50 dark:border-slate-700 dark:bg-zinc-900 dark:text-white dark:hover:bg-zinc-800">
                                            {{ __('ui.dashboard.view') }}
                                        </a>
                                    </div>

                                    <div class="mt-4 grid gap-2 text-sm text-slate-600 dark:text-slate-300">
                                        <div class="flex items-center gap-2"><span>🕒</span> {{ $match->match_date->format('M d, h:i A') }}</div>
                                        <div class="flex items-center gap-2"><span>📍</span> {{ $match->location ?? __('ui.dashboard.tbd') }}</div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @if($upcomingMatches->count() > 2)
                            <a href="{{ route('matches.index') }}" class="mt-4 inline-flex text-sm font-semibold text-energy transition hover:translate-x-0.5 hover:text-orange-600">
                                {{ __('ui.dashboard.view_all_matches') }} →
                            </a>
                        @endif
                    @endif
                </section>

                <section class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-md transition-transform duration-300 hover:-translate-y-1 dark:border-emerald-700 dark:bg-zinc-900 dark:shadow-2xl">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h4 class="font-heading text-xl font-bold text-slate-950 dark:text-white">{{ __('ui.dashboard.top_players') }}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('ui.dashboard.view_full_leaderboard') }}</p>
                        </div>
                        <span class="text-2xl">🏆</span>
                    </div>

                    <div class="space-y-3">
                        @foreach($leaderboard->take(5) as $index => $player)
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-3 py-3 transition hover:-translate-y-0.5 hover:border-amber-200 hover:bg-white hover:shadow-lg dark:border-slate-700 dark:bg-zinc-900">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full font-heading text-sm font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300' : ($index === 1 ? 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-200' : ($index === 2 ? 'bg-orange-100 text-orange-800 dark:bg-orange-500/20 dark:text-orange-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300')) }}">
                                    @if($index === 0)
                                        👑
                                    @elseif($index === 1)
                                        🥈
                                    @elseif($index === 2)
                                        🥉
                                    @else
                                        #{{ $index + 1 }}
                                    @endif
                                </span>

                                <a href="{{ route('profile.show', $player->id) }}" class="min-w-0 flex-1 truncate font-heading text-sm font-semibold text-slate-950 transition hover:text-energy dark:text-white">
                                    {{ $player->name }}
                                </a>

                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    {{ $player->elo_rating }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <a href="{{ route('challenges.index') }}" class="mt-4 inline-flex text-sm font-semibold text-energy transition hover:translate-x-0.5 hover:text-orange-600">
                        {{ __('ui.dashboard.view_full_leaderboard') }} →
                    </a>
                </section>
            </aside>
        </div>
    </div>

    <div class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3 lg:hidden">
        <button id="fabMain" class="flex h-14 w-14 items-center justify-center rounded-full bg-energy text-xl text-white shadow-[0_18px_36px_rgba(255,98,0,0.35)] transition hover:scale-105" aria-label="{{ __('ui.dashboard.quick_actions') }}" title="{{ __('ui.dashboard.quick_actions') }}">
            ➕
        </button>
        <div id="fabOptions" class="hidden flex-col gap-2 pb-2">
            <a href="#status-update" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg transition hover:-translate-y-0.5 dark:bg-slate-800 dark:text-white">
                <span>✍️</span> {{ __('ui.dashboard.create_post') }}
            </a>
            <a href="{{ route('matches.index') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg transition hover:-translate-y-0.5 dark:bg-slate-800 dark:text-white">
                <span>🔍</span> {{ __('ui.dashboard.match') }}
            </a>
            <a href="{{ route('challenges.create') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-3 text-sm font-semibold text-slate-900 shadow-lg transition hover:-translate-y-0.5 dark:bg-slate-800 dark:text-white">
                <span>⚔️</span> {{ __('ui.dashboard.challenge') }}
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fabMain = document.getElementById('fabMain');
    const fabOptions = document.getElementById('fabOptions');
    const imageInput = document.getElementById('post-images-input');
    const videoInput = document.getElementById('post-videos-input');
    const preview = document.getElementById('composer-media-preview');

    if (fabMain && fabOptions) {
        fabMain.addEventListener('click', function() {
            fabOptions.classList.toggle('hidden');
            fabMain.classList.toggle('rotate-45');
        });
    }

    function renderPreview() {
        if (!preview) return;

        const imageFiles = imageInput ? Array.from(imageInput.files || []) : [];
        const videoFiles = videoInput ? Array.from(videoInput.files || []) : [];

        if (!imageFiles.length && !videoFiles.length) {
            preview.innerHTML = '';
            return;
        }

        const cards = [];

        imageFiles.forEach((file) => {
            const url = URL.createObjectURL(file);
            cards.push(`<div class="h-20 w-20 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-800"><img src="${url}" alt="Selected image" class="h-full w-full object-cover" /></div>`);
        });

        videoFiles.forEach((file) => {
            const url = URL.createObjectURL(file);
            cards.push(`<div class="h-20 w-20 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-800"><video src="${url}" muted playsinline class="h-full w-full object-cover"></video></div>`);
        });

        preview.innerHTML = cards.join('');
    }

    if (imageInput) imageInput.addEventListener('change', renderPreview);
    if (videoInput) videoInput.addEventListener('change', renderPreview);
});
</script>

@endsection
