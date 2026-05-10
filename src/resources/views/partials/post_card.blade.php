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

    <div class="post-content" data-full-content="{{ e($post->display_content ?? $post->content) }}">
        {!! nl2br(e(\Illuminate\Support\Str::limit($post->display_content ?? $post->content, 400))) !!}
    </div>

    @php
        $images = $post->embedded_image_urls ?? [];
        if (empty($images) && !empty($post->embedded_image_url)) {
            $images = [$post->embedded_image_url];
        }
    @endphp

    @if(!empty($images))
        <div class="post-media">
            @if(count($images) === 1)
                <img src="{{ $images[0] }}" alt="Post image" class="post-image" loading="lazy" />
            @else
                <div class="post-image-grid">
                    @foreach($images as $img)
                        <div class="post-image-cell"><img src="{{ $img }}" alt="Post image" loading="lazy" /></div>
                    @endforeach
                </div>
            @endif
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

    @if(isset($post->comments) && $post->comments->count() > 0)
        <div class="post-comments">
            @foreach($post->comments as $comment)
                <div class="comment-row">
                    <strong>{{ $comment->user->name ?? 'User' }}</strong>
                    <span class="comment-text">{{ $comment->content ?? '' }}</span>
                </div>
            @endforeach

            @if(isset($post->comments_count) && $post->comments_count > $post->comments->count())
                <a href="{{ route('posts.show', $post->id) }}" class="view-all-comments">View all {{ $post->comments_count }} comments</a>
            @endif
        </div>
    @endif

    <script>
    // Post expand/collapse handled after DOM load globally in dashboard
    </script>
</article>
