@php
    $author = $post->user;
    $content = $post->display_content ?? $post->content;
    $commentCount = $post->comments_count ?? ($post->comments?->count() ?? 0);
    $isOwner = auth()->check() && auth()->id() === $post->user_id;

    $images = [];
    if (!empty($post->image)) {
        $images = [$post->image];
    } else {
        $images = $post->embedded_image_urls ?? [];
        if (empty($images) && !empty($post->embedded_image_url)) {
            $images = [$post->embedded_image_url];
        }
    }

    $videos = [];
    if (!empty($post->videos) && is_array($post->videos)) {
        $videos = $post->videos;
    } elseif (!empty($post->video)) {
        $videos = [$post->video];
    }
@endphp

<article class="post-card feed-card" data-post-id="{{ $post->id }}">
    <div class="post-header">
        <div class="post-author">
            <a href="{{ route('profile.show', $author->id) }}" class="author-avatar">
                @if($author->avatar)
                    <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}" class="h-full w-full object-cover">
                @else
                    <span class="author-avatar-system">{{ strtoupper(substr($author->name, 0, 1)) }}</span>
                @endif
            </a>

            <div class="min-w-0">
                <a href="{{ route('profile.show', $author->id) }}" class="font-heading text-base font-bold text-slate-950 transition hover:text-energy dark:text-white">
                    {{ $author->name }}
                </a>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
                        {{ $author->rank ?? __('ui.team.level') }}
                    </span>
                    <span>{{ $post->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        @if($isOwner)
            <form action="{{ route('posts.delete', $post->id) }}" method="POST" class="delete-form">
                @csrf
                <button type="submit" class="delete-btn" onclick="return confirm('{{ __('ui.post.delete_post_confirm') ?? 'Delete this post?' }}')">
                    {{ __('ui.post.delete') ?? 'Delete' }}
                </button>
            </form>
        @endif
    </div>

    <div class="post-content" data-full-content="{{ e($content) }}">
        {!! nl2br(e(\Illuminate\Support\Str::limit($content, 400))) !!}
    </div>

    @if(!empty($images))
        <div class="post-media">
            @if(count($images) === 1)
                <img src="{{ $images[0] }}" alt="Post image" class="max-h-[460px] w-full object-contain" loading="lazy" />
            @else
                <div class="post-image-grid">
                    @foreach($images as $img)
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <img src="{{ $img }}" alt="Post image" class="h-40 w-full object-cover" loading="lazy" />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if(!empty($videos))
        <div class="mt-4 space-y-3">
            @foreach($videos as $video)
                <video class="w-full rounded-2xl border border-slate-200 bg-slate-50" controls preload="metadata">
                    <source src="{{ $video }}">
                    Your browser does not support the video tag.
                </video>
            @endforeach
        </div>
    @endif

    <div class="post-stats">
        <span data-post-like-stat>❤️ {{ $post->likes_count ?? 0 }} {{ __('ui.post.likes') }}</span>
        <span data-post-comment-stat>💬 {{ $commentCount }} {{ __('ui.post.comments') }}</span>
    </div>

    <div class="post-actions post-actions-fb">
        @auth
            <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                @csrf
                <button type="button" class="post-action-pill inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 @if($post->isLikedBy(auth()->id())) ring-2 ring-energy/30 liked @endif" data-like-trigger data-like-url="{{ route('posts.like', $post->id) }}">
                    <span aria-hidden="true">👍</span>
                    <span class="post-action-label">{{ __('ui.post.like') }}</span>
                    <span class="post-action-count rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-zinc-800 dark:text-slate-200" data-like-count>{{ $post->likes_count ?? 0 }}</span>
                </button>
            </form>
        @else
            <button class="post-action-pill inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400" disabled>
                <span aria-hidden="true">👍</span>
                <span class="post-action-label">{{ __('ui.post.like') }}</span>
                <span class="post-action-count rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-zinc-800 dark:text-slate-200" data-like-count>{{ $post->likes_count ?? 0 }}</span>
            </button>
        @endauth

        <a href="{{ route('posts.show', $post->id) }}#comments-section" class="post-action-pill inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-orange-200 hover:bg-orange-50 hover:text-orange-800">
            <span aria-hidden="true">💬</span>
            <span class="post-action-label">{{ __('ui.post.comment') }}</span>
            <span class="post-action-count rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-zinc-800 dark:text-slate-200" data-comment-count>{{ $commentCount }}</span>
        </a>
    </div>

    @if(isset($post->comments) && $post->comments->count() > 0)
        <div class="mt-4 space-y-2 border-t border-slate-200 pt-4">
            @foreach($post->comments->take(2) as $comment)
                <div class="rounded-2xl bg-slate-50 px-3 py-2 text-sm text-slate-600">
                    <strong class="font-heading text-slate-900">{{ $comment->user->name ?? __('ui.nav.profile') }}</strong>
                    <span class="ml-2">{{ $comment->content ?? '' }}</span>
                </div>
            @endforeach

            @if($commentCount > 2)
                <a href="{{ route('posts.show', $post->id) }}" class="inline-flex text-sm font-semibold text-energy transition hover:text-orange-600">
                    {{ __('ui.post.view_comments') ?? 'View comments' }}
                </a>
            @endif
        </div>
    @endif
</article>
