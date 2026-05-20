@extends('layout')

@section('title', 'Social - BadNet')

@section('content')
<div class="page-shell">
    <div class="posts-header">
        <div>
            <p class="home-eyebrow">{{ __('ui.home.community') }}</p>
            <h1>{{ __('ui.nav.home') }}</h1>
            <p class="page-subtitle">{{ __('ui.post.archive_intro') }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">{{ __('ui.home.dashboard_feed') }}</a>
    </div>

    <div class="dashboard-section">
        @auth
            <section class="post-creator">
                <div class="creator-header">
                    <div class="creator-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <form action="{{ route('posts.store') }}" method="POST" class="creator-form">
                        @csrf
                        <textarea name="content" placeholder="{{ __('ui.post.share_placeholder') }}" maxlength="500" required></textarea>
                        <button type="submit" class="btn btn-primary">{{ __('ui.post.publish') }}</button>
                    </form>
                </div>
            </section>
        @endauth

        <section class="posts-feed">
            @if($posts->isEmpty())
                <div class="empty-panel">
                    <p class="empty-message">{{ __('ui.post.no_posts') }}</p>
                </div>
            @else
                @foreach($posts as $post)
                        <div class="post-card" data-post-id="{{ $post->id }}">
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
                            @auth
                                @if(auth()->id() === $post->user->id)
                                    <form action="{{ route('posts.delete', $post->id) }}" method="POST" class="delete-form">
                                        @csrf
                                        <button type="submit" class="delete-btn" onclick="return confirm('{{ __('ui.post.delete_post_confirm') }}')">{{ __('ui.post.delete') }}</button>
                                    </form>
                                @endif
                            @endauth
                        </div>

                        <div class="post-content">{!! nl2br(e($post->display_content)) !!}</div>

                        @php $postImage = $post->image_url ?? $post->embedded_image_url; @endphp
                        @if($postImage)
                            <div class="post-media">
                                <img src="{{ $postImage }}" alt="Post image" class="post-image" loading="lazy" />
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
                    </div>
                @endforeach
            @endif
        </section>

        @if($posts->hasPages())
            <div>
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
