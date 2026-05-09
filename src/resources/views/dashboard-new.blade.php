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

<div class="dashboard-shell">
    <!-- Minimal Header -->
    <section class="dashboard-header dashboard-header-minimal">
        <div>
            <h2 class="greeting-text">Welcome back, {{ $user->name }}!</h2>
            <p class="greeting-subtitle">Here's what's happening in your badminton community</p>
        </div>
    </section>

    <!-- Three-Column Layout -->
    <div class="dashboard-grid dashboard-grid-three">
        
        <!-- LEFT SIDEBAR: User Card + Online Friends -->
        <aside class="dashboard-column dashboard-column-left">
            
            <!-- Compact User Card -->
            <section class="dashboard-section compact-user-card">
                <div class="user-card-top">
                    <div class="user-card-avatar">
                        @if($user->avatar)
                            <img src="{{ asset('avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                </div>
                <h3 class="user-card-name">{{ $user->name }}</h3>
                <span class="rank-badge rank-badge-compact">{{ $user->rank }}</span>
                <div class="user-card-elo">{{ $user->elo_rating }} ELO</div>
            </section>

            <!-- Online Friends Section -->
            <section class="dashboard-section online-friends-section">
                <div class="section-header-compact">
                    <span class="online-pulse-dot"></span>
                    <h4>ONLINE NOW</h4>
                </div>

                @if($onlinePlayers->isEmpty())
                    <div class="empty-state-compact">
                        <p>No friends online yet.</p>
                        <a href="{{ route('teams.index') }}" class="btn btn-secondary btn-compact">Invite friends</a>
                    </div>
                @else
                    <div class="online-players-list">
                        @foreach($onlinePlayers->take(8) as $player)
                            <a href="{{ route('profile.show', $player->id) }}" class="online-player-row">
                                <div class="online-player-avatar">
                                    {{ strtoupper(substr($player->name, 0, 1)) }}
                                    <span class="online-indicator"></span>
                                </div>
                                <div class="online-player-info">
                                    <span class="online-player-name">{{ $player->name }}</span>
                                    <span class="online-player-rank">{{ $player->rank }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>

        </aside>

        <!-- CENTER: Status Composer + Community Feed -->
        <section class="dashboard-column dashboard-column-center">
            
            <!-- Status Composer -->
            <section class="dashboard-section feed-composer feed-composer-featured" id="status-update">
                <div class="composer-top">
                    <div class="composer-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <form action="{{ route('posts.store') }}" method="POST" class="composer-form">
                        @csrf
                        <textarea 
                            name="content" 
                            rows="3" 
                            maxlength="500" 
                            placeholder="Share your match story, {{ $user->name }}..."
                            class="composer-input"></textarea>
                        <div class="composer-actions">
                            <div class="composer-tools">
                                <button type="button" class="composer-tool" title="Attach image">
                                    <span>🖼️</span> Image
                                </button>
                                <button type="button" class="composer-tool" title="Attach video">
                                    <span>🎥</span> Video
                                </button>
                                <button type="button" class="composer-tool" title="Tag teammates">
                                    <span>@</span> Tag
                                </button>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Update</button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Community Feed -->
            <section class="dashboard-section dashboard-section-feed" id="live-feed">
                <div class="feed-header-compact">
                    <div>
                        <h3>COMMUNITY FEED</h3>
                        <div class="feed-live-meta">
                            <span class="feed-live-indicator">
                                <span class="feed-live-dot"></span> Live
                            </span>
                            @if($newPostCount > 0)
                                <span class="feed-live-count">{{ $newPostCount }} new</span>
                            @endif
                        </div>
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
                            <article class="post-card feed-card">
                                <div class="post-header">
                                    <div class="post-author">
                                        <a href="{{ route('profile.show', $post->user->id) }}" class="author-avatar">
                                            {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                        </a>
                                        <div class="author-info">
                                            <a href="{{ route('profile.show', $post->user->id) }}" class="author-name">
                                                {{ $post->user->name }}
                                            </a>
                                            <span class="author-rank">{{ $post->user->rank }}</span>
                                            <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="post-content">
                                    {!! nl2br(e($post->display_content)) !!}
                                </div>

                                @if($post->embedded_image_url)
                                    <div class="post-media">
                                        <img src="{{ $post->embedded_image_url }}" alt="Post image" class="post-image" loading="lazy" />
                                    </div>
                                @endif

                                <div class="post-actions">
                                    <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                                        @csrf
                                        <button type="submit" class="action-btn @if($post->isLikedBy(auth()->id())) liked @endif">
                                            ❤️ <span class="action-count">{{ $post->likes_count }}</span>
                                        </button>
                                    </form>
                                    <a href="{{ route('posts.show', $post->id) }}" class="action-btn">
                                        💬 <span class="action-count">{{ $post->comments->count() }}</span>
                                    </a>
                                    <button type="button" class="action-btn action-btn-share">
                                        📤
                                    </button>
                                    <button type="button" class="action-btn action-btn-bookmark">
                                        📌
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

        </section>

        <!-- RIGHT SIDEBAR: Matches + Leaderboard -->
        <aside class="dashboard-column dashboard-column-right">
            
            <!-- Upcoming Matches -->
            <section class="dashboard-section">
                <div class="section-header-compact">
                    <h4>YOUR MATCHES</h4>
                    <span class="calendar-icon">📅</span>
                </div>

                @if($upcomingMatches->isEmpty())
                    <div class="empty-state-compact">
                        <p>No upcoming matches</p>
                        <a href="{{ route('challenges.create') }}" class="btn btn-primary btn-compact">Find Match</a>
                    </div>
                @else
                    <div class="matches-list">
                        @foreach($upcomingMatches->take(2) as $match)
                            <article class="match-mini-card">
                                <div class="match-vs">
                                    <span class="vs-badge">vs</span>
                                    @if($match->player2)
                                        <span class="vs-opponent">{{ $match->player2->name }}</span>
                                    @else
                                        <span class="vs-opponent">Waiting...</span>
                                    @endif
                                </div>
                                <div class="match-details">
                                    <div class="match-datetime">{{ $match->match_date->format('M d, h:i A') }}</div>
                                    <div class="match-location">{{ $match->location ?? 'TBD' }}</div>
                                </div>
                                <a href="{{ route('matches.show', $match->id) }}" class="match-view-btn">View</a>
                            </article>
                        @endforeach
                    </div>
                    @if($upcomingMatches->count() > 2)
                        <a href="{{ route('matches.index') }}" class="view-all-link">View all matches</a>
                    @endif
                @endif
            </section>

            <!-- Leaderboard Preview -->
            <section class="dashboard-section">
                <div class="section-header-compact">
                    <h4>TOP PLAYERS</h4>
                    <span class="trophy-icon">🏆</span>
                </div>

                <div class="leaderboard-mini">
                    @foreach($leaderboard->take(5) as $index => $player)
                        <div class="leaderboard-mini-row">
                            <span class="rank-number @if($index === 0) rank-gold @elseif($index === 1) rank-silver @elseif($index === 2) rank-bronze @endif">
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
                            <a href="{{ route('profile.show', $player->id) }}" class="leaderboard-player-name">{{ $player->name }}</a>
                            <span class="leaderboard-elo">{{ $player->elo_rating }}</span>
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('challenges.index') }}" class="view-all-link">View full leaderboard</a>
            </section>

        </aside>

    </div>

    <!-- Floating Action Button (Mobile Only) -->
    <div class="fab-menu">
        <button class="fab fab-main" aria-label="Quick actions" title="Quick actions">
            ➕
        </button>
        <div class="fab-options">
            <a href="#status-update" class="fab-option" title="Create post">
                <span class="fab-option-icon">✍️</span>
                <span class="fab-option-label">Post</span>
            </a>
            <a href="{{ route('matches.index') }}" class="fab-option" title="Find match">
                <span class="fab-option-icon">🔍</span>
                <span class="fab-option-label">Match</span>
            </a>
            <a href="{{ route('challenges.create') }}" class="fab-option" title="Challenge player">
                <span class="fab-option-icon">⚔️</span>
                <span class="fab-option-label">Challenge</span>
            </a>
        </div>
    </div>

</div>

<style>
/* FAB Menu Styling */
.fab-menu {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 50;
    display: none;
}

@media (max-width: 768px) {
    .fab-menu {
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-items: flex-end;
    }
}

.fab {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--primary-color);
    border: none;
    cursor: pointer;
    font-size: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
    transition: all 0.3s ease;
}

.fab:hover {
    background: var(--primary-hover);
    transform: scale(1.1);
}

.fab.fab-main.active {
    transform: rotate(45deg);
}

.fab-options {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.3s ease;
}

.fab-menu.active .fab-options {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.fab-option {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--secondary-color);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);
    text-decoration: none;
    transition: all 0.2s ease;
}

.fab-option:hover {
    background: var(--secondary-hover);
    transform: scale(1.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fabMain = document.querySelector('.fab.fab-main');
    const fabMenu = document.querySelector('.fab-menu');
    
    if (fabMain) {
        fabMain.addEventListener('click', function() {
            fabMenu.classList.toggle('active');
            fabMain.classList.toggle('active');
        });
    }
});
</script>

@endsection
