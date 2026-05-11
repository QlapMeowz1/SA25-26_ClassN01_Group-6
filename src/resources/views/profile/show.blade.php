@extends('layout')

@section('title', $user->name . ' - BadNet')

@section('content')
<div class="page-shell profile-page-shell">
    <section class="profile-hero dashboard-section">
        <div class="profile-cover"></div>
        <div class="profile-info profile-info-compact">
            @if($user->avatar)
                <img src="{{ asset('avatars/' . $user->avatar) }}" alt="{{ $user->name }}" class="avatar-large">
            @else
                <div class="avatar-large placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
            <div class="profile-details">
                <p class="home-eyebrow">Player Profile</p>
                <h1>{{ $user->name }}</h1>
                <p class="rank-tag rank-tag-strong">{{ $user->rank }}</p>
                <p class="bio">{{ $user->bio }}</p>
                @if($user->phone)
                    <p class="phone">{{ $user->phone }}</p>
                @endif
            </div>
            @auth
                @if(auth()->id() === $user->id)
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
                @else
                    <a href="{{ route('challenges.create') }}?opponent_id={{ $user->id }}" class="btn btn-primary">Challenge</a>
                @endif
            @endauth
        </div>
    </section>

    <div class="profile-layout">
        <section class="dashboard-section">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">Statistics</p>
                    <h2>Performance snapshot</h2>
                </div>
            </div>
            <div class="stats-grid profile-stats-grid">
                <div class="stat-card">
                    <span class="stat-title">ELO Rating</span>
                    <span class="stat-value">{{ $user->elo_rating }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Wins</span>
                    <span class="stat-value">{{ $user->wins }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Losses</span>
                    <span class="stat-value">{{ $user->losses }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-title">Win Rate</span>
                    <span class="stat-value">{{ $user->getWinRate() }}%</span>
                </div>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">Recent Matches</p>
                    <h2>Match history</h2>
                </div>
            </div>
            @if($matches->isEmpty())
                <p class="empty-message">No completed matches</p>
            @else
                <div class="match-list">
                    @foreach($matches as $match)
                        <div class="match-card">
                            <div class="match-info">
                                <span class="player-name">
                                    @if($match->player1_id === $user->id)
                                        {{ $user->name }}
                                    @else
                                        {{ $match->player1->name }}
                                    @endif
                                </span>
                                <span class="vs">vs</span>
                                <span class="player-name">
                                    @if($match->player2_id === $user->id)
                                        {{ $user->name }}
                                    @elseif($match->player2)
                                        {{ $match->player2->name }}
                                    @else
                                        Waiting for player
                                    @endif
                                </span>
                            </div>
                            <div class="match-score">Score: {{ $match->player1_score }} - {{ $match->player2_score }}</div>
                            <div class="match-result">
                                @if($match->winner_id === $user->id)
                                    <span class="badge-win">Won</span>
                                @else
                                    <span class="badge-loss">Lost</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="dashboard-section profile-posts-panel">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">Recent Posts</p>
                    <h2>Community posts</h2>
                </div>
            </div>
            @if($posts->isEmpty())
                <p class="empty-message">No posts yet</p>
            @else
                <div class="post-list">
                    @foreach($posts as $post)
                        <div class="post-card">
                            <div class="post-content">{!! nl2br(e($post->display_content)) !!}</div>

                            @if($post->image || $post->embedded_image_url)
                                <div class="post-media">
                                    <img src="{{ $post->image ?? $post->embedded_image_url }}" alt="Post image" class="post-image" loading="lazy" />
                                </div>
                            @endif

                            @if(!empty($post->videos) || $post->video)
                                <div class="post-media post-video-media">
                                    @foreach(($post->videos ?? [$post->video]) as $video)
                                        @if($video)
                                            <video class="post-video" controls preload="metadata">
                                                <source src="{{ $video }}">
                                            </video>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            <div class="post-meta">{{ $post->created_at->diffForHumans() }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
