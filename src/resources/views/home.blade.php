@extends('layout')

@section('title', 'BadNet - Badminton Social Network')

@section('content')
<div class="home-feed-shell">
    <section class="home-hero">
        <div class="home-hero-copy">
            <p class="home-eyebrow">BadmintonHub</p>
            <h1>@auth {{ __('ui.home.welcome_back', ['name' => auth()->user()->name]) }} @else {{ __('ui.home.welcome_guest') }} @endauth</h1>
            <p>{{ __('ui.home.tagline') }}</p>
        </div>

        <div class="home-hero-actions">
            @guest
                <a href="{{ route('register') }}" class="btn btn-primary">{{ __('ui.nav.register') }}</a>
                <a href="{{ route('login') }}" class="btn btn-secondary">{{ __('ui.nav.login') }}</a>
            @else
                <form action="{{ route('matches.quick') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">{{ __('ui.match.quick_match') ?? 'Quick Match' }}</button>
                </form>
                <a href="{{ route('matches.index') }}#open-matches" class="btn btn-secondary">{{ __('ui.match.join_open_match') ?? 'Join Open Match' }}</a>
            @endguest
        </div>
    </section>

    @auth
        @if($isFirstRun)
            <section class="home-onboarding">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Getting Started</p>
                        <h2>{{ __('ui.home.first_match_title') ?? 'Play your first match in 3 steps' }}</h2>
                    </div>
                </div>
                <ol class="onboarding-steps">
                    <li><strong>{{ __('ui.home.create_step') ?? 'Create:' }}</strong> {{ __('ui.home.create_step_body') ?? 'tap Quick Match to instantly create an open match.' }}</li>
                    <li><strong>{{ __('ui.home.join_step') ?? 'Join:' }}</strong> {{ __('ui.home.join_step_body') ?? 'accept a player request (or join someone else\'s open match).' }}</li>
                    <li><strong>{{ __('ui.home.submit_step') ?? 'Submit Result:' }}</strong> {{ __('ui.home.submit_step_body') ?? 'start match, enter score, and update ELO automatically.' }}</li>
                </ol>
                <div class="home-hero-actions">
                    <a href="{{ route('challenges.create') }}" class="btn btn-secondary">{{ __('ui.match.send_challenge') ?? 'Send Challenge' }}</a>
                    <a href="{{ route('matches.create') }}" class="btn btn-primary">{{ __('ui.match.manual_setup') ?? 'Manual Match Setup' }}</a>
                </div>
            </section>
        @endif

        @if($openMatches->isNotEmpty())
            <section class="home-open-matches">
                <div class="feed-heading">
                    <div>
                        <p class="home-eyebrow">Matchmaking</p>
                        <h2>{{ __('ui.home.open_matches_title') ?? 'Open Matches You Can Join' }}</h2>
                    </div>
                    <a href="{{ route('matches.index') }}#open-matches" class="feed-link">{{ __('ui.home.open_list') ?? 'Open list' }}</a>
                </div>
                <div class="open-match-list">
                    @foreach($openMatches as $match)
                        <article class="open-match-item">
                            <div>
                                <p><strong>{{ $match->player1->name }}</strong> {{ __('ui.home.waiting_for_opponent') ?? 'is waiting for an opponent' }}</p>
                                <p class="post-time">{{ $match->match_date->format('M d, Y h:i A') }} · {{ $match->location ?? __('ui.home.court_tbd') ?? 'Court TBD' }}</p>
                            </div>
                            <a href="{{ route('matches.show', $match->id) }}" class="btn btn-secondary">{{ __('ui.home.request_join') ?? 'Request Join' }}</a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endauth

    <section class="home-feed">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">{{ __('ui.home.community') ?? 'Community' }}</p>
                <h2>{{ __('ui.home.latest_posts') ?? 'Latest Posts' }}</h2>
            </div>
            <a href="{{ route('posts.index') }}" class="feed-link">{{ __('ui.home.see_all') ?? 'See all' }}</a>
        </div>

        @if($posts->isEmpty())
            <div class="post-card feed-empty">
                <h3>{{ __('ui.home.no_posts_title') }}</h3>
                <p>{{ __('ui.home.no_posts_body') }}</p>
            </div>
        @else
            <div class="posts-feed">
                @foreach($posts as $post)
                    <article class="post-card feed-card" data-post-id="{{ $post->id }}">
                        <div class="post-header">
                            <div class="post-author">
                                <a href="{{ route('profile.show', $post->user->id) }}" class="author-avatar">
                                    @if($post->user->avatar)
                                        <img src="{{ asset('avatars/' . $post->user->avatar) }}" alt="{{ $post->user->name }}">
                                    @else
                                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                    @endif
                                </a>
                                <div class="author-info">
                                    <a href="{{ route('profile.show', $post->user->id) }}" class="author-name">
                                        {{ $post->user->name }}
                                    </a>
                                    <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="post-content" data-full-content="{{ e($post->display_content ?? $post->content) }}">
                            {!! nl2br(e($post->display_content ?? $post->content)) !!}
                        </div>

                        @php $postImage = $post->image_url ?? $post->embedded_image_url; @endphp
                        @if($postImage)
                            <div class="post-media">
                                <img src="{{ $postImage }}" alt="Post image" class="post-image" loading="lazy" />
                            </div>
                        @endif

                        <div class="post-stats">
                            <span data-post-like-stat>❤️ {{ $post->likes_count }} {{ __('ui.post.likes') }}</span>
                            <span data-post-comment-stat>💬 {{ $post->comments->count() }} {{ __('ui.post.comments') }}</span>
                        </div>

                        <div class="post-actions post-actions-fb">
                            @auth
                                <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                                    @csrf
                                    <button type="submit" class="action-btn fb-action-btn @if($post->isLikedBy(auth()->id())) liked @endif">
                                        <span class="action-icon" aria-hidden="true">👍</span>
                                        <span class="action-label">{{ __('ui.post.like') }}</span>
                                        <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                                    </button>
                                </form>
                            @else
                                <button class="action-btn fb-action-btn" disabled>
                                    <span class="action-icon" aria-hidden="true">👍</span>
                                    <span class="action-label">{{ __('ui.post.like') }}</span>
                                    <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                                </button>
                            @endauth

                            <a href="{{ route('posts.show', $post->id) }}#comments-section" class="action-btn fb-action-btn">
                                <span class="action-icon" aria-hidden="true">💬</span>
                                <span class="action-label">{{ __('ui.post.comment') }}</span>
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
