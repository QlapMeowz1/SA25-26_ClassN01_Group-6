@extends('layout')

@section('title', 'Challenges - BadNet')

@php
    $openCount = $openChallenges->count();
    $receivedPending = $received->where('status', 'pending')->count();
    $sentActive = $sent->whereIn('status', ['open', 'pending'])->count();

    $statusLabel = function ($status) {
        return \Illuminate\Support\Str::headline((string) $status);
    };
@endphp

@section('content')
<div class="page-shell challenge-arena-shell challenge-page">
    <section class="challenge-hero-panel">
        <div class="challenge-hero-copy">
            <p class="home-eyebrow">{{ __('ui.challenge.title') }}</p>
            <h1>{{ __('ui.challenge.title') }}</h1>
            <p class="page-subtitle">{{ __('ui.challenge.subtitle') }}</p>
        </div>

        <div class="challenge-hero-actions">
            <a href="{{ route('challenges.create') }}" class="btn btn-primary">{{ __('ui.challenge.send') }}</a>
            <form action="{{ route('challenges.quick') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-secondary">{{ __('ui.challenge.quick') }}</button>
            </form>
        </div>
    </section>

    <div class="challenge-summary-grid">
        <a href="#open-challenges" class="challenge-summary-card">
            <span>{{ __('ui.challenge.open') }}</span>
            <strong>{{ $openCount }}</strong>
            <small>{{ __('ui.challenge.live_callouts') }}</small>
        </a>
        <a href="#received-challenges" class="challenge-summary-card">
            <span>{{ __('ui.challenge.received') }}</span>
            <strong>{{ $receivedPending }}</strong>
            <small>{{ __('ui.challenge.decide_fast') }}</small>
        </a>
        <a href="#sent-challenges" class="challenge-summary-card">
            <span>{{ __('ui.challenge.sent') }}</span>
            <strong>{{ $sentActive }}</strong>
            <small>{{ __('ui.challenge.queue') }}</small>
        </a>
        <a href="#leaderboard" class="challenge-summary-card">
            <span>{{ __('ui.challenge.leaderboard') }}</span>
            <strong>{{ $leaderboard->count() }}</strong>
            <small>{{ __('ui.challenge.updated_now') }}</small>
        </a>
    </div>

    <nav class="filter-tabs challenge-tabs" aria-label="Challenge sections">
        <a href="#open-challenges" class="filter-tab">{{ __('ui.challenge.open') }}</a>
        <a href="#received-challenges" class="filter-tab">{{ __('ui.challenge.received') }}</a>
        <a href="#sent-challenges" class="filter-tab">{{ __('ui.challenge.sent') }}</a>
        <a href="#leaderboard" class="filter-tab">{{ __('ui.challenge.leaderboard') }}</a>
    </nav>

    <div class="challenge-layout">
        <main class="challenge-main-column">
            <section class="challenge-section challenge-board" id="open-challenges">
                <div class="challenge-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.challenge.open') }}</p>
                        <h2>{{ __('ui.challenge.hot_seats') }}</h2>
                    </div>
                    <span class="feed-live-indicator">{{ __('ui.challenge.live_callouts') }}</span>
                </div>

                @if($openChallenges->isEmpty())
                    <div class="empty-panel challenge-empty-panel">
                        @include('partials.empty-illustration', ['title' => __('ui.challenge.no_pending'), 'message' => __('ui.challenge.create_one')])
                        <a href="{{ route('challenges.create') }}" class="btn btn-primary">{{ __('ui.challenge.create') }}</a>
                    </div>
                @else
                    <div class="challenge-list">
                        @foreach($openChallenges as $challenge)
                            @php
                                $isOwner = auth()->id() === $challenge->challenger_id;
                                $pendingRequests = $challenge->joinRequests->where('status', 'pending');
                            @endphp

                            <article id="challenge-{{ $challenge->id }}" class="challenge-card challenge-card-featured">
                                <div class="challenge-card-top">
                                    <div class="challenge-from">
                                        <span class="challenge-avatar">{{ strtoupper(substr($challenge->challenger->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <span class="challenge-label">{{ __('ui.challenge.open_by') }}</span>
                                            <strong>{{ $challenge->challenger->name }}</strong>
                                        </div>
                                    </div>
                                    <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ $statusLabel($challenge->status) }}</span>
                                </div>

                                <p class="challenge-message challenge-message-strong">{{ $challenge->arena_description }}</p>

                                <div class="challenge-meta-grid">
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.description') }}</span>
                                        <strong>{{ $challenge->arena_priority }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.time_limit') }}</span>
                                        <strong>{{ $challenge->arena_time_limit }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.required_level') }}</span>
                                        <strong>{{ $challenge->arena_required_level }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.location') }}</span>
                                        <strong>{{ $challenge->arena_location }}</strong>
                                    </div>
                                </div>

                                <div class="challenge-countdown">
                                    <span class="challenge-meta-label">{{ __('ui.challenge.countdown_timer') }}</span>
                                    <span class="countdown-pill" data-target="{{ optional($challenge->expires_at)->toIsoString() }}">{{ $challenge->arena_countdown }}</span>
                                </div>

                                <div class="challenge-actions challenge-actions-spread">
                                    @if(!empty($challenge->challenger_id))
                                        <a href="{{ route('profile.show', $challenge->challenger_id) }}" class="btn btn-secondary btn-small">{{ __('ui.challenge.view_player') }}</a>
                                    @endif

                                    @if(!$isOwner && !empty($challenge->challenger_id))
                                        <form action="{{ route('challenges.requestJoin', $challenge->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-small">{{ __('ui.challenge.request_to_join') }}</button>
                                        </form>
                                    @endif
                                </div>

                                @if($isOwner)
                                    @if($pendingRequests->isEmpty())
                                        <div class="empty-inline">{{ __('ui.challenge.no_join_requests') }}</div>
                                    @else
                                        <div class="challenge-request-list">
                                            @foreach($pendingRequests as $joinRequest)
                                                <div class="challenge-request-card">
                                                    <div class="challenge-from">
                                                        <span class="challenge-avatar challenge-avatar-muted">{{ strtoupper(substr($joinRequest->requester->name, 0, 1)) }}</span>
                                                        <div class="min-w-0">
                                                            <span class="challenge-label">{{ __('ui.challenge.request_from') }}</span>
                                                            <strong>{{ $joinRequest->requester->name }}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="challenge-actions">
                                                        <form action="{{ route('challenges.requests.accept', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-small">{{ __('ui.challenge.accept') }}</button>
                                                        </form>
                                                        <form action="{{ route('challenges.requests.reject', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-small">{{ __('ui.challenge.reject') }}</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="challenge-section" id="received-challenges">
                <div class="challenge-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.challenge.received') }}</p>
                        <h2>{{ __('ui.challenge.decide_fast') }}</h2>
                    </div>
                </div>

                @if($received->isEmpty())
                    <div class="empty-panel challenge-empty-panel">
                        @include('partials.empty-illustration', ['title' => __('ui.challenge.no_received'), 'message' => __('ui.challenge.open_invitations')])
                        <a href="{{ route('challenges.create') }}" class="btn btn-primary">{{ __('ui.challenge.create') }}</a>
                    </div>
                @else
                    <div class="challenge-list">
                        @foreach($received as $challenge)
                            <article id="challenge-{{ $challenge->id }}" class="challenge-card">
                                <div class="challenge-card-top">
                                    <div class="challenge-from">
                                        <span class="challenge-avatar">{{ strtoupper(substr($challenge->challenger->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <span class="challenge-label">{{ __('ui.challenge.challenge_from') }}</span>
                                            <strong>{{ $challenge->challenger->name }}</strong>
                                        </div>
                                    </div>
                                    <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ $statusLabel($challenge->status) }}</span>
                                </div>

                                <p class="challenge-message">{{ $challenge->arena_description }}</p>

                                <div class="challenge-meta-grid">
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.time_limit') }}</span>
                                        <strong>{{ $challenge->arena_time_limit }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.required_level') }}</span>
                                        <strong>{{ $challenge->arena_required_level }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.location') }}</span>
                                        <strong>{{ $challenge->arena_location }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.countdown_timer') }}</span>
                                        <strong data-target="{{ optional($challenge->expires_at)->toIsoString() }}">{{ $challenge->arena_countdown }}</strong>
                                    </div>
                                </div>

                                @if($challenge->status === 'pending' && !($challenge->is_sample ?? false))
                                    <div class="challenge-actions challenge-actions-spread">
                                        <form action="{{ route('challenges.accept', $challenge->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-small">{{ __('ui.challenge.accept') }}</button>
                                        </form>
                                        <form action="{{ route('challenges.reject', $challenge->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-small">{{ __('ui.challenge.reject') }}</button>
                                        </form>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="challenge-section" id="sent-challenges">
                <div class="challenge-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.challenge.sent') }}</p>
                        <h2>{{ __('ui.challenge.queue') }}</h2>
                    </div>
                </div>

                @if($sent->isEmpty())
                    <div class="empty-panel challenge-empty-panel">
                        @include('partials.empty-illustration', ['title' => __('ui.challenge.no_sent'), 'message' => __('ui.challenge.queue_empty')])
                        <a href="{{ route('challenges.create') }}" class="btn btn-primary">{{ __('ui.challenge.create') }}</a>
                    </div>
                @else
                    <div class="challenge-list">
                        @foreach($sent as $challenge)
                            @php $pendingRequests = $challenge->joinRequests->where('status', 'pending'); @endphp

                            <article id="challenge-{{ $challenge->id }}" class="challenge-card">
                                <div class="challenge-card-top">
                                    <div class="challenge-from">
                                        <span class="challenge-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                        <div class="min-w-0">
                                            <span class="challenge-label">{{ __('ui.challenge.challenge_to') }}</span>
                                            <strong>{{ $challenge->opponent?->name ?? __('ui.challenge.open') }}</strong>
                                        </div>
                                    </div>
                                    <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ $statusLabel($challenge->status) }}</span>
                                </div>

                                <p class="challenge-message">{{ $challenge->arena_description }}</p>

                                <div class="challenge-meta-grid">
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.time_limit') }}</span>
                                        <strong>{{ $challenge->arena_time_limit }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.required_level') }}</span>
                                        <strong>{{ $challenge->arena_required_level }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.location') }}</span>
                                        <strong>{{ $challenge->arena_location }}</strong>
                                    </div>
                                    <div>
                                        <span class="challenge-meta-label">{{ __('ui.challenge.countdown_timer') }}</span>
                                        <strong data-target="{{ optional($challenge->expires_at)->toIsoString() }}">{{ $challenge->arena_countdown }}</strong>
                                    </div>
                                </div>

                                @if($challenge->status === 'open' && !($challenge->is_sample ?? false))
                                    @if($pendingRequests->isEmpty())
                                        <div class="empty-inline">{{ __('ui.challenge.no_join_requests') }}</div>
                                    @else
                                        <div class="challenge-request-list">
                                            @foreach($pendingRequests as $joinRequest)
                                                <div class="challenge-request-card">
                                                    <div class="challenge-from">
                                                        <span class="challenge-avatar challenge-avatar-muted">{{ strtoupper(substr($joinRequest->requester->name, 0, 1)) }}</span>
                                                        <div class="min-w-0">
                                                            <span class="challenge-label">{{ __('ui.challenge.request_from') }}</span>
                                                            <strong>{{ $joinRequest->requester->name }}</strong>
                                                        </div>
                                                    </div>
                                                    <div class="challenge-actions">
                                                        <form action="{{ route('challenges.requests.accept', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-small">{{ __('ui.challenge.accept') }}</button>
                                                        </form>
                                                        <form action="{{ route('challenges.requests.reject', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-danger btn-small">{{ __('ui.challenge.reject') }}</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>

        <aside class="challenge-side-column">
            <section class="challenge-section leaderboard-panel" id="leaderboard">
                <div class="challenge-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.challenge.leaderboard') }}</p>
                        <h2>{{ __('ui.challenge.top_8_players') ?? 'Top 8 players' }}</h2>
                    </div>
                    <span class="feed-live-count">{{ __('ui.challenge.updated_now') }}</span>
                </div>

                <div class="leaderboard leaderboard-arena">
                    @foreach($leaderboard as $index => $player)
                        <div class="leaderboard-row leaderboard-row-arena">
                            <span class="rank-badge rank-badge-{{ $player->badge_class }}">{{ $index + 1 }}</span>
                            <div class="player-info player-info-stack">
                                <div class="player-name-row">
                                    <span class="player-name">{{ $player->name }}</span>
                                    <span class="rank-tag rank-tag-{{ $player->badge_class }}">{{ $player->rank }}</span>
                                </div>
                                <span class="player-rank">{{ $player->elo_rating }} ELO</span>
                            </div>
                            @if($player->profile_url)
                                <a href="{{ $player->profile_url }}" class="btn btn-small">{{ __('ui.challenge.view') }}</a>
                            @else
                                <a href="{{ route('challenges.create') }}" class="btn btn-small">{{ __('ui.challenge.create') }}</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
