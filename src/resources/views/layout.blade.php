<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BadNet')</title>
    <script>
        // Bootstrap theme BEFORE page renders (prevents flash)
        (function () {
            const userTheme = @json(auth()->user()?->theme ?? 'dark');
            const savedTheme = localStorage.getItem('badnet-theme') || userTheme || 'dark';
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            // Determine what to APPLY (resolve system to actual dark/light)
            let appliedTheme = savedTheme;
            if (savedTheme === 'system') {
                appliedTheme = prefersDark ? 'dark' : 'light';
            }
            
            // Apply to DOM immediately
            const html = document.documentElement;
            html.classList.toggle('dark', appliedTheme === 'dark');
            html.setAttribute('data-theme', appliedTheme);
            html.style.colorScheme = appliedTheme === 'dark' ? 'dark' : 'light';
        })();
        
        // Store current user ID for theme API sync
        window.currentUserId = @json(auth()->id());
    </script>
    @unless(request()->routeIs('admin.*'))
        <script>
            window.tailwind = window.tailwind || {};
            window.tailwind.config = {
                darkMode: ['class', '[data-theme="dark"]'],
                theme: {
                    extend: {
                        fontFamily: {
                            heading: ['"Barlow Condensed"', 'sans-serif'],
                            body: ['Inter', 'sans-serif'],
                            mono: ['"JetBrains Mono"', 'monospace'],
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
    @endunless
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-redesign.css') }}">
    @vite('resources/js/app.js')
    <script src="{{ asset('js/theme-manager.js') }}"></script>
</head>
<body class="{{ request()->routeIs('admin.*') ? 'admin-route' : '' }}" data-page="{{ request()->routeIs('dashboard') ? 'dashboard' : (request()->routeIs('admin.*') ? 'admin' : 'app') }}">
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
                    @if(auth()->user()->hasAdminAccess())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'nav-link-active' : '' }}">Admin</a>
                    @endif
                </div>

                <div class="nav-actions">
                    <div class="locale-switch" aria-label="{{ __('ui.locale.label') }}">
                        <a href="{{ route('locale.switch', 'en') }}" class="locale-switch-btn {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</a>
                        <a href="{{ route('locale.switch', 'vi') }}" class="locale-switch-btn {{ app()->getLocale() === 'vi' ? 'is-active' : '' }}">VI</a>
                    </div>

                    <!-- Theme Dropdown Menu -->
                    <div class="theme-dropdown" id="themeDropdown">
                        <button type="button" class="theme-dropdown-btn" id="themeDropdownBtn" aria-label="Select theme" aria-expanded="false">
                            <span id="themeDropdownIcon">🌙</span>
                        </button>
                        <div class="theme-dropdown-menu" id="themeDropdownMenu" role="menu">
                            <button type="button" class="theme-option" data-theme="light" role="menuitem">
                                <span class="theme-icon">☀️</span>
                                <span class="theme-label">Light Mode</span>
                            </button>
                            <button type="button" class="theme-option" data-theme="dark" role="menuitem">
                                <span class="theme-icon">🌙</span>
                                <span class="theme-label">Dark Mode</span>
                            </button>
                            <button type="button" class="theme-option" data-theme="system" role="menuitem">
                                <span class="theme-icon">🖥️</span>
                                <span class="theme-label">System</span>
                            </button>
                        </div>
                    </div>

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
                            <div class="nav-bell-tabs" role="tablist" aria-label="Notification filters">
                                <button type="button" class="nav-bell-tab is-active" data-notification-tab="all">All</button>
                                <button type="button" class="nav-bell-tab" data-notification-tab="interactions">Interactions</button>
                                <button type="button" class="nav-bell-tab" data-notification-tab="matches">Matches</button>
                                <button type="button" class="nav-bell-tab" data-notification-tab="betting">Betting / Wallet</button>
                                <button type="button" class="nav-bell-tab" data-notification-tab="system">System</button>
                            </div>
                            <div class="nav-bell-list" id="navBellList">
                                <p class="muted">Loading…</p>
                            </div>
                            <div class="nav-bell-footer">
                                <a href="{{ route('notifications.index') }}">{{ __('ui.notifications.view_all') }}</a>
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
                            @if(auth()->user()->hasAdminAccess())
                                <a href="{{ route('admin.dashboard') }}" class="nav-dropdown-link">Admin Console</a>
                            @endif
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

    <main class="container main-content app-shell {{ request()->routeIs('admin.*') ? 'admin-app-shell' : '' }}">
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
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19h16" />
                    <path d="M7 19V9" />
                    <path d="M12 19V5" />
                    <path d="M17 19v-7" />
                </svg>
            </span>
            <span class="mobile-nav-label">{{ __('ui.nav.home') }}</span>
        </a>
        <a href="{{ route('challenges.index') }}" class="mobile-nav-item {{ request()->routeIs('challenges.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.challenges') }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 3l7 7-4 1-2 4-4-2-4 4-4-4 4-4 2-4 5-2z" />
                </svg>
            </span>
            <span class="mobile-nav-label">{{ __('ui.nav.challenges') }}</span>
        </a>
        <a href="{{ route('matches.index') }}" class="mobile-nav-item {{ request()->routeIs('matches.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.matches') }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="7" />
                    <path d="M9 9c3 1 4 4 6 6" />
                </svg>
            </span>
            <span class="mobile-nav-label">{{ __('ui.nav.matches') }}</span>
        </a>
        <a href="{{ route('teams.index') }}" class="mobile-nav-item {{ request()->routeIs('teams.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.teams') }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 18v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1" />
                    <circle cx="10" cy="8" r="3" />
                    <path d="M20 18v-1a3 3 0 0 0-2-2.8" />
                    <path d="M15 5.2a3 3 0 0 1 0 5.6" />
                </svg>
            </span>
            <span class="mobile-nav-label">{{ __('ui.nav.teams') }}</span>
        </a>
        <a href="{{ route('tournaments.index') }}" class="mobile-nav-item {{ request()->routeIs('tournaments.*') ? 'mobile-nav-active' : '' }}" title="{{ __('ui.nav.tournaments') }}">
            <span class="mobile-nav-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 4h8v4a4 4 0 0 1-8 0V4z" />
                    <path d="M6 6H4a2 2 0 0 0 2 2" />
                    <path d="M18 6h2a2 2 0 0 1-2 2" />
                    <path d="M12 12v4" />
                    <path d="M8 20h8" />
                </svg>
            </span>
            <span class="mobile-nav-label">{{ __('ui.nav.tournaments') }}</span>
        </a>
        @if(auth()->user()->hasAdminAccess())
            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('admin.*') ? 'mobile-nav-active' : '' }}" title="Admin">
                <span class="mobile-nav-icon" aria-hidden="true">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 3l7 4v5c0 5-3.4 8.4-7 10-3.6-1.6-7-5-7-10V7l7-4z" />
                        <path d="M9 12l2 2 4-5" />
                    </svg>
                </span>
                <span class="mobile-nav-label">Admin</span>
            </a>
        @endif
    </nav>
    @endauth

    <footer class="footer">
        <p>&copy; {{ __('ui.footer.copyright') }}</p>
    </footer>

    <script>
        /**
         * Theme dropdown menu handler
         * Allows users to directly select their preferred theme
         */
        document.addEventListener('DOMContentLoaded', function() {
            const themeDropdown = document.getElementById('themeDropdown');
            const themeDropdownBtn = document.getElementById('themeDropdownBtn');
            const themeDropdownMenu = document.getElementById('themeDropdownMenu');
            const themeOptions = document.querySelectorAll('.theme-option');
            const themeDropdownIcon = document.getElementById('themeDropdownIcon');

            if (!themeDropdownBtn || !window.themeManager) return;

            /**
             * Update dropdown button to show current theme icon
             */
            function updateDropdownButton() {
                const saved = window.themeManager.getSavedTheme();
                const iconMap = {
                    'light': '☀️',
                    'dark': '🌙',
                    'system': '🖥️'
                };
                themeDropdownIcon.textContent = iconMap[saved] || '🌙';
                
                // Update active state on menu items
                themeOptions.forEach(option => {
                    const optionTheme = option.getAttribute('data-theme');
                    option.classList.toggle('is-active', optionTheme === saved);
                });
            }

            /**
             * Open/close dropdown menu
             */
            function toggleDropdown() {
                const isOpen = themeDropdownMenu.classList.contains('is-open');
                if (isOpen) {
                    themeDropdownMenu.classList.remove('is-open');
                    themeDropdownBtn.setAttribute('aria-expanded', 'false');
                } else {
                    themeDropdownMenu.classList.add('is-open');
                    themeDropdownBtn.setAttribute('aria-expanded', 'true');
                }
            }

            /**
             * Close dropdown menu
             */
            function closeDropdown() {
                themeDropdownMenu.classList.remove('is-open');
                themeDropdownBtn.setAttribute('aria-expanded', 'false');
            }

            /**
             * Handle theme option selection
             */
            themeOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const theme = this.getAttribute('data-theme');
                    window.themeManager.setTheme(theme);
                    updateDropdownButton();
                    closeDropdown();
                });
            });

            /**
             * Toggle dropdown on button click
             */
            themeDropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleDropdown();
            });

            /**
             * Close dropdown when clicking outside
             */
            document.addEventListener('click', function(e) {
                if (!themeDropdown.contains(e.target)) {
                    closeDropdown();
                }
            });

            /**
             * Update dropdown when theme changes via other means
             */
            window.addEventListener('themeChange', updateDropdownButton);

            /**
             * Initial button state
             */
            updateDropdownButton();

            // Fade out alerts after 3 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                }, 3000);
            });
        });
        </script>

        <script>
            // Notifications dropdown behavior
            const navBell = document.getElementById('navBell');
            const navBellDropdown = document.getElementById('navBellDropdown');
            const navBellList = document.getElementById('navBellList');
            let navBellBadge = document.getElementById('navBellBadge');
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            const notificationTabs = Array.from(document.querySelectorAll('[data-notification-tab]'));
            let allNotifications = [];
            let currentNotificationTab = 'all';

            function csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value
                    || '';
            }

            async function fetchNotifications() {
                if (!navBellList) return;
                try {
                    const res = await fetch('{{ route('notifications.recent') }}', { credentials: 'same-origin' });
                    const data = await res.json();
                    allNotifications = data.notifications || [];
                    renderNotifications();
                    updateNotificationBadge(data.unread_count ?? allNotifications.filter(i => !i.is_read).length);
                } catch (e) {
                    navBellList.innerHTML = '<p class="muted">Unable to load</p>';
                }
            }

            function renderNotifications() {
                if (!navBellList) return;
                const items = currentNotificationTab === 'all'
                    ? allNotifications
                    : allNotifications.filter(n => (n.category || 'system') === currentNotificationTab);

                if (!items.length) {
                    navBellList.innerHTML = '<p class="muted notification-empty">No notifications in this tab</p>';
                    return;
                }

                navBellList.innerHTML = items.map(n => {
                    const link = n.link || '#';
                    const icon = n.icon || '🔔';
                    const rawText = n.data.title ?? n.data.message ?? 'Update';
                    const parts = String(rawText).split(' ');
                    const head = parts.shift() || '';
                    const tail = parts.join(' ');
                    const actions = Array.isArray(n.actions) && n.actions.length
                        ? `<div class="notification-actions">${n.actions.map(action => `
                            <button type="button"
                                class="notification-action notification-action--${escapeHtml(action.tone || 'default')}"
                                data-notification-action
                                data-action-url="${escapeHtml(action.url)}"
                                data-action-method="${escapeHtml(action.method || 'POST')}">
                                ${escapeHtml(action.label)}
                            </button>`).join('')}</div>`
                        : '';
                    const controls = String(n.id).startsWith('demo-') ? '' : `
                        <div class="notification-controls" aria-label="Notification actions">
                            <button type="button" class="notification-control" data-notification-open title="Open notification">Open</button>
                            <button type="button" class="notification-control" data-notification-unread title="Mark as unread">Unread</button>
                            <button type="button" class="notification-control ${n.is_pinned ? 'is-active' : ''}" data-notification-pin title="${n.is_pinned ? 'Unpin notification' : 'Pin notification'}">
                                ${n.is_pinned ? 'Pinned' : 'Pin'}
                            </button>
                        </div>`;

                    return `<article
                        class="notification-item notification-tone-${escapeHtml(n.tone || 'neutral')} ${n.is_read ? 'read' : 'unread'} ${n.is_pinned ? 'is-pinned' : ''}"
                        data-notification-id="${escapeHtml(n.id)}"
                        data-link="${escapeHtml(link)}">
                        <div class="notification-icon">${escapeHtml(icon)}</div>
                        <div class="notification-body">
                            <button type="button" class="notification-content" data-notification-open>
                                <span class="notification-text"><strong>${escapeHtml(head)}</strong>${tail ? ' ' + escapeHtml(tail) : ''}</span>
                            </button>
                            <div class="notification-meta">
                                <span>${escapeHtml(n.time ?? '')}</span>
                                <span class="notification-category">${escapeHtml(categoryLabel(n.category))}</span>
                                ${n.is_pinned ? '<span class="notification-pinned-label">Pinned</span>' : ''}
                            </div>
                            ${actions}
                            ${controls}
                        </div>
                    </article>`;
                }).join('');

                Array.from(navBellList.querySelectorAll('.notification-item')).forEach(function(el){
                    Array.from(el.querySelectorAll('[data-notification-open]')).forEach(function(openButton) {
                        openButton.addEventListener('click', async function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        const id = el.getAttribute('data-notification-id');
                        const link = el.getAttribute('data-link') || '#';

                        if (id && !String(id).startsWith('demo-')) {
                            await markNotificationRead(id);
                        }

                        if (link !== '#') {
                            window.location.href = link;
                        }
                        });
                    });
                });

                Array.from(navBellList.querySelectorAll('[data-notification-action]')).forEach(function(button) {
                    button.addEventListener('click', async function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        button.disabled = true;

                        try {
                            await fetch(button.getAttribute('data-action-url'), {
                                method: button.getAttribute('data-action-method') || 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken(),
                                    'Accept': 'application/json'
                                }
                            });
                            await fetchNotifications();
                        } catch (error) {
                            button.disabled = false;
                        }
                    });
                });

                Array.from(navBellList.querySelectorAll('[data-notification-unread]')).forEach(function(button) {
                    button.addEventListener('click', async function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const item = button.closest('.notification-item');
                        const id = item?.getAttribute('data-notification-id');
                        if (!id) return;

                        button.disabled = true;
                        try {
                            const response = await fetch(`/notifications/${encodeURIComponent(id)}/unread`, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken(),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (!response.ok) throw new Error('Unable to mark notification as unread');
                            allNotifications = allNotifications.map(notification =>
                                String(notification.id) === String(id)
                                    ? { ...notification, is_read: false }
                                    : notification
                            );
                            renderNotifications();
                            updateNotificationBadge(allNotifications.filter(notification => !notification.is_read).length);
                        } catch (error) {
                            button.disabled = false;
                            console.error(error);
                        }
                    });
                });

                Array.from(navBellList.querySelectorAll('[data-notification-pin]')).forEach(function(button) {
                    button.addEventListener('click', async function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const item = button.closest('.notification-item');
                        const id = item?.getAttribute('data-notification-id');
                        if (!id) return;

                        button.disabled = true;
                        try {
                            const response = await fetch(`/notifications/${encodeURIComponent(id)}/pin`, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken(),
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (!response.ok) throw new Error('Unable to update pin');
                            const payload = await response.json();
                            allNotifications = allNotifications
                                .map(notification =>
                                    String(notification.id) === String(id)
                                        ? { ...notification, is_pinned: Boolean(payload.is_pinned) }
                                        : notification
                                )
                                .sort((a, b) => Number(Boolean(b.is_pinned)) - Number(Boolean(a.is_pinned)));
                            renderNotifications();
                        } catch (error) {
                            button.disabled = false;
                            console.error(error);
                        }
                    });
                });
            }

            async function markNotificationRead(id) {
                try {
                    const response = await fetch(`/notifications/${encodeURIComponent(id)}/read`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept': 'application/json'
                        }
                    });
                    if (!response.ok) return;
                    allNotifications = allNotifications.map(notification =>
                        String(notification.id) === String(id)
                            ? { ...notification, is_read: true }
                            : notification
                    );
                    updateNotificationBadge(allNotifications.filter(notification => !notification.is_read).length);
                } catch (e) {
                    console.error(e);
                }
            }

            function updateNotificationBadge(unread) {
                if (!navBell) return;
                if (unread > 0) {
                    if (!navBellBadge) {
                        navBellBadge = document.createElement('span');
                        navBellBadge.className = 'nav-bell-badge';
                        navBellBadge.id = 'navBellBadge';
                        navBell.appendChild(navBellBadge);
                    }
                    navBellBadge.textContent = unread;
                } else if (navBellBadge) {
                    navBellBadge.remove();
                    navBellBadge = null;
                }
            }

            function categoryLabel(category) {
                return {
                    interactions: 'Interactions',
                    matches: 'Matches',
                    betting: 'Betting',
                    system: 'System'
                }[category] || 'System';
            }

            function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]; }); }

            notificationTabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    currentNotificationTab = tab.getAttribute('data-notification-tab') || 'all';
                    notificationTabs.forEach(item => item.classList.toggle('is-active', item === tab));
                    renderNotifications();
                });
            });

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
                markAllReadBtn.addEventListener('click', async function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    markAllReadBtn.disabled = true;
                    try {
                        const response = await fetch('{{ route('notifications.markAll') }}', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken(),
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Unable to mark notifications as read');
                        }

                        const payload = await response.json().catch(() => ({ unread_count: 0 }));

                        allNotifications = allNotifications.map(item => ({ ...item, is_read: true }));
                        renderNotifications();
                        updateNotificationBadge(payload.unread_count ?? 0);
                        fetchNotifications();
                    } catch (e) {
                        console.error(e);
                    } finally {
                        markAllReadBtn.disabled = false;
                    }
                });
            }

            if (navBell) {
                fetchNotifications();
                setInterval(fetchNotifications, 45000);
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

            async function toggleLike(button) {
                const actionUrl = button.getAttribute('data-like-url') || '';
                if (!actionUrl || button.dataset.likeBusy === 'true') return;

                const form = button.closest('form');
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const csrf = tokenMeta ? tokenMeta.getAttribute('content') : (form && form.querySelector('input[name="_token"]') ? form.querySelector('input[name="_token"]').value : '');
                const isPostLike = /\/posts\/[^/]+\/like$/.test(new URL(actionUrl, window.location.origin).pathname);

                try {
                    button.dataset.likeBusy = 'true';
                    button.disabled = true;

                    const res = await fetch(actionUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!res.ok) throw new Error('Network response not ok');

                    const data = await res.json();
                    const count = data.likes_count ?? data.likesCount ?? data.count;

                    if (data.liked) button.classList.add('liked'); else button.classList.remove('liked');
                    button.classList.add('like-pop');
                    setTimeout(function () { button.classList.remove('like-pop'); }, 350);

                    const countEls = button.querySelectorAll ? button.querySelectorAll('[data-like-count]') : [];
                    const fallbackCountEls = form ? form.querySelectorAll('[data-like-count]') : [];
                    (countEls.length ? countEls : fallbackCountEls).forEach(function (countEl) {
                        if (typeof count !== 'undefined') countEl.textContent = count;
                    });

                    const container = button.closest('[data-post-id]') || (form ? form.closest('[data-post-id]') : null);
                    if (container) {
                        const statLike = container.querySelector('[data-post-like-stat]');
                        if (isPostLike && statLike && typeof count !== 'undefined') {
                            const isUppercase = statLike.textContent.indexOf('Likes') !== -1;
                            statLike.textContent = '❤️ ' + count + (isUppercase ? ' Likes' : ' likes');
                        }
                    }
                } catch (err) {
                    console.error('Like action failed', err);
                } finally {
                    button.dataset.likeBusy = 'false';
                    button.disabled = false;
                }
            }

            document.addEventListener('click', function (ev) {
                const btn = ev.target.closest('[data-like-trigger]');
                if (!btn) return;

                ev.preventDefault();
                ev.stopPropagation();
                toggleLike(btn);
            }, true);

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
                            // update only the post like button. Comment likes have their own endpoint/state.
                            const likeButtons = Array.from(container.querySelectorAll('[data-like-trigger]')).filter(function (likeBtn) {
                                const url = likeBtn.getAttribute('data-like-url') || '';
                                return /\/posts\/[^/]+\/like$/.test(new URL(url, window.location.origin).pathname);
                            });

                            likeButtons.forEach(function (likeBtn) {
                                likeBtn.querySelectorAll('[data-like-count]').forEach(function(c){ c.textContent = data.likes_count; });
                                if (data.liked) likeBtn.classList.add('liked'); else likeBtn.classList.remove('liked');
                            });

                            const statLike = container.querySelector('[data-post-like-stat]');
                            if (statLike) {
                                const isUppercase = statLike.textContent.indexOf('Likes') !== -1;
                                statLike.textContent = '❤️ ' + data.likes_count + (isUppercase ? ' Likes' : ' likes');
                            }
                        });
                    } catch (e) {
                        // ignore individual failures
                    }
                }
            }

            // run first poll after short delay, then at interval
            setTimeout(pollLikes, 3000);
            setInterval(pollLikes, 15000);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-admin-cursor-chart]').forEach(function (chart) {
                const values = (chart.getAttribute('data-admin-cursor-chart') || '')
                    .split('|')
                    .map((value) => value.trim())
                    .filter(Boolean);

                if (!values.length) return;

                let tooltip = chart.querySelector('.admin-cursor-tooltip');
                if (!tooltip) {
                    tooltip = document.createElement('div');
                    tooltip.className = 'admin-cursor-tooltip';
                    chart.appendChild(tooltip);
                }

                function updateTooltip(event) {
                    const rect = chart.getBoundingClientRect();
                    const x = Math.min(Math.max(event.clientX - rect.left, 0), rect.width);
                    const y = Math.min(Math.max(event.clientY - rect.top, 0), rect.height);
                    const ratio = rect.width > 0 ? x / rect.width : 0;
                    const index = Math.min(values.length - 1, Math.max(0, Math.floor(ratio * values.length)));

                    tooltip.textContent = values[index];
                    tooltip.style.left = x + 'px';
                    tooltip.style.top = y + 'px';
                    chart.classList.add('is-chart-hovering');
                }

                chart.addEventListener('mousemove', updateTooltip);
                chart.addEventListener('mouseenter', updateTooltip);
                chart.addEventListener('mouseleave', function () {
                    chart.classList.remove('is-chart-hovering');
                });
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
