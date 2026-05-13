@extends('layout')

@section('title', 'Post - BadNet')

@section('content')
<div class="post-detail">
    <div class="post-card full" data-post-id="{{ $post->id }}">
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

        <div class="post-stats">
            <span>❤️ {{ $post->likes_count }} Likes</span>
            <span>💬 {{ $post->comments->count() }} Comments</span>
        </div>

        <div class="post-actions post-actions-fb">
            @auth
                <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                    @csrf
                    <button type="submit" class="action-btn fb-action-btn @if($post->isLikedBy(auth()->id())) liked @endif">
                        <span class="action-icon" aria-hidden="true">👍</span>
                        <span class="action-label">Like</span>
                        <span class="action-count">{{ $post->likes_count }}</span>
                    </button>
                </form>
            @else
                <button class="action-btn fb-action-btn" disabled>
                    <span class="action-icon" aria-hidden="true">👍</span>
                    <span class="action-label">Like</span>
                    <span class="action-count">{{ $post->likes_count }}</span>
                </button>
            @endauth

            <a href="#comments-section" class="action-btn fb-action-btn">
                <span class="action-icon" aria-hidden="true">💬</span>
                <span class="action-label">Comment</span>
                <span class="action-count">{{ $post->comments->count() }}</span>
            </a>
        </div>
    </div>

    <section class="comments-section" id="comments-section">
        <h2>Comments</h2>

        @auth
        <div class="comment-form">
            <form action="{{ route('posts.comment', $post->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <textarea name="content" placeholder="Write a comment..." maxlength="300" required></textarea>
                <input type="file" name="image" accept="image/*" class="comment-image-input">
                <button type="submit" class="btn btn-primary">Comment</button>
            </form>
        </div>
        @endauth

        <div class="comments-list">
            @if($comments->isEmpty())
                <p class="empty-message">No comments yet</p>
            @else
                @foreach($comments as $comment)
                    <div class="comment-card">
                        <div class="comment-header">
                            <div class="comment-user-info">
                                <a href="{{ route('profile.show', $comment->user->id) }}" class="comment-avatar-link">
                                    @if($comment->user->avatar)
                                        <img src="{{ asset('avatars/' . $comment->user->avatar) }}" alt="{{ $comment->user->name }}" class="comment-avatar">
                                    @else
                                        <span class="comment-avatar comment-avatar-fallback">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('profile.show', $comment->user->id) }}" class="comment-author">
                                    {{ $comment->user->name }}
                                </a>
                            </div>
                            <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                            @auth
                                <div class="comment-actions">
                                    <form action="{{ route('comments.like', $comment->id) }}" method="POST" class="action-form">
                                        @csrf
                                        <button type="submit" class="action-btn comment-like-btn @if($comment->isLikedBy(auth()->id())) liked @endif">
                                            ❤️ {{ $comment->likes_count ?? $comment->likes->count() }}
                                        </button>
                                    </form>
                                    @if(auth()->id() === $comment->user->id)
                                        <form action="{{ route('comments.delete', $comment->id) }}" method="POST" class="delete-form">
                                            @csrf
                                            <button type="submit" class="delete-btn" onclick="return confirm('Delete comment?')">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <span class="comment-like-count">❤️ {{ $comment->likes_count ?? $comment->likes->count() }}</span>
                            @endauth
                        </div>
                        <div class="comment-content">{{ $comment->content }}</div>
                        @if($comment->image)
                            <div class="comment-media">
                                <img src="{{ $comment->image }}" alt="Comment image" class="comment-image" loading="lazy">
                            </div>
                        @endif

                        @auth
                            <form action="{{ route('comments.reply', $comment->id) }}" method="POST" enctype="multipart/form-data" class="comment-reply-form">
                                @csrf
                                <textarea name="content" placeholder="Reply to {{ $comment->user->name }}..." maxlength="300" required></textarea>
                                <input type="file" name="image" accept="image/*" class="comment-image-input">
                                <button type="submit" class="btn btn-secondary btn-small">Reply</button>
                            </form>
                        @endauth

                        @if($comment->replies->isNotEmpty())
                            <div class="comment-replies">
                                @foreach($comment->replies as $reply)
                                    <div class="comment-card comment-reply-card">
                                        <div class="comment-header">
                                            <div class="comment-user-info">
                                                <a href="{{ route('profile.show', $reply->user->id) }}" class="comment-avatar-link">
                                                    @if($reply->user->avatar)
                                                        <img src="{{ asset('avatars/' . $reply->user->avatar) }}" alt="{{ $reply->user->name }}" class="comment-avatar">
                                                    @else
                                                        <span class="comment-avatar comment-avatar-fallback">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</span>
                                                    @endif
                                                </a>
                                                <a href="{{ route('profile.show', $reply->user->id) }}" class="comment-author">
                                                    {{ $reply->user->name }}
                                                </a>
                                            </div>
                                            <span class="comment-time">{{ $reply->created_at->diffForHumans() }}</span>
                                            @auth
                                                <div class="comment-actions">
                                                    <form action="{{ route('comments.like', $reply->id) }}" method="POST" class="action-form">
                                                        @csrf
                                                        <button type="submit" class="action-btn comment-like-btn @if($reply->isLikedBy(auth()->id())) liked @endif">
                                                            ❤️ {{ $reply->likes_count ?? $reply->likes->count() }}
                                                        </button>
                                                    </form>
                                                    @if(auth()->id() === $reply->user->id)
                                                        <form action="{{ route('comments.delete', $reply->id) }}" method="POST" class="delete-form">
                                                            @csrf
                                                            <button type="submit" class="delete-btn" onclick="return confirm('Delete reply?')">Delete</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="comment-like-count">❤️ {{ $reply->likes_count ?? $reply->likes->count() }}</span>
                                            @endauth
                                        </div>
                                        <div class="comment-content">{{ $reply->content }}</div>
                                        @if($reply->image)
                                            <div class="comment-media">
                                                <img src="{{ $reply->image }}" alt="Reply image" class="comment-image" loading="lazy">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    @if($comments->hasPages())
        <div style="margin-top: 30px;">
            {{ $comments->links() }}
        </div>
    @endif
</div>
@endsection
