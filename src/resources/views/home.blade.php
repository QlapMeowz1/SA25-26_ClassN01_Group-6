@extends('layout')

@section('title', 'BadNet - Badminton Social Network')

@section('content')
<div class="home-feed-shell">
    <section class="home-hero">
        <div class="home-hero-copy">
            <p class="home-eyebrow">BadmintonHub</p>
            <h1>@auth Welcome back, {{ auth()->user()->name }}! @else Welcome to BadNet @endauth</h1>
            <p>Connect, compete, and share what happens on and off the court.</p>
        </div>

        <div class="home-hero-actions">
            @guest
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                <a href="{{ route('login') }}" class="btn btn-secondary">Sign In</a>
            @else
                <form action="{{ route('matches.quick') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">Quick Match</button>
                </form>
                <a href="{{ route('matches.index') }}#open-matches" class="btn btn-secondary">Join Open Match</a>
            @endguest
        </div>
    </section>

    @auth
        @if($isFirstRun)
            <section class="home-onboarding">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Getting Started</p>
                        <h2>Play your first match in 3 steps</h2>
                    </div>
                </div>
                <ol class="onboarding-steps">
                    <li><strong>Create:</strong> tap <em>Quick Match</em> to instantly create an open match.</li>
                    <li><strong>Join:</strong> accept a player request (or join someone else's open match).</li>
                    <li><strong>Submit Result:</strong> start match, enter score, and update ELO automatically.</li>
                </ol>
                <div class="home-hero-actions">
                    <a href="{{ route('challenges.create') }}" class="btn btn-secondary">Send Challenge</a>
                    <a href="{{ route('matches.create') }}" class="btn btn-primary">Manual Match Setup</a>
                </div>
            </section>
        @endif

        @if($openMatches->isNotEmpty())
            <section class="home-open-matches">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Matchmaking</p>
                        <h2>Open Matches You Can Join</h2>
                    </div>
                    <a href="{{ route('matches.index') }}#open-matches" class="feed-link">Open list</a>
                </div>
                <div class="open-match-list">
                    @foreach($openMatches as $match)
                        <article class="open-match-item">
                            <div>
                                <p><strong>{{ $match->player1->name }}</strong> is waiting for an opponent</p>
                                <p class="post-time">{{ $match->match_date->format('M d, Y h:i A') }} · {{ $match->location ?? 'Court TBD' }}</p>
                            </div>
                            <a href="{{ route('matches.show', $match->id) }}" class="btn btn-secondary">Request Join</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endauth

    <section class="home-feed">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">Community</p>
                <h2>Latest Posts</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="feed-link">See all</a>
        </div>

        @if($posts->isEmpty())
            <div class="post-card feed-empty">
                <h3>No posts yet</h3>
                <p>Be the first to share a result, a photo, or a doubles callout.</p>
            </div>
        @else
            <div class="posts-feed">
                @foreach($posts as $post)
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
                                    <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                            <article class="post-card feed-card" data-post-id="{{ $post->id }}">
                            {!! nl2br(e($post->display_content)) !!}
                        </div>

                        @php $postImage = $post->image_url ?? $post->embedded_image_url; @endphp
                        @if($postImage)
                            <div class="post-media">
                                <img src="{{ $postImage }}" alt="Post image" class="post-image" loading="lazy" />
                            </div>
                        @endif

                        <div class="post-stats">
                            <span data-post-like-stat>❤️ {{ $post->likes_count }} likes</span>
                            <span data-post-comment-stat>💬 {{ $post->comments->count() }} comments</span>
                        </div>

                        <div class="post-actions post-actions-fb">
                            @auth
                                <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                                    @csrf
                                    <button type="submit" class="action-btn fb-action-btn @if($post->isLikedBy(auth()->id())) liked @endif">
                                        <span class="action-icon" aria-hidden="true">👍</span>
                                        <span class="action-label">Like</span>
                                        <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                                    </button>
                                </form>
                            @else
                                <button class="action-btn fb-action-btn" disabled>
                                    <span class="action-icon" aria-hidden="true">👍</span>
                                    <span class="action-label">Like</span>
                                    <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                                </button>
                            @endauth

                            <a href="{{ route('posts.show', $post->id) }}#comments-section" class="action-btn fb-action-btn">
                                <span class="action-icon" aria-hidden="true">💬</span>
                                <span class="action-label">Comment</span>
                                <span class="action-count" data-comment-count>{{ $post->comments->count() }}</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
