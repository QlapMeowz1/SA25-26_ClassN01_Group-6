<article class="post-card feed-card" data-post-id="{{ $post->id }}">
    <div class="post-header">
        <div class="post-author">
            <a href="{{ route('profile.show', $post->user->id) }}" class="author-avatar">
                @if($post->user->avatar)
                    <img src="{{ asset('avatars/' . $post->user->avatar) }}" alt="{{ $post->user->name }}" class="h-full w-full object-cover">
                @else
                    <span class="author-avatar-system">{{ strtoupper(substr($post->user->name, 0, 1)) }}</span>
                @endif
            </a>
            <div class="min-w-0">
                <a href="{{ route('profile.show', $post->user->id) }}" class="font-heading text-base font-bold text-slate-950 transition hover:text-energy dark:text-white">
                    {{ $post->user->name }}
                </a>
                <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">{{ $post->user->rank ?? '' }}</span>
                    <span>{{ $post->created_at->diffForHumans() }}</span>
                </div>
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
                <img src="{{ $images[0] }}" alt="Post image" class="max-h-[460px] w-full object-contain" loading="lazy" />
            @else
                <div class="post-image-grid">
                    @foreach($images as $img)
                        <div class="overflow-hidden rounded-2xl bg-white dark:bg-zinc-900"><img src="{{ $img }}" alt="Post image" class="h-40 w-full object-cover" loading="lazy" /></div>
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
        <div class="mt-4 space-y-3">
            @foreach($videos as $video)
                <video class="w-full rounded-2xl border border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-zinc-900" controls preload="metadata">
                    <source src="{{ $video }}">
                    Your browser does not support the video tag.
                </video>
            @endforeach
        </div>
    @endif

    <div class="post-actions post-actions-fb">
        <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
            @csrf
            <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800 dark:border-slate-700 dark:bg-zinc-900 dark:text-slate-200 dark:hover:border-emerald-700 dark:hover:bg-zinc-800 dark:hover:text-emerald-300 @if(method_exists($post, 'isLikedBy') && $post->isLikedBy(auth()->id())) ring-2 ring-energy/30 @endif">
                <span aria-hidden="true">👍</span>
                <span>Like</span>
                <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-zinc-800 dark:text-slate-200" data-like-count>{{ $post->likes_count ?? 0 }}</span>
            </button>
        </form>
        <a href="{{ route('posts.show', $post->id) }}#comments-section" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:-translate-y-0.5 hover:border-orange-200 hover:bg-orange-50 hover:text-orange-800 dark:border-slate-700 dark:bg-zinc-900 dark:text-slate-200 dark:hover:border-orange-700 dark:hover:bg-zinc-800 dark:hover:text-orange-300">
            <span aria-hidden="true">💬</span>
            <span>Comment</span>
                <span class="rounded-full bg-white px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-zinc-800 dark:text-slate-200" data-comment-count>{{ $post->comments->count() ?? 0 }}</span>
        </a>
    </div>

    @if(isset($post->comments) && $post->comments->count() > 0)
        <div class="mt-4 space-y-2 border-t border-slate-200 pt-4 dark:border-slate-700">
            @foreach($post->comments as $comment)
                <div class="rounded-2xl bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-zinc-900 dark:text-slate-300">
                    <strong class="font-heading text-slate-900 dark:text-white">{{ $comment->user->name ?? 'User' }}</strong>
                    <span class="ml-2">{{ $comment->content ?? '' }}</span>
                </div>
            @endforeach

            @if(isset($post->comments_count) && $post->comments_count > $post->comments->count())
                <a href="{{ route('posts.show', $post->id) }}" class="inline-flex text-sm font-semibold text-energy transition hover:text-orange-600">View all {{ $post->comments_count }} comments</a>
            @endif
        </div>
    @endif

</article>
