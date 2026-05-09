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
                <span class="author-rank">{{ $post->user->rank ?? '' }}</span>
                <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

    <div class="post-content">
        {!! nl2br(e($post->display_content ?? $post->content)) !!}
    </div>

    @if(!empty($post->embedded_image_url))
        <div class="post-media">
            <img src="{{ $post->embedded_image_url }}" alt="Post image" class="post-image" loading="lazy" />
        </div>
    @endif

    <div class="post-actions">
        <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
            @csrf
            <button type="submit" class="action-btn @if(method_exists($post, 'isLikedBy') && $post->isLikedBy(auth()->id())) liked @endif">
                ❤️ <span class="action-count">{{ $post->likes_count ?? 0 }}</span>
            </button>
        </form>
        <a href="{{ route('posts.show', $post->id) }}" class="action-btn">💬 <span class="action-count">{{ $post->comments->count() ?? 0 }}</span></a>
        <button type="button" class="action-btn action-btn-share">📤</button>
        <button type="button" class="action-btn action-btn-bookmark">📌</button>
    </div>
</article>
