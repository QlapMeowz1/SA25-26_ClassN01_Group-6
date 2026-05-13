@extends('layout')

@section('title', 'Post - BadNet')

@section('content')
<div class="post-detail">
    <div class="post-card full">
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

        <div class="post-actions">
            @auth
                <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                    @csrf
                    <button type="submit" class="action-btn @if($post->isLikedBy(auth()->id())) liked @endif">
                        ❤️ Like
                    </button>
                </form>
            @else
                <button class="action-btn" disabled>❤️ Like</button>
            @endauth
        </div>
    </div>

    <section class="comments-section">
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
                                @if(auth()->id() === $comment->user->id)
                                    <form action="{{ route('comments.delete', $comment->id) }}" method="POST" class="delete-form">
                                        @csrf
                                        <button type="submit" class="delete-btn" onclick="return confirm('Delete comment?')">Delete</button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                        <div class="comment-content">{{ $comment->content }}</div>
                        @if($comment->image)
                            <div class="comment-media">
                                <img src="{{ $comment->image }}" alt="Comment image" class="comment-image" loading="lazy">
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
