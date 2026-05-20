@extends('layout')

@section('title', 'Challenges - BadNet')

@section('content')
<div class="page-shell challenge-arena-shell">
    <div class="challenges-header challenge-hero">
        <div>
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
    </div>

    <div class="filter-tabs">
        <a href="#open-challenges" class="filter-tab">{{ __('ui.challenge.open') }}</a>
        <a href="#received-challenges" class="filter-tab">{{ __('ui.challenge.received') }}</a>
        <a href="#sent-challenges" class="filter-tab">{{ __('ui.challenge.sent') }}</a>
        <a href="#leaderboard" class="filter-tab">{{ __('ui.challenge.leaderboard') }}</a>
    </div>

    <div class="challenges-grid">
        <section class="challenge-section challenge-board" id="open-challenges">
            <div class="feed-heading feed-heading-prominent">
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
                        <article class="challenge-card challenge-card-featured">
                            <div class="challenge-card-top">
                                <div class="challenge-from">
                                    <span class="challenge-avatar">{{ strtoupper(substr($challenge->challenger->name, 0, 1)) }}</span>
                                    <div>
                                        <span class="challenge-label">{{ __('ui.challenge.open_by') }}</span>
                                        <strong>{{ $challenge->challenger->name }}</strong>
                                    </div>
                                </div>
                                <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ ucfirst($challenge->status) }}</span>
                            </div>

                            <div class="challenge-message challenge-message-strong">{{ $challenge->arena_description }}</div>

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
                                @else
                                    <span class="btn btn-secondary btn-small" aria-disabled="true">{{ __('ui.challenge.sample_player') }}</span>
                                @endif
                                @if(auth()->id() !== $challenge->challenger_id && !empty($challenge->challenger_id))
                                    <form action="{{ route('challenges.requestJoin', $challenge->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-small">{{ __('ui.challenge.request_to_join') }}</button>
                                    </form>
                                @endif
                            </div>

                            @if(auth()->id() === $challenge->challenger_id)
                                @if($challenge->joinRequests->where('status', 'pending')->isEmpty())
                                    <div class="empty-inline">{{ __('ui.challenge.no_join_requests') }}</div>
                                @else
                                    <div class="challenge-request-list">
                                        @foreach($challenge->joinRequests->where('status', 'pending') as $joinRequest)
                                            <div class="challenge-request-card">
                                                <div class="challenge-from">
                                                    <span class="challenge-avatar challenge-avatar-muted">{{ strtoupper(substr($joinRequest->requester->name, 0, 1)) }}</span>
                                                    <div>
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

        <section class="challenge-section leaderboard-panel" id="leaderboard">
            <div class="feed-heading feed-heading-prominent">
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
                        <span class="rating">{{ $player->elo_rating }}</span>
                        @if($player->profile_url)
                            <a href="{{ $player->profile_url }}" class="btn btn-small">{{ __('ui.challenge.view') }}</a>
                        @else
                            <a href="{{ route('challenges.create') }}" class="btn btn-small">{{ __('ui.challenge.create') }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="challenge-section" id="received-challenges">
            <div class="feed-heading feed-heading-prominent">
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
                        <article class="challenge-card">
                            <div class="challenge-card-top">
                                <div class="challenge-from">
                                    <span class="challenge-avatar">{{ strtoupper(substr($challenge->challenger->name, 0, 1)) }}</span>
                                    <div>
                                        <span class="challenge-label">{{ __('ui.challenge.challenge_from') }}</span>
                                        <strong>{{ $challenge->challenger->name }}</strong>
                                    </div>
                                </div>
                                <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ ucfirst($challenge->status) }}</span>
                            </div>
                            <div class="challenge-message">{{ $challenge->arena_description }}</div>
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
                            @if($challenge->status === 'pending')
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
            <div class="feed-heading feed-heading-prominent">
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
                        <article class="challenge-card">
                            <div class="challenge-card-top">
                                <div class="challenge-from">
                                    <span class="challenge-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                    <div>
                                        <span class="challenge-label">{{ __('ui.challenge.challenge_to') }}</span>
                                        <strong>{{ $challenge->opponent?->name ?? 'ShuttleKing' }} - {{ __('ui.challenge.pending_response') }}</strong>
                                    </div>
                                </div>
                                <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ ucfirst($challenge->status) }}</span>
                            </div>
                            <div class="challenge-message">{{ $challenge->arena_description }}</div>
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
                            @if($challenge->status === 'open')
                                @if($challenge->joinRequests->where('status', 'pending')->isEmpty())
                                    <div class="empty-inline">{{ __('ui.challenge.no_join_requests') }}</div>
                                @else
                                    <div class="challenge-request-list">
                                        @foreach($challenge->joinRequests->where('status', 'pending') as $joinRequest)
                                            <div class="challenge-request-card">
                                                <div class="challenge-from">
                                                    <span class="challenge-avatar challenge-avatar-muted">{{ strtoupper(substr($joinRequest->requester->name, 0, 1)) }}</span>
                                                    <div>
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
    </div>
</div>
@endsection
