@extends('layout')

@section('title', 'Challenges - BadNet')

@section('content')
<div class="page-shell challenge-arena-shell">
    <div class="challenges-header challenge-hero">
        <div>
            <p class="home-eyebrow">Challenge Arena</p>
            <h1>Challenges</h1>
            <p class="page-subtitle">Call out opponents, review pending requests, and keep the leaderboard pressure on.</p>
        </div>
        <div class="challenge-hero-actions">
            <a href="{{ route('challenges.create') }}" class="btn btn-primary">Send Challenge</a>
            <form action="{{ route('challenges.quick') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-secondary">Quick Challenge</button>
            </form>
        </div>
    </div>

    <div class="filter-tabs">
        <a href="#open-challenges" class="filter-tab">Open Challenges</a>
        <a href="#received-challenges" class="filter-tab">Received Challenges</a>
        <a href="#sent-challenges" class="filter-tab">Sent Challenges</a>
        <a href="#leaderboard" class="filter-tab">Leaderboard</a>
    </div>

    <div class="challenges-grid">
        <section class="challenge-section challenge-board" id="open-challenges">
            <div class="feed-heading feed-heading-prominent">
                <div>
                    <p class="home-eyebrow">Open Challenges</p>
                    <h2>Hot seats</h2>
                </div>
                <span class="feed-live-indicator">Live callouts</span>
            </div>

            @if($openChallenges->isEmpty())
                <div class="empty-panel challenge-empty-panel">
                    @include('partials.empty-illustration', ['title' => 'No pending challenges', 'message' => 'Create one and climb the ranks!'])
                    <a href="{{ route('challenges.create') }}" class="btn btn-primary">Create Challenge</a>
                </div>
            @else
                <div class="challenge-list">
                    @foreach($openChallenges as $challenge)
                        <article class="challenge-card challenge-card-featured">
                            <div class="challenge-card-top">
                                <div class="challenge-from">
                                    <span class="challenge-avatar">{{ strtoupper(substr($challenge->challenger->name, 0, 1)) }}</span>
                                    <div>
                                        <span class="challenge-label">Open challenge by</span>
                                        <strong>{{ $challenge->challenger->name }}</strong>
                                    </div>
                                </div>
                                <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ ucfirst($challenge->status) }}</span>
                            </div>

                            <div class="challenge-message challenge-message-strong">{{ $challenge->arena_description }}</div>

                            <div class="challenge-meta-grid">
                                <div>
                                    <span class="challenge-meta-label">Description</span>
                                    <strong>{{ $challenge->arena_priority }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Time Limit</span>
                                    <strong>{{ $challenge->arena_time_limit }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Required Level</span>
                                    <strong>{{ $challenge->arena_required_level }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Location</span>
                                    <strong>{{ $challenge->arena_location }}</strong>
                                </div>
                            </div>

                            <div class="challenge-countdown">
                                <span class="challenge-meta-label">Countdown Timer</span>
                                <span class="countdown-pill" data-target="{{ optional($challenge->expires_at)->toIsoString() }}">{{ $challenge->arena_countdown }}</span>
                            </div>

                            <div class="challenge-actions challenge-actions-spread">
                                @if(!empty($challenge->challenger_id))
                                    <a href="{{ route('profile.show', $challenge->challenger_id) }}" class="btn btn-secondary btn-small">View Player</a>
                                @else
                                    <span class="btn btn-secondary btn-small" aria-disabled="true">Sample Player</span>
                                @endif
                                @if(auth()->id() !== $challenge->challenger_id && !empty($challenge->challenger_id))
                                    <form action="{{ route('challenges.requestJoin', $challenge->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-small">Request to Join</button>
                                    </form>
                                @endif
                            </div>

                            @if(auth()->id() === $challenge->challenger_id)
                                @if($challenge->joinRequests->where('status', 'pending')->isEmpty())
                                    <div class="empty-inline">No join requests yet.</div>
                                @else
                                    <div class="challenge-request-list">
                                        @foreach($challenge->joinRequests->where('status', 'pending') as $joinRequest)
                                            <div class="challenge-request-card">
                                                <div class="challenge-from">
                                                    <span class="challenge-avatar challenge-avatar-muted">{{ strtoupper(substr($joinRequest->requester->name, 0, 1)) }}</span>
                                                    <div>
                                                        <span class="challenge-label">Request from</span>
                                                        <strong>{{ $joinRequest->requester->name }}</strong>
                                                    </div>
                                                </div>
                                                <div class="challenge-actions">
                                                    <form action="{{ route('challenges.requests.accept', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-small">Accept</button>
                                                    </form>
                                                    <form action="{{ route('challenges.requests.reject', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-small">Reject</button>
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
                    <p class="home-eyebrow">Leaderboard</p>
                    <h2>Top 8 players</h2>
                </div>
                <span class="feed-live-count">Updated now</span>
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
                            <a href="{{ $player->profile_url }}" class="btn btn-small">View</a>
                        @else
                            <a href="{{ route('challenges.create') }}" class="btn btn-small">Challenge</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        <section class="challenge-section" id="received-challenges">
            <div class="feed-heading feed-heading-prominent">
                <div>
                    <p class="home-eyebrow">Received Challenges</p>
                    <h2>Decide fast</h2>
                </div>
            </div>

            @if($received->isEmpty())
                <div class="empty-panel challenge-empty-panel">
                    @include('partials.empty-illustration', ['title' => 'No received challenges', 'message' => 'You have no open invitations at the moment.'])
                    <a href="{{ route('challenges.create') }}" class="btn btn-primary">Create Challenge</a>
                </div>
            @else
                <div class="challenge-list">
                    @foreach($received as $challenge)
                        <article class="challenge-card">
                            <div class="challenge-card-top">
                                <div class="challenge-from">
                                    <span class="challenge-avatar">{{ strtoupper(substr($challenge->challenger->name, 0, 1)) }}</span>
                                    <div>
                                        <span class="challenge-label">Challenge from</span>
                                        <strong>{{ $challenge->challenger->name }}</strong>
                                    </div>
                                </div>
                                <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ ucfirst($challenge->status) }}</span>
                            </div>
                            <div class="challenge-message">{{ $challenge->arena_description }}</div>
                            <div class="challenge-meta-grid">
                                <div>
                                    <span class="challenge-meta-label">Description</span>
                                    <strong>{{ $challenge->arena_priority }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Time Limit</span>
                                    <strong>{{ $challenge->arena_time_limit }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Required Level</span>
                                    <strong>{{ $challenge->arena_required_level }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Location</span>
                                    <strong>{{ $challenge->arena_location }}</strong>
                                </div>
                            </div>
                            <div class="challenge-countdown">
                                <span class="challenge-meta-label">Countdown Timer</span>
                                <span class="countdown-pill" data-target="{{ optional($challenge->expires_at)->toIsoString() }}">{{ $challenge->arena_countdown }}</span>
                            </div>
                            @if($challenge->status === 'pending')
                                <div class="challenge-actions challenge-actions-spread">
                                    <form action="{{ route('challenges.accept', $challenge->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-small">Accept</button>
                                    </form>
                                    <form action="{{ route('challenges.reject', $challenge->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-small">Reject</button>
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
                    <p class="home-eyebrow">Sent Challenges</p>
                    <h2>Your queue</h2>
                </div>
            </div>

            @if($sent->isEmpty())
                <div class="empty-panel challenge-empty-panel">
                    @include('partials.empty-illustration', ['title' => 'No sent challenges', 'message' => 'Your challenge queue is empty. Send one to get started.'])
                    <a href="{{ route('challenges.create') }}" class="btn btn-primary">Create Challenge</a>
                </div>
            @else
                <div class="challenge-list">
                    @foreach($sent as $challenge)
                        <article class="challenge-card">
                            <div class="challenge-card-top">
                                <div class="challenge-from">
                                    <span class="challenge-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                    <div>
                                        <span class="challenge-label">Challenge to</span>
                                        <strong>{{ $challenge->opponent?->name ?? 'ShuttleKing' }} - Pending Response</strong>
                                    </div>
                                </div>
                                <span class="challenge-status badge-{{ strtolower($challenge->status) }}">{{ ucfirst($challenge->status) }}</span>
                            </div>
                            <div class="challenge-message">{{ $challenge->arena_description }}</div>
                            <div class="challenge-meta-grid">
                                <div>
                                    <span class="challenge-meta-label">Description</span>
                                    <strong>{{ $challenge->arena_priority }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Time Limit</span>
                                    <strong>{{ $challenge->arena_time_limit }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Required Level</span>
                                    <strong>{{ $challenge->arena_required_level }}</strong>
                                </div>
                                <div>
                                    <span class="challenge-meta-label">Location</span>
                                    <strong>{{ $challenge->arena_location }}</strong>
                                </div>
                            </div>
                            <div class="challenge-countdown">
                                <span class="challenge-meta-label">Countdown Timer</span>
                                <span class="countdown-pill" data-target="{{ optional($challenge->expires_at)->toIsoString() }}">{{ $challenge->arena_countdown }}</span>
                            </div>
                            @if($challenge->status === 'open')
                                @if($challenge->joinRequests->where('status', 'pending')->isEmpty())
                                    <div class="empty-inline">No join requests yet.</div>
                                @else
                                    <div class="challenge-request-list">
                                        @foreach($challenge->joinRequests->where('status', 'pending') as $joinRequest)
                                            <div class="challenge-request-card">
                                                <div class="challenge-from">
                                                    <span class="challenge-avatar challenge-avatar-muted">{{ strtoupper(substr($joinRequest->requester->name, 0, 1)) }}</span>
                                                    <div>
                                                        <span class="challenge-label">Request from</span>
                                                        <strong>{{ $joinRequest->requester->name }}</strong>
                                                    </div>
                                                </div>
                                                <div class="challenge-actions">
                                                    <form action="{{ route('challenges.requests.accept', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success btn-small">Accept</button>
                                                    </form>
                                                    <form action="{{ route('challenges.requests.reject', [$challenge->id, $joinRequest->id]) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger btn-small">Reject</button>
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
