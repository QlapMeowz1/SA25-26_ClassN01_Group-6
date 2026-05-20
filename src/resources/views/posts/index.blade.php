@extends('layout')

@section('title', 'Social - BadNet')

@section('content')
<div class="page-shell">
    <div class="posts-header">
        <div>
            <p class="home-eyebrow">Community Archive</p>
            <h1>Social</h1>
            <p class="page-subtitle">The live feed now sits on the dashboard. Use this page as an archive, comment lane, and quick posting surface.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Go to Dashboard Feed</a>
    </div>

    <div class="dashboard-section">
        @auth
            <section class="post-creator">
                <div class="creator-header">
                    <div class="creator-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <form action="{{ route('posts.store') }}" method="POST" class="creator-form">
                        @csrf
                        <textarea name="content" placeholder="Share a recap, highlight, or invite..." maxlength="500" required></textarea>
                        <button type="submit" class="btn btn-primary">Post</button>
                    </form>
                </div>
            </section>
        @endauth

        <section class="posts-feed">
            @if($posts->isEmpty())
                <div class="empty-panel">
                    <p class="empty-message">No posts yet. Be the first to share!</p>
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
                                        <button type="submit" class="delete-btn" onclick="return confirm('Delete this post?')">Delete</button>
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
                            <span data-post-like-stat>❤️ {{ $post->likes_count }} Likes</span>
                            <span data-post-comment-stat>💬 {{ $post->comments->count() }} Comments</span>
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
