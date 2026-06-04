@extends('layout')

@section('title', 'Admin Content - BadNet')

@section('content')
<div class="page-shell admin-console-page">
    <section class="admin-page-header">
        <div>
            <p class="home-eyebrow">Admin Console</p>
            <h1>Content Moderation</h1>
            <p class="page-subtitle">Review posts and recent comments from the community feed.</p>
        </div>
    </section>

    @include('admin.partials.nav')

    <div class="admin-layout">
        <main class="admin-main-column">
            <section class="admin-panel">
                <form method="GET" action="{{ route('admin.content') }}" class="admin-filter-bar">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search post content or author">
                    <button type="submit" class="btn btn-primary btn-small">Search</button>
                    <a href="{{ route('admin.content') }}" class="btn btn-secondary btn-small">Reset</a>
                </form>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Posts</p>
                        <h2>Feed Review</h2>
                    </div>
                </div>

                <div class="admin-row-list">
                    @foreach($posts as $post)
                        <article class="admin-content-review">
                            <div>
                                <strong>{{ $post->user?->name ?? 'Unknown user' }}</strong>
                                <small>{{ $post->created_at?->diffForHumans() }} - {{ $post->likes_count }} likes / {{ $post->comments_count }} comments</small>
                                <p>{{ \Illuminate\Support\Str::limit($post->content, 220) }}</p>
                            </div>
                            <div class="admin-review-actions">
                                <a href="{{ route('posts.show', $post->id) }}" class="btn btn-secondary btn-small">Open</a>
                                <form method="POST" action="{{ route('admin.posts.delete', $post->id) }}" onsubmit="return confirm('Delete this post?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination-wrapper">
                    {{ $posts->links() }}
                </div>
            </section>
        </main>

        <aside class="admin-side-column">
            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Comments</p>
                        <h2>Recent Replies</h2>
                    </div>
                </div>

                <div class="admin-row-list">
                    @forelse($comments as $comment)
                        <a href="{{ route('posts.show', $comment->post_id) }}#comments-section" class="admin-match-row">
                            <strong>{{ $comment->user?->name ?? 'Unknown user' }}</strong>
                            <span>{{ \Illuminate\Support\Str::limit($comment->content, 90) }}</span>
                        </a>
                    @empty
                        <div class="empty-inline">No comments yet.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
