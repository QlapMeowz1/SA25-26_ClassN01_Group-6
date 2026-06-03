@php
    $isReply = $isReply ?? false;
    $showReplyForm = $showReplyForm ?? false;
    $canDelete = auth()->check() && auth()->id() === $comment->user->id;
    $likeCount = $comment->likes_count ?? $comment->likes->count();
@endphp

<article class="comment-card modern-comment-card {{ $isReply ? 'comment-reply-card' : '' }}" data-comment-id="{{ $comment->id }}">
    <div class="comment-card-main">
        <a href="{{ route('profile.show', $comment->user->id) }}" class="comment-avatar-link">
            @if($comment->user->avatar)
                <img src="{{ asset('avatars/' . $comment->user->avatar) }}" alt="{{ $comment->user->name }}" class="comment-avatar">
            @else
                <span class="comment-avatar comment-avatar-fallback">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
            @endif
        </a>

        <div class="comment-body">
            <div class="comment-bubble">
                <div class="comment-bubble-header">
                    <a href="{{ route('profile.show', $comment->user->id) }}" class="comment-author">{{ $comment->user->name }}</a>
                    <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                </div>

                <div class="comment-content">{!! nl2br(e($comment->content)) !!}</div>

                @if($comment->image)
                    <div class="comment-media">
                        <img src="{{ $comment->image }}" alt="Comment image" class="comment-image" loading="lazy">
                    </div>
                @endif
            </div>

            <div class="comment-action-bar">
                @auth
                    <form action="{{ route('comments.like', $comment->id) }}" method="POST" class="action-form">
                        @csrf
                        <button type="button" class="action-btn comment-like-btn @if($comment->isLikedBy(auth()->id())) liked @endif" data-like-trigger data-like-url="{{ route('comments.like', $comment->id) }}">
                            <span class="action-icon" aria-hidden="true">❤️</span>
                            <span class="action-label">{{ __('ui.post.like') }}</span>
                            <span class="comment-like-count" data-like-count>{{ $likeCount }}</span>
                        </button>
                    </form>

                    @if(! $isReply)
                        <button type="button" class="action-btn comment-reply-toggle" data-reply-toggle="reply-form-{{ $comment->id }}">{{ __('ui.post.reply') }}</button>
                    @endif

                    @if($canDelete)
                        <form action="{{ route('comments.delete', $comment->id) }}" method="POST" class="delete-form comment-delete-form">
                            @csrf
                            <button type="submit" class="delete-btn comment-delete-btn" onclick="return confirm('{{ __('ui.post.delete_comment_confirm') }}')">{{ __('ui.post.delete') }}</button>
                        </form>
                    @endif
                @else
                    <span class="comment-like-count">❤️ {{ $likeCount }}</span>
                @endauth
            </div>

            @if($showReplyForm)
                @auth
                    <form action="{{ route('comments.reply', $comment->id) }}" method="POST" enctype="multipart/form-data" class="comment-reply-form is-hidden" id="reply-form-{{ $comment->id }}" data-comment-reply-form>
                        @csrf
                        <textarea name="content" placeholder="{{ __('ui.post.reply_to') }} {{ $comment->user->name }}..." maxlength="300" required></textarea>
                        <div class="comment-reply-tools">
                            <label class="composer-icon-btn composer-file-btn small">
                                📷
                                <input type="file" name="image" accept="image/*" class="comment-image-input" hidden>
                            </label>
                                <button type="submit" class="btn btn-secondary btn-small">{{ __('ui.post.reply') }}</button>
                        </div>
                    </form>
                @endauth
            @endif

            @if(!$isReply && $comment->replies->isNotEmpty())
                <div class="comment-replies">
                    @foreach($comment->replies as $reply)
                        @include('posts.partials.comment_item', ['comment' => $reply, 'isReply' => true, 'showReplyForm' => false])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</article>
