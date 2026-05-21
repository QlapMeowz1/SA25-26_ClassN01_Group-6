<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BadNet')</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('badnet-theme') || 'light';
            const root = document.documentElement;
            root.dataset.theme = savedTheme;
            root.classList.toggle('dark', savedTheme === 'dark');
            root.style.colorScheme = savedTheme;
        })();
    </script>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: ['class', '[data-theme="dark"]'],
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['"Be Vietnam Pro"', 'sans-serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        court: '#0A5C0A',
                        energy: '#FF6200',
                        amber: '#F59E0B',
                    },
                },
            },
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-redesign.css') }}">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                @auth
                    <a href="{{ route('dashboard') }}" class="brand">🏸 BadNet</a>
                @else
                    <a href="{{ route('home') }}" class="brand">🏸 BadNet</a>
                @endauth
            </div>

            @auth
                <div class="nav-menu nav-menu-primary">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">{{ __('ui.nav.dashboard') }}</a>
                    <a href="{{ route('challenges.index') }}" class="nav-link {{ request()->routeIs('challenges.*') ? 'nav-link-active' : '' }}">{{ __('ui.nav.challenges') }}</a>
                    <a href="{{ route('matches.index') }}" class="nav-link {{ request()->routeIs('matches.*') ? 'nav-link-active' : '' }}">{{ __('ui.nav.matches') }}</a>
                    <a href="{{ route('teams.index') }}" class="nav-link {{ request()->routeIs('teams.*') ? 'nav-link-active' : '' }}">{{ __('ui.nav.teams') }}</a>
                    <a href="{{ route('tournaments.index') }}" class="nav-link {{ request()->routeIs('tournaments.*') ? 'nav-link-active' : '' }}">{{ __('ui.nav.tournaments') }}</a>
                </div>

                <div class="nav-actions">
                    <div class="locale-switch" aria-label="{{ __('ui.locale.label') }}">
                        <a href="{{ route('locale.switch', 'en') }}" class="locale-switch-btn {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</a>
                        <a href="{{ route('locale.switch', 'vi') }}" class="locale-switch-btn {{ app()->getLocale() === 'vi' ? 'is-active' : '' }}">VI</a>
                    </div>

                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="{{ __('ui.theme.dark') }}">{{ __('ui.theme.dark') }}</button>

                    @php
                        $unreadNotifications = auth()->user()->notifications()->where('is_read', false)->count();
                    @endphp
                    <div class="nav-bell-wrapper">
                        <button type="button" id="navBell" class="nav-bell" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
                            <span aria-hidden="true">🔔</span>
                            @if($unreadNotifications > 0)
                                <span class="nav-bell-badge" id="navBellBadge">{{ $unreadNotifications }}</span>
                            @endif
                        </button>

                        <div class="nav-bell-dropdown" id="navBellDropdown" hidden>
                            <div class="nav-bell-header">
                                <strong>{{ __('ui.notifications.title') }}</strong>
                                <button type="button" id="markAllReadBtn" class="btn btn-link">{{ __('ui.notifications.mark_all_read') }}</button>
                            </div>
                            <div class="nav-bell-list" id="navBellList">
                                <p class="muted">Loading…</p>
                            </div>
                            <div class="nav-bell-footer">
                                <a href="{{ route('dashboard') }}#notifications">{{ __('ui.notifications.view_all') }}</a>
                            </div>
                        </div>
                    </div>

                    <details class="nav-user-menu">
                        <summary class="nav-user-trigger">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('avatars/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="nav-avatar">
                            @else
                                <span class="nav-avatar nav-avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                            <span class="nav-user-name">{{ auth()->user()->name }}</span>
                            <span class="nav-coins">{{ number_format(auth()->user()->virtual_coins ?? 0) }} 🪙</span>
                        </summary>

                        <div class="nav-dropdown">
                            <a href="{{ route('profile.show', auth()->id()) }}" class="nav-dropdown-link">{{ __('ui.nav.profile') }}</a>
                            <a href="{{ route('profile.edit') }}" class="nav-dropdown-link">{{ __('ui.nav.settings') }}</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="nav-dropdown-link nav-dropdown-button">{{ __('ui.nav.logout') }}</button>
                            </form>
                        </div>
                    </details>
                </div>
            @else
                <div class="nav-menu nav-menu-guest">
                    <div class="locale-switch" aria-label="{{ __('ui.locale.label') }}">
                        <a href="{{ route('locale.switch', 'en') }}" class="locale-switch-btn {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</a>
                        <a href="{{ route('locale.switch', 'vi') }}" class="locale-switch-btn {{ app()->getLocale() === 'vi' ? 'is-active' : '' }}">VI</a>
                    </div>

                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="{{ __('ui.theme.dark') }}">{{ __('ui.theme.dark') }}</button>
                    <a href="{{ route('login') }}" class="nav-link">{{ __('ui.nav.login') }}</a>
                    <a href="{{ route('register') }}" class="nav-link nav-link-accent">{{ __('ui.nav.register') }}</a>
                </div>
            @endauth
        </div>
    </nav>

    <main class="container main-content app-shell">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation -->
    @auth
    <nav class="mobile-bottom-nav">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.home') }}">
            <span class="mobile-nav-icon">📊</span>
            <span class="mobile-nav-label">{{ __('ui.nav.home') }}</span>
        </a>
        <a href="{{ route('challenges.index') }}" class="mobile-nav-item {{ request()->routeIs('challenges.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.challenges') }}">
            <span class="mobile-nav-icon">⚔️</span>
            <span class="mobile-nav-label">{{ __('ui.nav.challenges') }}</span>
        </a>
        <a href="{{ route('matches.index') }}" class="mobile-nav-item {{ request()->routeIs('matches.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.matches') }}">
            <span class="mobile-nav-icon">🎾</span>
            <span class="mobile-nav-label">{{ __('ui.nav.matches') }}</span>
        </a>
        <a href="{{ route('teams.index') }}" class="mobile-nav-item {{ request()->routeIs('teams.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.teams') }}">
            <span class="mobile-nav-icon">👥</span>
            <span class="mobile-nav-label">{{ __('ui.nav.teams') }}</span>
        </a>
        <a href="{{ route('tournaments.index') }}" class="mobile-nav-item {{ request()->routeIs('tournaments.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.tournaments') }}">
            <span class="mobile-nav-icon">🏆</span>
            <span class="mobile-nav-label">{{ __('ui.nav.tournaments') }}</span>
        </a>
    </nav>
    @endauth

    <footer class="footer">
        <p>&copy; {{ __('ui.footer.copyright') }}</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const root = document.documentElement;

            function updateThemeButton() {
                if (!themeToggle) return;
                const currentTheme = root.dataset.theme || 'light';
                themeToggle.textContent = currentTheme === 'dark' ? '{{ __('ui.theme.light') }}' : '{{ __('ui.theme.dark') }}';
                themeToggle.setAttribute('aria-label', currentTheme === 'dark' ? '{{ __('ui.theme.light') }}' : '{{ __('ui.theme.dark') }}');
            }

            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    const nextTheme = (root.dataset.theme === 'dark') ? 'light' : 'dark';
                    root.dataset.theme = nextTheme;
                    root.classList.toggle('dark', nextTheme === 'dark');
                    root.style.colorScheme = nextTheme;
                    localStorage.setItem('badnet-theme', nextTheme);
                    updateThemeButton();
                });

                updateThemeButton();
            }

            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                }, 3000);
            });

            // Notifications dropdown behavior
            const navBell = document.getElementById('navBell');
            const navBellDropdown = document.getElementById('navBellDropdown');
            const navBellList = document.getElementById('navBellList');
            const navBellBadge = document.getElementById('navBellBadge');
            const markAllReadBtn = document.getElementById('markAllReadBtn');

            async function fetchNotifications() {
                if (!navBellList) return;
                try {
                    const res = await fetch('{{ route('notifications.recent') }}', { credentials: 'same-origin' });
                    const data = await res.json();
                    renderNotifications(data.notifications || []);
                } catch (e) {
                    navBellList.innerHTML = '<p class="muted">Unable to load</p>';
                }
            }

            function renderNotifications(items) {
                if (!navBellList) return;
                if (!items.length) {
                    navBellList.innerHTML = '<p class="muted">No new notifications</p>';
                    if (navBellBadge) navBellBadge.remove();
                    return;
                }

                navBellList.innerHTML = items.map(n => {
                    const link = n.link || '#';
                    const icon = n.icon || '🔔';
                    const rawText = n.data.title ?? n.data.message ?? 'Update';
                    const parts = String(rawText).split(' ');
                    const head = parts.shift() || '';
                    const tail = parts.join(' ');
                    return `<a href="${escapeHtml(link)}" class="notification-item ${n.is_read ? 'read' : 'unread'}" data-link="${escapeHtml(link)}">
                        <div class="notification-icon">${escapeHtml(icon)}</div>
                        <div class="notification-body">
                            <div class="notification-text"><strong>${escapeHtml(head)}</strong>${tail ? ' ' + escapeHtml(tail) : ''}</div>
                            <div class="notification-meta">${n.time ?? ''}</div>
                        </div>
                    </a>`;
                }).join('');

                // make items clickable to navigate
                Array.from(navBellList.querySelectorAll('.notification-item')).forEach(function(el){
                    el.addEventListener('click', function(e){
                        // allow normal link behavior
                    });
                });

                // update badge
                const unread = items.filter(i => !i.is_read).length;
                if (navBellBadge) {
                    if (unread > 0) navBellBadge.textContent = unread; else navBellBadge.remove();
                }
            }

            function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]; }); }

            if (navBell && navBellDropdown) {
                navBell.addEventListener('click', function (e) {
                    e.preventDefault();
                    const isOpen = navBellDropdown.hasAttribute('hidden') === false;
                    if (isOpen) {
                        navBellDropdown.setAttribute('hidden','');
                        navBell.setAttribute('aria-expanded','false');
                    } else {
                        navBellDropdown.removeAttribute('hidden');
                        navBell.setAttribute('aria-expanded','true');
                        fetchNotifications();
                    }
                });

                document.addEventListener('click', function (ev) {
                    if (!navBell.contains(ev.target) && !navBellDropdown.contains(ev.target)) {
                        navBellDropdown.setAttribute('hidden','');
                        navBell.setAttribute('aria-expanded','false');
                    }
                });
            }

            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', async function () {
                    try {
                        await fetch('{{ route('notifications.markAll') }}', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '' ,
                                'Accept': 'application/json'
                            }
                        });
                        fetchNotifications();
                    } catch (e) {
                        console.error(e);
                    }
                });
            }

            // Countdown ticker for any element with data-target
            function updateCountdowns() {
                const els = document.querySelectorAll('[data-target]');
                const now = new Date();
                els.forEach(el => {
                    const target = new Date(el.getAttribute('data-target'));
                    if (isNaN(target)) return;
                    let diff = Math.max(0, Math.floor((target - now) / 1000));
                    const days = Math.floor(diff / 86400); diff %= 86400;
                    const hours = Math.floor(diff / 3600); diff %= 3600;
                    const minutes = Math.floor(diff / 60);
                    let parts = [];
                    if (days > 0) parts.push(days + 'd');
                    if (hours > 0) parts.push(hours + 'h');
                    if (minutes > 0) parts.push(minutes + 'm');
                    if (parts.length === 0) parts.push('less than 1m');
                    el.textContent = parts.slice(0,3).join(' ') + (days+hours+minutes > 0 ? ' left' : '');
                });
            }

            // initial update and then every minute
            updateCountdowns();
            setInterval(updateCountdowns, 60 * 1000);

            // Intercept like forms and submit via AJAX to avoid full page reload (prevents jumping to top)
            document.addEventListener('submit', async function (ev) {
                const form = ev.target;
                if (!form || !form.classList.contains('action-form')) return;

                const actionUrl = form.getAttribute('action') || '';
                if (!/\/like($|\/)/.test(actionUrl)) return; // only handle like endpoints

                ev.preventDefault();

                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrf = tokenMeta ? tokenMeta.getAttribute('content') : (form.querySelector('input[name="_token"]') ? form.querySelector('input[name="_token"]').value : '');

                try {
                    const res = await fetch(actionUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!res.ok) throw new Error('Network response not ok');

                    const data = await res.json();

                    // Update UI: toggle liked class and update count
                    const btn = form.querySelector('button') || form;
                    if (data.liked) btn.classList.add('liked'); else btn.classList.remove('liked');
                    btn.classList.add('like-pop');
                    setTimeout(function () { btn.classList.remove('like-pop'); }, 350);

                    const count = data.likes_count ?? data.likesCount ?? data.count;
                    const countEl = btn.querySelector('[data-like-count]') || btn.querySelector('.action-count') || btn.querySelector('.comment-like-count');
                    if (countEl && typeof count !== 'undefined') countEl.textContent = count;
                } catch (err) {
                    console.error('Like action failed', err);
                    // fallback to normal submit -> reload
                    form.submit();
                }
            });

            // Poll likes count periodically so changes by other users reflect on this page
            async function pollLikes() {
                const postEls = document.querySelectorAll('[data-post-id]');
                const ids = Array.from(new Set(Array.from(postEls).map(el => el.getAttribute('data-post-id')).filter(Boolean)));
                if (!ids.length) return;

                for (const id of ids) {
                    try {
                        const res = await fetch('/posts/' + encodeURIComponent(id) + '/likes-count', { credentials: 'same-origin', headers: { 'Accept': 'application/json' }});
                        if (!res.ok) continue;
                        const data = await res.json();

                        // update all elements for this post
                        const els = document.querySelectorAll('[data-post-id="' + id + '"]');
                        els.forEach(function(container) {
                            // update only like counters inside this container
                            const countEls = container.querySelectorAll('.fb-action-btn [data-like-count], .comment-like-btn [data-like-count], .comment-like-btn .comment-like-count');
                            countEls.forEach(function(c){ c.textContent = data.likes_count; });

                            const statLike = container.querySelector('[data-post-like-stat]');
                            if (statLike) {
                                const isUppercase = statLike.textContent.indexOf('Likes') !== -1;
                                statLike.textContent = '❤️ ' + data.likes_count + (isUppercase ? ' Likes' : ' likes');
                            }

                            // toggle liked class on like buttons
                            const likeButtons = container.querySelectorAll('.fb-action-btn, .comment-like-btn');
                            likeButtons.forEach(function (likeBtn) {
                                if (data.liked) likeBtn.classList.add('liked'); else likeBtn.classList.remove('liked');
                            });
                        });
                    } catch (e) {
                        // ignore individual failures
                    }
                }
            }

            // run first poll after short delay, then at interval
            setTimeout(pollLikes, 3000);
            setInterval(pollLikes, 15000);
        });
    </script>

    @stack('scripts')
</body>
</html>
