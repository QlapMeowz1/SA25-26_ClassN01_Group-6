<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BadNet')</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('badnet-theme') || 'light';
            document.documentElement.dataset.theme = savedTheme;
            document.documentElement.style.colorScheme = savedTheme;
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
                    <a href="{{ route('challenges.index') }}" class="nav-link {{ request()->routeIs('challenges.*') ? 'nav-link-active' : '' }}">Challenges</a>
                    <a href="{{ route('matches.index') }}" class="nav-link {{ request()->routeIs('matches.*') ? 'nav-link-active' : '' }}">Matches</a>
                    <a href="{{ route('teams.index') }}" class="nav-link {{ request()->routeIs('teams.*') ? 'nav-link-active' : '' }}">Teams</a>
                    <a href="{{ route('tournaments.index') }}" class="nav-link {{ request()->routeIs('tournaments.*') ? 'nav-link-active' : '' }}">Tournaments</a>
                </div>

                <div class="nav-actions">
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Theme</button>

                    @php
                        $unreadNotifications = auth()->user()->notifications()->where('is_read', false)->count();
                    @endphp
                    <a href="{{ route('dashboard') }}#notifications" class="nav-bell" aria-label="Notifications">
                        <span aria-hidden="true">🔔</span>
                        @if($unreadNotifications > 0)
                            <span class="nav-bell-badge">{{ $unreadNotifications }}</span>
                        @endif
                    </a>

                    <details class="nav-user-menu">
                        <summary class="nav-user-trigger">
                            @if(auth()->user()->avatar)
                                <img src="{{ asset('avatars/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="nav-avatar">
                            @else
                                <span class="nav-avatar nav-avatar-fallback">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            @endif
                            <span class="nav-user-name">{{ auth()->user()->name }}</span>
                        </summary>

                        <div class="nav-dropdown">
                            <a href="{{ route('profile.show', auth()->id()) }}" class="nav-dropdown-link">Profile</a>
                            <a href="{{ route('profile.edit') }}" class="nav-dropdown-link">Settings</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="nav-dropdown-link nav-dropdown-button">Logout</button>
                            </form>
                        </div>
                    </details>
                </div>
            @else
                <div class="nav-menu nav-menu-guest">
                    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle theme">Toggle Theme</button>
                    <a href="{{ route('login') }}" class="nav-link">Login</a>
                    <a href="{{ route('register') }}" class="nav-link nav-link-accent">Register</a>
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

    <footer class="footer">
        <p>&copy; 2024 BadNet - Badminton Social Network. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const root = document.documentElement;

            function updateThemeButton() {
                const currentTheme = root.dataset.theme || 'light';
                themeToggle.textContent = currentTheme === 'dark' ? 'Light Mode' : 'Dark Mode';
            }

            themeToggle.addEventListener('click', function() {
                const nextTheme = (root.dataset.theme === 'dark') ? 'light' : 'dark';
                root.dataset.theme = nextTheme;
                root.style.colorScheme = nextTheme;
                localStorage.setItem('badnet-theme', nextTheme);
                updateThemeButton();
            });

            updateThemeButton();

            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade-out');
                }, 3000);
            });
        });
    </script>
</body>
</html>
