<article class="post-card feed-card" data-post-id="{{ $post->id }}">
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
        $images = [];

        if (!empty($post->image)) {
            $images = [$post->image];
        } else {
            $images = $post->embedded_image_urls ?? [];
            if (empty($images) && !empty($post->embedded_image_url)) {
                $images = [$post->embedded_image_url];
            }
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

    @php
        $videos = [];

        if (!empty($post->videos) && is_array($post->videos)) {
            $videos = $post->videos;
        } elseif (!empty($post->video)) {
            $videos = [$post->video];
        }
    @endphp

    @if(!empty($videos))
        <div class="post-media post-video-media">
            @foreach($videos as $video)
                <video class="post-video" controls preload="metadata">
                    <source src="{{ $video }}">
                    Your browser does not support the video tag.
                </video>
            @endforeach
        </div>
    @endif

    <div class="post-actions post-actions-fb">
        <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
            @csrf
            <button type="submit" class="action-btn fb-action-btn @if(method_exists($post, 'isLikedBy') && $post->isLikedBy(auth()->id())) liked @endif">
                <span class="action-icon" aria-hidden="true">👍</span>
                <span class="action-label">Like</span>
                <span class="action-count">{{ $post->likes_count ?? 0 }}</span>
            </button>
        </form>
        <a href="{{ route('posts.show', $post->id) }}#comments-section" class="action-btn fb-action-btn">
            <span class="action-icon" aria-hidden="true">💬</span>
            <span class="action-label">Comment</span>
            <span class="action-count">{{ $post->comments->count() ?? 0 }}</span>
        </a>
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

</article>
