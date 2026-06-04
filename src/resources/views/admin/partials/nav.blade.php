<nav class="admin-section-nav" aria-label="Admin sections">
    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">Overview</a>
    <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'is-active' : '' }}">Users</a>
    <a href="{{ route('admin.content') }}" class="{{ request()->routeIs('admin.content') ? 'is-active' : '' }}">Content</a>
    <a href="{{ route('admin.matches') }}" class="{{ request()->routeIs('admin.matches') ? 'is-active' : '' }}">Matches</a>
    <a href="{{ route('admin.tournaments') }}" class="{{ request()->routeIs('admin.tournaments') ? 'is-active' : '' }}">Tournaments</a>
    <a href="{{ route('admin.bets') }}" class="{{ request()->routeIs('admin.bets') ? 'is-active' : '' }}">Bets</a>
</nav>
