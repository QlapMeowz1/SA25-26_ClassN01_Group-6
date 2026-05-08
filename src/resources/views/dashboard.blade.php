@extends('layout')

@section('title', 'Dashboard - BadNet')

@section('content')
@php
    $user = auth()->user();
    $rankProgress = $user->getRankPercentage();
    $winRate = $user->getWinRate();
    $winRateLabel = $winRate > 0 ? $winRate . '%' : 'Rookie 🏸';
    $recentConnections = collect($recentMatches)
        ->map(function ($match) use ($user) {
            $opponent = $match->player1_id === $user->id ? $match->player2 : $match->player1;
            return $opponent;
        })
        ->filter()
        ->unique('id')
        ->take(3);
    $newPostCount = $communityPosts
        ->filter(function ($post) {
            return $post->created_at->greaterThan(now()->subHours(3));
        })
        ->count();
@endphp

<div class="dashboard-shell">
    <section class="dashboard-header dashboard-hero">
        <div>
            <p class="home-eyebrow">Meowhunterz HQ</p>
            <h1>Welcome back, {{ $user->name }}!</h1>
            <p>Welcome back! Ready for your next match? Check the feed and keep your BadNet momentum going.</p>
        </div>
        <div class="dashboard-hero-metrics">
            <div class="stat-box">
                <span class="stat-label">Rating</span>
                <span class="stat-value">{{ $user->elo_rating }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Coins</span>
                <span class="stat-value">{{ $user->virtual_coins }}</span>
            </div>
            <div class="stat-box">
                <span class="stat-label">Win Rate</span>
                <span class="stat-value @if($winRate === 0) stat-value-message @endif">{{ $winRateLabel }}</span>
            </div>
        </div>
    </section>

    <div class="dashboard-grid dashboard-grid-three">
        <aside class="dashboard-column dashboard-column-left">
            <section class="dashboard-section">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Quick Actions</p>
                        <h2>Jump in fast</h2>
                    </div>
                </div>

                <div class="action-grid action-grid-featured">
                    <a href="{{ route('matches.create') }}" class="action-card action-card-green">
                        <span class="action-icon">🔍</span>
                        <strong>Find Match</strong>
                        <span>Find a court nearby</span>
                    </a>
                    <a href="{{ route('challenges.create') }}" class="action-card action-card-orange">
                        <span class="action-icon">⚔️</span>
                        <strong>Create Challenge</strong>
                        <span>Challenge an opponent</span>
                    </a>
                    <a href="{{ route('tournaments.index') }}" class="action-card action-card-blue">
                        <span class="action-icon">🏆</span>
                        <strong>Join Tournament</strong>
                        <span>Enter a tournament</span>
                    </a>
                </div>
            </section>

            <section class="dashboard-section profile-summary">
                <div class="profile-summary-top">
                    @if($user->avatar)
                        <img src="{{ asset('avatars/' . $user->avatar) }}" alt="{{ $user->name }}" class="profile-summary-avatar">
                    @else
                        <div class="profile-summary-avatar profile-summary-avatar-fallback">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                    <div>
                        <p class="home-eyebrow">Active Player</p>
                        <h2>{{ $user->name }}</h2>
                        <span class="rank-tag rank-tag-strong">{{ $user->rank }}</span>
                    </div>
                </div>

                <div class="profile-progress">
                    <div class="progress-labels">
                        <span>ELO toward next rank</span>
                        <span>{{ $rankProgress }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ $rankProgress }}%;"></div>
                    </div>
                </div>

                <div class="profile-summary-stats">
                    <div>
                        <span class="profile-summary-label">Rating</span>
                        <strong>{{ $user->elo_rating }}</strong>
                    </div>
                    <div>
                        <span class="profile-summary-label">Coins</span>
                        <strong>{{ $user->virtual_coins }}</strong>
                    </div>
                    <div>
                        <span class="profile-summary-label">Record</span>
                        <strong>{{ $user->wins }}/{{ $user->losses }}</strong>
                    </div>
                    <div>
                        <span class="profile-summary-label">Win Rate</span>
                        <strong class="@if($winRate === 0) stat-value-message @endif" title="Play your first match to establish your ranking!">{{ $winRateLabel }}</strong>
                    </div>
                </div>
            </section>

            <section class="dashboard-section">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Active Players</p>
                        <h2>Recent Sparring Partners</h2>
                    </div>
                </div>

                @if($recentConnections->isEmpty())
                    <div class="empty-panel">
                        <p class="empty-message">Invite friends to BadNet to see who’s online.</p>
                        <a href="{{ route('teams.index') }}" class="btn btn-secondary btn-block">Invite friends to BadNet</a>
                    </div>
                @else
                    <div class="people-list">
                        @foreach($recentConnections as $connection)
                            <div class="people-row">
                                <div class="people-avatar">{{ strtoupper(substr($connection->name, 0, 1)) }}</div>
                                <div>
                                    <strong>{{ $connection->name }}</strong>
                                    <p>{{ $connection->rank }} · {{ $connection->elo_rating }} ELO</p>
                                </div>
                                <span class="online-dot"></span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>

        <section class="dashboard-column dashboard-column-center">
            <section class="dashboard-section feed-composer feed-composer-featured" id="status-update">
                <div class="feed-heading feed-heading-prominent">
                    <div>
                        <p class="home-eyebrow">Status Update</p>
                        <h2>Tell the community what happened on court</h2>
                    </div>
                </div>

                <form action="{{ route('posts.store') }}" method="POST" class="composer-box composer-box-featured">
                    @csrf
                    <textarea name="content" rows="6" maxlength="500" placeholder="Share a match recap, tag teammates, or post a highlight from the court."></textarea>
                    <div class="composer-actions">
                        <div class="composer-tools">
                            <span class="composer-pill">Attach image</span>
                            <span class="composer-pill">Attach video</span>
                            <span class="composer-pill">Tag teammates</span>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Update</button>
                    </div>
                </form>
            </section>

            @if($openMatches->isNotEmpty())
                <section class="dashboard-section">
                    <div class="feed-heading system-feed-heading">
                        <div>
                            <p class="home-eyebrow">System Updates</p>
                            <h2>Open court alerts</h2>
                        </div>
                    </div>
                    <div class="posts-feed system-feed">
                        @foreach($openMatches as $match)
                            <article class="post-card feed-card system-card">
                                <div class="post-header">
                                    <div class="post-author">
                                        <div class="author-avatar author-avatar-system">🏸</div>
                                        <div class="author-info">
                                            <span class="author-name">BadNet System</span>
                                            <span class="post-time">{{ $match->match_date->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="post-content">
                                    {{ $match->player1->name }} opened a match at {{ $match->location ?? 'a nearby court' }} for {{ $match->match_date->format('M d, Y h:i A') }}.
                                </div>
                                <div class="post-actions">
                                    <a href="{{ route('matches.show', $match->id) }}" class="action-btn">View Match</a>
                                    <a href="{{ route('matches.index') }}#open-matches" class="action-btn">Join Queue</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="dashboard-section dashboard-section-feed" id="live-feed">
                <div class="feed-heading feed-heading-prominent">
                    <div>
                        <p class="home-eyebrow">Live Feed</p>
                        <h2>Community posts in real time</h2>
                    </div>
                    <div class="feed-live-meta">
                        <span class="feed-live-indicator">
                            <span class="feed-live-dot"></span>
                            Live now
                        </span>
                        @if($newPostCount > 0)
                            <span class="feed-live-count">{{ $newPostCount }} new post{{ $newPostCount === 1 ? '' : 's' }}</span>
                        @endif
                    </div>
                </div>

                @if($communityPosts->isEmpty())
                    <div class="post-card feed-empty">
                        <h3>No posts yet</h3>
                        <p>Be the first to share a result, a photo, or a doubles callout.</p>
                    </div>
                @else
                    <div class="posts-feed">
                        @foreach($communityPosts as $post)
                            <article class="post-card feed-card feed-card-live">
                                <div class="post-header">
                                    <div class="post-author">
                                        <a href="{{ route('profile.show', $post->user->id) }}" class="author-avatar">
                                            {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                        </a>
                                        <div class="author-info">
                                            <a href="{{ route('profile.show', $post->user->id) }}" class="author-name">
                                                {{ $post->user->name }}
                                            </a>
                                            <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    @if($post->created_at->greaterThan(now()->subHours(3)))
                                        <span class="post-badge post-badge-new">New</span>
                                    @endif
                                </div>

                                <div class="post-content">
                                    {{ $post->content }}
                                </div>

                                <div class="post-stats">
                                    <span>❤️ {{ $post->likes_count }} likes</span>
                                    <span>💬 {{ $post->comments->count() }} comments</span>
                                </div>

                                <div class="post-actions">
                                    <a href="{{ route('posts.show', $post->id) }}" class="action-btn">Like</a>
                                    <a href="{{ route('posts.show', $post->id) }}" class="action-btn">Comment</a>
                                    <a href="{{ route('posts.show', $post->id) }}" class="action-btn">Share</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </section>

        <aside class="dashboard-column dashboard-column-right">
            <section class="dashboard-section">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Leaderboard</p>
                        <h2>Top 10 players</h2>
                    </div>
                    <a href="{{ route('challenges.index') }}#leaderboard" class="feed-link">View Full Leaderboard</a>
                </div>

                <div class="leaderboard leaderboard-compact">
                    @foreach($leaderboard as $index => $player)
                        <div class="leaderboard-row leaderboard-row-compact">
                            <span class="rank-badge @if($index === 0) rank-gold @elseif($index === 1) rank-silver @elseif($index === 2) rank-bronze @endif">{{ $index + 1 }}</span>
                            <a href="{{ route('profile.show', $player->id) }}" class="player-name">
                                {{ $player->name }}
                            </a>
                            <span class="rating">{{ $player->elo_rating }}</span>
                            <span class="rank-tag">{{ $player->rank }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="dashboard-section">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">My Upcoming Matches</p>
                        <h2>Tickets on deck</h2>
                    </div>
                </div>

                @if($upcomingMatches->isEmpty())
                    <div class="empty-panel">
                        <p class="empty-message">You have no upcoming matches yet.</p>
                        <a href="{{ route('challenges.create') }}" class="btn btn-primary btn-block">Create a challenge</a>
                    </div>
                @else
                    <div class="ticket-list">
                        @foreach($upcomingMatches as $match)
                            <article class="match-ticket">
                                <div class="ticket-top">
                                    <span class="match-status-pill">{{ ucfirst($match->status) }}</span>
                                    <span class="ticket-time">{{ $match->match_date->diffForHumans() }}</span>
                                </div>
                                <h3>{{ $match->player2?->name ?? 'Waiting for player' }}</h3>
                                <p>{{ $match->match_date->format('M d, Y h:i A') }}</p>
                                <p>{{ $match->location ?? 'Court TBD' }}</p>
                                <a href="{{ route('matches.show', $match->id) }}" class="btn btn-secondary btn-block">View Match</a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="dashboard-section" id="notifications">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Recent Matches</p>
                        <h2>Last results</h2>
                    </div>
                </div>

                @if($recentMatches->isEmpty())
                    <div class="empty-panel">
                        <p class="empty-message">Play your first match!</p>
                    </div>
                @else
                    <div class="match-list">
                        @foreach($recentMatches as $match)
                            <article class="match-card completed">
                                <div class="match-players">
                                    {{ $match->player1->name }} ({{ $match->player1_score }})
                                    <span class="vs">vs</span>
                                    {{ $match->player2?->name ?? 'Waiting for player' }} ({{ $match->player2_score }})
                                </div>
                                <div class="match-winner">
                                    Winner: {{ $match->winner?->name ?? 'TBD' }}
                                </div>
                                <a href="{{ route('matches.show', $match->id) }}" class="btn btn-small">View</a>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </div>

    <a href="#status-update" class="create-post-fab" aria-label="Create a post">
        <span class="create-post-fab-icon">✍️</span>
        <span>Create Post</span>
    </a>
</div>
@endsection
