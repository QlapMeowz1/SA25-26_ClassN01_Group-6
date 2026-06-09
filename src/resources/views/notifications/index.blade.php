@extends('layout')

@section('title', 'Notifications - BadNet')

@section('content')
@php
    $categoryLabels = [
        'all' => 'All',
        'interactions' => 'Interactions',
        'matches' => 'Matches',
        'betting' => 'Betting / Wallet',
        'system' => 'System',
    ];
    $categoryFor = fn ($notification) => app(\App\Http\Controllers\NotificationsController::class)->categoryFor($notification);
@endphp

<div class="page-shell notifications-page">
    <section class="notifications-hero">
        <div>
            <p class="home-eyebrow">Notification Center</p>
            <h1>Notifications</h1>
            <p class="page-subtitle">Review activity, betting updates, match invitations, and system alerts.</p>
        </div>
        <div class="notifications-hero-actions">
            <form method="POST" action="{{ route('notifications.markAll') }}">
                @csrf
                <button class="btn btn-primary" type="submit">Mark All Read</button>
            </form>
            <form method="POST" action="{{ route('notifications.clearRead') }}" onsubmit="return confirm('Delete all read notifications?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-secondary" type="submit">Clear Read</button>
            </form>
        </div>
    </section>

    <div class="notifications-layout">
        <main class="notifications-main">
            <section class="notifications-toolbar">
                <form method="GET" action="{{ route('notifications.index') }}">
                    <input type="search" name="q" value="{{ $search }}" placeholder="Search notifications...">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <button class="btn btn-secondary btn-small">Search</button>
                </form>
                <nav>
                    @foreach($categoryLabels as $key => $label)
                        <a href="{{ route('notifications.index', array_filter(['category' => $key, 'q' => $search])) }}" class="{{ $category === $key ? 'is-active' : '' }}">{{ $label }}</a>
                    @endforeach
                </nav>
            </section>

            <section class="notifications-list-panel">
                @forelse($notifications as $notification)
                    @php $itemCategory = $categoryFor($notification); @endphp
                    <article class="notification-center-item {{ $notification->is_read ? 'is-read' : 'is-unread' }} {{ $notification->is_pinned ? 'is-pinned' : '' }}">
                        <a href="{{ $notification->target_url ?: route('notifications.index') }}" class="notification-center-copy">
                            <span class="notification-center-dot"></span>
                            <div>
                                <strong>{{ $notification->title }}</strong>
                                <p>{{ $notification->message }}</p>
                                <small>{{ $notification->created_at->diffForHumans() }} · {{ $categoryLabels[$itemCategory] ?? 'System' }}</small>
                            </div>
                        </a>
                        <div class="notification-center-actions">
                            <form method="POST" action="{{ $notification->is_read ? route('notifications.markUnread', $notification) : route('notifications.markRead', $notification) }}">
                                @csrf
                                <button class="btn btn-secondary btn-small">{{ $notification->is_read ? 'Mark Unread' : 'Mark Read' }}</button>
                            </form>
                            <form method="POST" action="{{ route('notifications.togglePin', $notification) }}">
                                @csrf
                                <button class="btn btn-secondary btn-small">{{ $notification->is_pinned ? 'Unpin' : 'Pin' }}</button>
                            </form>
                            <form method="POST" action="{{ route('notifications.destroy', $notification) }}" onsubmit="return confirm('Delete this notification?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-small">Delete</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="empty-inline">No notifications match this filter.</div>
                @endforelse

                {{ $notifications->links() }}
            </section>
        </main>

        <aside class="notifications-preferences">
            <section class="profile-edit-panel">
                <p class="home-eyebrow">Preferences</p>
                <h2>Delivery Controls</h2>
                <form method="POST" action="{{ route('notifications.preferences') }}" class="notification-preference-form">
                    @csrf
                    @foreach([
                        'interactions_web' => 'Interactions',
                        'matches_web' => 'Matches and challenges',
                        'betting_web' => 'Betting and wallet',
                        'system_web' => 'System updates',
                        'critical_email' => 'Critical email alerts',
                        'match_reminders' => 'Match reminders',
                        'betting_updates' => 'Betting result updates',
                    ] as $field => $label)
                        <label>
                            <input type="checkbox" name="{{ $field }}" value="1" @checked($notificationPreference->{$field})>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                    <button class="btn btn-primary btn-block" type="submit">Save Preferences</button>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection
