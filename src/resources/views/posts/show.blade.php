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
                        <button type="submit" class="delete-btn" onclick="return confirm('{{ __('ui.post.delete_post_confirm') }}')">{{ __('ui.post.delete') }}</button>
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
            <span data-post-like-stat>❤️ {{ $post->likes_count }} {{ __('ui.post.likes') }}</span>
            <span data-post-comment-stat>💬 {{ $post->comments->count() }} {{ __('ui.post.comments') }}</span>
        </div>

        <div class="post-actions post-actions-fb">
            @auth
                <form action="{{ route('posts.like', $post->id) }}" method="POST" class="action-form">
                    @csrf
                    <button type="button" class="action-btn fb-action-btn @if($post->isLikedBy(auth()->id())) liked @endif" data-like-trigger data-like-url="{{ route('posts.like', $post->id) }}">
                        <span class="action-icon" aria-hidden="true">👍</span>
                        <span class="action-label">{{ __('ui.post.like') }}</span>
                        <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                    </button>
                </form>
            @else
                <button class="action-btn fb-action-btn" disabled>
                    <span class="action-icon" aria-hidden="true">👍</span>
                    <span class="action-label">{{ __('ui.post.like') }}</span>
                    <span class="action-count" data-like-count>{{ $post->likes_count }}</span>
                </button>
            @endauth

            <a href="#comments-section" class="action-btn fb-action-btn">
                <span class="action-icon" aria-hidden="true">💬</span>
                <span class="action-label">{{ __('ui.post.comment') }}</span>
                <span class="action-count" data-comment-count>{{ $post->comments->count() }}</span>
            </a>
        </div>
    </div>

    <section class="comments-section modern-comments" id="comments-section" data-comments-modern data-mention-users='@json($mentionUsers)'>
        <div class="comments-section-header">
            <div>
                <p class="section-kicker">{{ __('ui.post.view_comments') }}</p>
                <h2>{{ __('ui.post.comments') }}</h2>
            </div>
            <div class="comments-meta">{{ $comments->total() }} {{ __('ui.post.comments') }}</div>
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
                            placeholder="{{ __('ui.post.comment_placeholder') ?? 'Write a public comment...' }}"
                            maxlength="300"
                            rows="2"
                            required
                            data-mention-input></textarea>

                        <div class="comment-composer-tools">
                            <button type="button" class="composer-icon-btn" data-emoji-toggle aria-label="{{ __('ui.post.add_emoji') ?? 'Add emoji' }}">😊</button>
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
                        <div class="composer-hint">{{ __('ui.post.mention_hint') ?? 'Type @ to mention someone.' }}</div>
                        <button type="submit" class="btn btn-primary comment-submit-btn">{{ __('ui.post.comment') }}</button>
                    </div>

                    <div class="selected-file" hidden data-selected-file></div>
                </div>
            </form>
        @endauth

        @if($comments->isEmpty())
            <div class="comment-empty-state" data-comment-empty-state>
                <div class="comment-empty-icon">💬</div>
                <h3>{{ __('ui.post.no_comments_title') ?? 'Be the first to comment on this post' }}</h3>
                <p>{{ __('ui.post.no_comments_body') ?? 'Start the conversation with a quick thought, emoji, or photo.' }}</p>
            </div>
        @endif

        <div class="comment-thread" data-comment-thread @if($comments->isEmpty()) hidden @endif>
            @foreach($comments as $comment)
                @include('posts.partials.comment_item', ['comment' => $comment, 'isReply' => false, 'showReplyForm' => true])
            @endforeach
        </div>
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
        const thread = section.querySelector('[data-comment-thread]');
        const emptyState = section.querySelector('[data-comment-empty-state]');
        const commentsMeta = section.querySelector('.comments-meta');
        const postCommentStat = section.querySelector('[data-post-comment-stat]');
        const postCommentCount = section.querySelector('[data-comment-count]');

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

        function setFormLoading(form, loading) {
            if (!form) return;
            const button = form.querySelector('button[type="submit"]');
            if (!button) return;

            if (loading) {
                if (!button.dataset.originalLabel) {
                    button.dataset.originalLabel = button.textContent.trim();
                }
                button.disabled = true;
                button.textContent = 'Sending...';
            } else {
                button.disabled = false;
                if (button.dataset.originalLabel) {
                    button.textContent = button.dataset.originalLabel;
                }
            }
        }

        function updateCommentTotals(nextCount) {
            if (typeof nextCount !== 'number') return;
            if (commentsMeta) {
                commentsMeta.textContent = nextCount + ' {{ __('ui.post.comments') }}';
            }
            if (postCommentStat) {
                postCommentStat.textContent = '💬 ' + nextCount + ' {{ __('ui.post.comments') }}';
            }
            if (postCommentCount) {
                postCommentCount.textContent = nextCount;
            }
        }

        function ensureThreadVisible() {
            if (thread) thread.hidden = false;
            if (emptyState) emptyState.hidden = true;
        }

        function insertCommentHtml(html, isReply, parentId) {
            if (!html) return;
            ensureThreadVisible();

            if (!isReply) {
                if (!thread) return;
                thread.insertAdjacentHTML('afterbegin', html);
                return;
            }

            if (!parentId) return;
            const parentComment = section.querySelector('[data-comment-id="' + parentId + '"]');
            if (!parentComment) return;

            let repliesContainer = parentComment.querySelector('.comment-replies');
            if (!repliesContainer) {
                repliesContainer = document.createElement('div');
                repliesContainer.className = 'comment-replies';
                const body = parentComment.querySelector('.comment-body');
                if (body) {
                    body.appendChild(repliesContainer);
                }
            }

            repliesContainer.insertAdjacentHTML('beforeend', html);
        }

        async function submitCommentForm(form) {
            const actionUrl = form.getAttribute('action') || '';
            const isReply = form.classList.contains('comment-reply-form');
            const formData = new FormData(form);

            formData.append('X-Requested-With', 'XMLHttpRequest');

            setFormLoading(form, true);

            try {
                const res = await fetch(actionUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                if (!res.ok) throw new Error('Network response not ok');

                const data = await res.json();
                insertCommentHtml(data.html, isReply, data.parent_comment_id);

                if (typeof data.top_level_comments_count === 'number') {
                    updateCommentTotals(data.top_level_comments_count);
                }

                const replyForm = form.querySelector('textarea');
                if (replyForm) {
                    replyForm.value = '';
                    autosize(replyForm);
                }

                const file = form.querySelector('input[type="file"]');
                if (file) file.value = '';

                if (selectedFile) {
                    selectedFile.hidden = true;
                    selectedFile.textContent = '';
                }

                if (isReply) {
                    form.classList.add('is-hidden');
                }
            } catch (error) {
                console.error('Comment submit failed', error);
            } finally {
                setFormLoading(form, false);
            }
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

        if (composer) {
            composer.addEventListener('submit', function (event) {
                event.preventDefault();
                submitCommentForm(composer);
            });
        }

        document.querySelectorAll('[data-comment-reply-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                submitCommentForm(form);
            });
        });
    });
    </script>
    @endpush
@endsection
