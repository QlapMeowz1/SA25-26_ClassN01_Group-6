@php
    $adminLinks = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'grid', 'label' => 'Dashboard'],
        ['route' => 'admin.players', 'match' => 'admin.players', 'icon' => 'users', 'label' => 'Players'],
        ['route' => 'admin.tournaments', 'match' => 'admin.tournaments', 'icon' => 'trophy', 'label' => 'Tournaments'],
        ['route' => 'admin.schedule', 'match' => 'admin.schedule', 'icon' => 'calendar', 'label' => 'Schedule'],
        ['route' => 'admin.court-bookings', 'match' => 'admin.court-bookings', 'icon' => 'book', 'label' => 'Court Bookings'],
        ['route' => 'admin.betting', 'match' => 'admin.betting', 'icon' => 'coins', 'label' => 'Betting'],
        ['route' => 'admin.content', 'match' => 'admin.content', 'icon' => 'content', 'label' => 'Moderation'],
        ['route' => 'admin.statistics', 'match' => 'admin.statistics', 'icon' => 'chart', 'label' => 'Statistics'],
    ];

    $adminIcon = function ($name) {
        return match ($name) {
            'users' => '<path d="M16 18v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1"/><circle cx="10" cy="8" r="3"/><path d="M20 18v-1a3 3 0 0 0-2-2.8"/><path d="M15 5.2a3 3 0 0 1 0 5.6"/>',
            'trophy' => '<path d="M8 4h8v4a4 4 0 0 1-8 0V4z"/><path d="M6 6H4a2 2 0 0 0 2 2"/><path d="M18 6h2a2 2 0 0 1-2 2"/><path d="M12 12v4"/><path d="M8 20h8"/>',
            'calendar' => '<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M8 3v4M16 3v4M3 11h18"/>',
            'book' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v17H6.5A2.5 2.5 0 0 1 4 17.5z"/><path d="M4 17.5A2.5 2.5 0 0 1 6.5 15H20"/>',
            'chart' => '<path d="M4 19V5"/><path d="M8 19v-6"/><path d="M13 19V9"/><path d="M18 19v-3"/><path d="M3 19h19"/>',
            'coins' => '<circle cx="8" cy="8" r="4"/><path d="M12 8h4a4 4 0 0 1 0 8H8"/><path d="M8 12v8"/><path d="M12 16h4"/>',
            'content' => '<path d="M4 5h16"/><path d="M4 12h10"/><path d="M4 19h7"/><path d="M17 14l3 3-3 3"/><path d="M14 17h6"/>',
            default => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
        };
    };
@endphp

<nav class="admin-section-nav" aria-label="Admin sections">
    <div class="admin-sidebar-brand">
        <span class="admin-brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                <path d="M20.2 12.2A6 6 0 0 0 12 3L5 10v8h8l7.2-5.8Z"/>
                <path d="M12 3v7h7"/>
                <path d="M5 18 3 21"/>
            </svg>
        </span>
        <div>
            <strong>SMASH</strong>
            <span>Admin Panel</span>
        </div>
    </div>

    <div class="admin-sidebar-menu-label">Main Menu</div>

    <div class="admin-sidebar-links">
        @foreach($adminLinks as $link)
            <a href="{{ route($link['route']) }}" class="{{ request()->routeIs($link['match']) ? 'is-active' : '' }}">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    {!! $adminIcon($link['icon']) !!}
                </svg>
                <span>{{ $link['label'] }}</span>
                <span class="admin-nav-chevron" aria-hidden="true">›</span>
            </a>
        @endforeach
    </div>

    <a href="{{ route('dashboard') }}" class="admin-player-zone-link">→ Xem Player Zone</a>

    <div class="admin-sidebar-user">
        <span class="admin-sidebar-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</span>
        <div>
            <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
            <span>Super Admin</span>
        </div>
        <span class="admin-sidebar-bell" aria-hidden="true">⌕</span>
    </div>
</nav>
