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

        @php $postImage = $post->image_url ?? $post->embedded_image_url; @endphp
        @if($postImage)
            <div class="post-media">
                <img src="{{ $postImage }}" alt="Post image" class="post-image" loading="lazy" />
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
            <span data-post-like-stat>❤️ {{ $post->likes_count }} Likes</span>
            <span data-post-comment-stat>💬 {{ $post->comments->count() }} Comments</span>
        </div>

        <div class="post-actions post-actions-fb">
            @auth
                <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                    @csrf
                    <button type="submit" class="action-btn fb-action-btn @if($post->isLikedBy(auth()->id())) liked @endif">
                        <span class="action-icon" aria-hidden="true">👍</span>
                        <span class="action-label">Like</span>
                        <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                    </button>
                </form>
            @else
                <button class="action-btn fb-action-btn" disabled>
                    <span class="action-icon" aria-hidden="true">👍</span>
                    <span class="action-label">Like</span>
                    <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                </button>
            @endauth

            <a href="#comments-section" class="action-btn fb-action-btn">
                <span class="action-icon" aria-hidden="true">💬</span>
                <span class="action-label">Comment</span>
                <span class="action-count" data-comment-count>{{ $post->comments->count() }}</span>
            </a>
        </div>
    </div>

    <section class="comments-section modern-comments" id="comments-section" data-comments-modern data-mention-users='@json($mentionUsers)'>
        <div class="comments-section-header">
            <div>
                <p class="section-kicker">Conversation</p>
                <h2>Comments</h2>
            </div>
            <div class="comments-meta">{{ $comments->total() }} comments</div>
        </div>

        @auth
            <form action="{{ route('posts.comment', $post->id) }}" method="POST" enctype="multipart/form-data" class="comment-composer" data-comment-composer>
                @csrf
                <a href="{{ route('profile.show', auth()->id()) }}" class="comment-composer-avatar">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('avatars/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </a>

                <div class="comment-composer-body">
                    <div class="comment-input-shell">
                        <textarea
                            name="content"
                            class="comment-textarea js-comment-textarea"
                            placeholder="Write a public comment..."
                            maxlength="300"
                            rows="2"
                            required
                            data-mention-input></textarea>

                        <div class="comment-composer-tools">
                            <button type="button" class="composer-icon-btn" data-emoji-toggle aria-label="Add emoji">😊</button>
                            <label class="composer-icon-btn composer-file-btn">
                                📷
                                <input type="file" name="image" accept="image/*" class="comment-image-input js-comment-file-input" hidden>
                            </label>
                        </div>

                        <div class="emoji-picker" hidden data-emoji-picker>
                            <button type="button" data-emoji="😀">😀</button>
                            <button type="button" data-emoji="😁">😁</button>
                            <button type="button" data-emoji="😂">😂</button>
                            <button type="button" data-emoji="🤣">🤣</button>
                            <button type="button" data-emoji="😍">😍</button>
                            <button type="button" data-emoji="🥰">🥰</button>
                            <button type="button" data-emoji="😎">😎</button>
                            <button type="button" data-emoji="🤩">🤩</button>
                            <button type="button" data-emoji="😮">😮</button>
                            <button type="button" data-emoji="😢">😢</button>
                            <button type="button" data-emoji="😡">😡</button>
                            <button type="button" data-emoji="🙏">🙏</button>
                            <button type="button" data-emoji="👏">👏</button>
                            <button type="button" data-emoji="🤝">🤝</button>
                            <button type="button" data-emoji="💯">💯</button>
                            <button type="button" data-emoji="🔥">🔥</button>
                            <button type="button" data-emoji="✨">✨</button>
                            <button type="button" data-emoji="🎉">🎉</button>
                            <button type="button" data-emoji="💖">💖</button>
                            <button type="button" data-emoji="❤️">❤️</button>
                            <button type="button" data-emoji="👍">👍</button>
                            <button type="button" data-emoji="👎">👎</button>
                            <button type="button" data-emoji="😂">😂</button>
                            <button type="button" data-emoji="🤯">🤯</button>
                        </div>

                        <div class="mention-dropdown" hidden data-mention-dropdown></div>
                    </div>

                    <div class="comment-composer-footer">
                        <div class="composer-hint">Type @ to mention someone.</div>
                        <button type="submit" class="btn btn-primary comment-submit-btn">Post</button>
                    </div>

                    <div class="selected-file" hidden data-selected-file></div>
                </div>
            </form>
        @endauth

        @if($comments->isEmpty())
            <div class="comment-empty-state">
                <div class="comment-empty-icon">💬</div>
                <h3>Be the first to comment on this post</h3>
                <p>Start the conversation with a quick thought, emoji, or photo.</p>
            </div>
        @else
            <div class="comment-thread">
                @foreach($comments as $comment)
                    <article class="comment-card modern-comment-card" data-comment-id="{{ $comment->id }}">
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
                                            <button type="submit" class="action-btn comment-like-btn @if($comment->isLikedBy(auth()->id())) liked @endif">
                                                <span class="action-icon" aria-hidden="true">❤️</span>
                                                <span class="action-label">Like</span>
                                                <span class="comment-like-count" data-like-count>{{ $comment->likes_count ?? $comment->likes->count() }}</span>
                                            </button>
                                        </form>

                                        <button type="button" class="action-btn comment-reply-toggle" data-reply-toggle="reply-form-{{ $comment->id }}">Reply</button>

                                        @if(auth()->id() === $comment->user->id)
                                            <form action="{{ route('comments.delete', $comment->id) }}" method="POST" class="delete-form comment-delete-form">
                                                @csrf
                                                <button type="submit" class="delete-btn comment-delete-btn" onclick="return confirm('Delete comment?')">Delete</button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="comment-like-count">❤️ {{ $comment->likes_count ?? $comment->likes->count() }}</span>
                                    @endauth
                                </div>

                                @auth
                                    <form action="{{ route('comments.reply', $comment->id) }}" method="POST" enctype="multipart/form-data" class="comment-reply-form is-hidden" id="reply-form-{{ $comment->id }}" data-comment-reply-form>
                                        @csrf
                                        <textarea name="content" placeholder="Reply to {{ $comment->user->name }}..." maxlength="300" required></textarea>
                                        <div class="comment-reply-tools">
                                            <label class="composer-icon-btn composer-file-btn small">
                                                📷
                                                <input type="file" name="image" accept="image/*" class="comment-image-input" hidden>
                                            </label>
                                            <button type="submit" class="btn btn-secondary btn-small">Reply</button>
                                        </div>
                                    </form>
                                @endauth

                                @if($comment->replies->isNotEmpty())
                                    <div class="comment-replies">
                                        @foreach($comment->replies as $reply)
                                            <article class="comment-card modern-comment-card comment-reply-card" data-comment-id="{{ $reply->id }}">
                                                <div class="comment-card-main">
                                                    <a href="{{ route('profile.show', $reply->user->id) }}" class="comment-avatar-link">
                                                        @if($reply->user->avatar)
                                                            <img src="{{ asset('avatars/' . $reply->user->avatar) }}" alt="{{ $reply->user->name }}" class="comment-avatar">
                                                        @else
                                                            <span class="comment-avatar comment-avatar-fallback">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</span>
                                                        @endif
                                                    </a>

                                                    <div class="comment-body">
                                                        <div class="comment-bubble">
                                                            <div class="comment-bubble-header">
                                                                <a href="{{ route('profile.show', $reply->user->id) }}" class="comment-author">{{ $reply->user->name }}</a>
                                                                <span class="comment-time">{{ $reply->created_at->diffForHumans() }}</span>
                                                            </div>

                                                            <div class="comment-content">{!! nl2br(e($reply->content)) !!}</div>

                                                            @if($reply->image)
                                                                <div class="comment-media">
                                                                    <img src="{{ $reply->image }}" alt="Reply image" class="comment-image" loading="lazy">
                                                                </div>
                                                            @endif
                                                        </div>

                                                        <div class="comment-action-bar">
                                                            @auth
                                                                <form action="{{ route('comments.like', $reply->id) }}" method="POST" class="action-form">
                                                                    @csrf
                                                                    <button type="submit" class="action-btn comment-like-btn @if($reply->isLikedBy(auth()->id())) liked @endif">
                                                                        <span class="action-icon" aria-hidden="true">❤️</span>
                                                                        <span class="action-label">Like</span>
                                                                        <span class="comment-like-count" data-like-count>{{ $reply->likes_count ?? $reply->likes->count() }}</span>
                                                                    </button>
                                                                </form>

                                                                @if(auth()->id() === $reply->user->id)
                                                                    <form action="{{ route('comments.delete', $reply->id) }}" method="POST" class="delete-form comment-delete-form">
                                                                        @csrf
                                                                        <button type="submit" class="delete-btn comment-delete-btn" onclick="return confirm('Delete reply?')">Delete</button>
                                                                    </form>
                                                                @endif
                                                            @else
                                                                <span class="comment-like-count">❤️ {{ $reply->likes_count ?? $reply->likes->count() }}</span>
                                                            @endauth
                                                        </div>
                                                    </div>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @if($comments->hasPages())
        <div style="margin-top: 30px;">
            {{ $comments->links() }}
        </div>
    @endif
</div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const section = document.querySelector('[data-comments-modern]');
        if (!section) return;

        const mentionUsers = JSON.parse(section.dataset.mentionUsers || '[]');
        const composer = section.querySelector('[data-comment-composer]');
        const emojiPicker = section.querySelector('[data-emoji-picker]');
        const emojiToggle = section.querySelector('[data-emoji-toggle]');
        const textarea = section.querySelector('[data-mention-input]');
        const mentionDropdown = section.querySelector('[data-mention-dropdown]');
        const selectedFile = section.querySelector('[data-selected-file]');

        function autosize(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 180) + 'px';
        }

        function insertAtCursor(el, text) {
            const start = el.selectionStart ?? el.value.length;
            const end = el.selectionEnd ?? el.value.length;
            el.value = el.value.slice(0, start) + text + el.value.slice(end);
            const next = start + text.length;
            el.setSelectionRange(next, next);
            el.focus();
            autosize(el);
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }

        function renderMentions(query) {
            if (!mentionDropdown) return;
            const value = query.toLowerCase();
            const matches = mentionUsers.filter(function (user) {
                return user.name.toLowerCase().includes(value);
            }).slice(0, 6);

            if (!query || !matches.length) {
                mentionDropdown.hidden = true;
                mentionDropdown.innerHTML = '';
                return;
            }

            mentionDropdown.innerHTML = matches.map(function (user) {
                return '<button type="button" class="mention-option" data-mention-name="' + user.name.replace(/"/g, '&quot;') + '">@' + user.name + '</button>';
            }).join('');
            mentionDropdown.hidden = false;
        }

        function currentMentionQuery(el) {
            const beforeCaret = el.value.slice(0, el.selectionStart ?? el.value.length);
            const atIndex = beforeCaret.lastIndexOf('@');
            if (atIndex === -1) return '';
            const fragment = beforeCaret.slice(atIndex + 1);
            if (/\s/.test(fragment)) return '';
            return fragment;
        }

        if (textarea) {
            autosize(textarea);
            textarea.addEventListener('input', function () {
                autosize(textarea);
                renderMentions(currentMentionQuery(textarea));
            });

            textarea.addEventListener('click', function () {
                renderMentions(currentMentionQuery(textarea));
            });
        }

        if (emojiToggle && emojiPicker && textarea) {
            const emojis = Array.from(emojiPicker.querySelectorAll('[data-emoji]'));
            emojiToggle.addEventListener('click', function () {
                const shouldOpen = emojiPicker.hidden;
                emojiPicker.hidden = !shouldOpen;
                emojiPicker.classList.toggle('is-open', shouldOpen);
                mentionDropdown.hidden = true;
            });

            emojis.forEach(function (button) {
                button.addEventListener('click', function () {
                    insertAtCursor(textarea, button.getAttribute('data-emoji') || '');
                    emojiPicker.hidden = true;
                });
            });

            document.addEventListener('click', function (event) {
                if (!emojiPicker.contains(event.target) && !emojiToggle.contains(event.target)) {
                    emojiPicker.hidden = true;
                    emojiPicker.classList.remove('is-open');
                }
            });
        }

        if (mentionDropdown && textarea) {
            mentionDropdown.addEventListener('click', function (event) {
                const target = event.target.closest('[data-mention-name]');
                if (!target) return;

                const name = target.getAttribute('data-mention-name') || '';
                const beforeCaret = textarea.value.slice(0, textarea.selectionStart ?? textarea.value.length);
                const afterCaret = textarea.value.slice(textarea.selectionEnd ?? textarea.value.length);
                const atIndex = beforeCaret.lastIndexOf('@');
                if (atIndex === -1) return;

                textarea.value = beforeCaret.slice(0, atIndex) + '@' + name + ' ' + afterCaret;
                textarea.focus();
                autosize(textarea);
                mentionDropdown.hidden = true;
            });
        }

        document.querySelectorAll('[data-reply-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                const formId = button.getAttribute('data-reply-toggle');
                const form = document.getElementById(formId);
                if (!form) return;

                const isHidden = form.classList.contains('is-hidden');
                document.querySelectorAll('[data-comment-reply-form]').forEach(function (otherForm) {
                    if (otherForm !== form) otherForm.classList.add('is-hidden');
                });
                form.classList.toggle('is-hidden', !isHidden);

                const replyTextarea = form.querySelector('textarea');
                if (replyTextarea && !form.classList.contains('is-hidden')) {
                    replyTextarea.focus();
                }
            });
        });

        const fileInput = section.querySelector('.js-comment-file-input');
        if (fileInput && selectedFile) {
            fileInput.addEventListener('change', function () {
                const file = fileInput.files && fileInput.files[0];
                if (!file) {
                    selectedFile.hidden = true;
                    selectedFile.textContent = '';
                    return;
                }

                selectedFile.hidden = false;
                selectedFile.textContent = 'Attached: ' + file.name;
            });
        }
    });
    </script>
    @endpush
@endsection
