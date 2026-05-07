@extends('layout')

@section('title', 'Post - BadNet')

@section('content')
<div class="post-detail">
    <div class="post-card full">
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
            @auth
                @if(auth()->id() === $post->user->id)
                    <form action="{{ route('posts.delete', $post->id) }}" method="POST" class="delete-form">
                        @csrf
                        <button type="submit" class="delete-btn" onclick="return confirm('Delete this post?')">Delete</button>
                    </form>
                @endif
            @endauth
        </div>

        <div class="post-content">
            {{ $post->content }}
        </div>

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
            <form action="{{ route('posts.comment', $post->id) }}" method="POST">
                @csrf
                <textarea name="content" placeholder="Write a comment..." maxlength="300" required></textarea>
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
                            <a href="{{ route('profile.show', $comment->user->id) }}" class="comment-author">
                                {{ $comment->user->name }}
                            </a>
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
