@extends('layout')

@section('title', 'Tournaments - BadNet')

@php
    $tournamentHref = fn ($tournament) => is_numeric($tournament->id) ? route('tournaments.show', $tournament->id) : route('tournaments.create');
@endphp

@section('content')
<div class="page-shell tournament-circuit-shell tournament-index-shell">
    <section class="tournament-hero">
        <div class="tournament-hero-copy">
            <p class="home-eyebrow">{{ __('ui.tournament.live_circuit') }}</p>
            <h1>{{ __('ui.tournament.find_next_battle') }}</h1>
            <p class="page-subtitle">{{ __('ui.tournament.live_circuit_body') }}</p>

            <div class="tournament-hero-actions">
                <a href="#tournament-browser" class="btn btn-primary btn-large">{{ __('ui.tournament.browse') }}</a>
                <a href="{{ route('tournaments.create') }}" class="btn btn-secondary btn-large">{{ __('ui.tournament.create') }}</a>
            </div>

            <div class="tournament-hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-value">{{ $allTournaments->count() }}</span>
                    <span class="hero-stat-label">{{ __('ui.tournament.total_events') }}</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">{{ $upcomingTournaments->count() }}</span>
                    <span class="hero-stat-label">{{ __('ui.tournament.upcoming') }}</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">{{ $myTournaments->count() }}</span>
                    <span class="hero-stat-label">{{ __('ui.tournament.my_registrations') }}</span>
                </div>
            </div>
        </div>

    </section>

    @if(!empty($featuredTournament))
        <section class="featured-tournament-section">
            <div class="featured-tournament-card tournament-featured-card" style="--featured-tournament-start: {{ $featuredTournament->banner_color ?? '#6366f1' }};">
                <div class="featured-tournament-grid">
                    <div class="featured-tournament-copy">
                        <div class="featured-topline">
                            <span class="featured-badge">⭐ {{ __('ui.tournament.featured') }}</span>
                            <span class="tournament-status-badge status-{{ $featuredTournament->status_class }}">{{ $featuredTournament->status_label }}</span>
                        </div>

                        <h2>{{ $featuredTournament->name }}</h2>
                        <p class="featured-description">{{ $featuredTournament->description }}</p>

                        <div class="featured-meta-row">
                            <div class="featured-meta-block">
                                <span class="meta-icon">💰</span>
                                <div class="meta-text">
                                    <span class="meta-label">{{ __('ui.tournament.prize_pool') }}</span>
                                    <span class="meta-value">{{ $featuredTournament->prize_details }}</span>
                                </div>
                            </div>
                            <div class="featured-meta-block">
                                <span class="meta-icon">👥</span>
                                <div class="meta-text">
                                    <span class="meta-label">{{ __('ui.tournament.slots') }}</span>
                                    <span class="meta-value">{{ $featuredTournament->slots_filled }} / {{ $featuredTournament->slots_total }}</span>
                                </div>
                            </div>
                            <div class="featured-meta-block">
                                <span class="meta-icon">📅</span>
                                <div class="meta-text">
                                    <span class="meta-label">{{ __('ui.tournament.starts') }}</span>
                                    <span class="meta-value">{{ $featuredTournament->starts_text }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="featured-slots-bar">
                            <div class="slots-label">{{ __('ui.tournament.registration_progress') }}</div>
                            <div class="progress-bar-large">
                                <div class="progress-fill" style="width: {{ $featuredTournament->slots_percentage }}%"></div>
                            </div>
                            <div class="slots-text">{{ $featuredTournament->slots_filled }} / {{ $featuredTournament->slots_total }} {{ __('ui.tournament.filled') }}</div>
                        </div>

                        <div class="featured-countdown" data-countdown data-status="{{ $featuredTournament->display_status }}" data-start-date="{{ optional($featuredTournament->start_date)->toIso8601String() }}">
                            {{ $featuredTournament->countdown_text }}
                        </div>

                        <div class="featured-actions">
                            <a href="{{ $tournamentHref($featuredTournament) }}" class="btn btn-primary btn-large">{{ $featuredTournament->action_label }}</a>
                        </div>
                    </div>

                    <div class="featured-tournament-side">
                        <div class="featured-side-card">
                            <span class="featured-side-label">{{ __('ui.tournament.organizer') }}</span>
                            <strong>{{ $featuredTournament->organizer?->name ?? __('ui.tournament.community') }}</strong>
                        </div>
                        <div class="featured-side-card">
                            <span class="featured-side-label">{{ __('ui.tournament.format') ?? 'Format' }}</span>
                            <strong>{{ ucfirst($featuredTournament->tournament_type ?? 'Standard') }}</strong>
                        </div>
                        <div class="featured-side-card">
                            <span class="featured-side-label">{{ __('ui.tournament.status') ?? 'Status' }}</span>
                            <strong>{{ $featuredTournament->status_label }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="tournament-section tournament-summary-section" id="my-tournaments">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">{{ __('ui.tournament.my_registrations_title') }}</p>
                <h2>{{ __('ui.tournament.your_tournaments') }}</h2>
            </div>
        </div>

        @if($myTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                @include('partials.empty-illustration', ['title' => __('ui.tournament.no_registered') ?? "You haven't registered for any tournament yet", 'message' => __('ui.tournament.open_events_message') ?? 'Browse the open events below and jump in.'])
                <a href="#tournament-browser" class="btn btn-primary">{{ __('ui.tournament.browse') }}</a>
            </div>
        @else
            <div class="tournament-card-grid tournament-card-grid-tight">
                @foreach($myTournaments as $tournament)
                    <article class="tournament-card tournament-card-modern" data-tournament-card data-status="{{ $tournament->display_status }}" data-name="{{ strtolower($tournament->name) }}">
                        <div class="tournament-banner tournament-banner-modern" style="--tournament-banner-start: {{ $tournament->banner_color ?? '#6366f1' }};">
                            <span class="tournament-status-badge status-{{ $tournament->status_class }}">{{ $tournament->status_label }}</span>
                        </div>

                        <div class="tournament-card-content tournament-card-content-modern">
                            <div class="tournament-card-header">
                                <h3>{{ $tournament->name }}</h3>
                                <span class="tournament-type-badge" data-type="{{ $tournament->tournament_type ?? 'standard' }}">{{ ucfirst($tournament->tournament_type ?? 'Tournament') }}</span>
                            </div>

                            <p class="tournament-organizer">{{ __('ui.tournament.organizer') }} {{ $tournament->organizer?->name ?? __('ui.tournament.community') }}</p>

                            <div class="tournament-meta-list">
                                <div class="tournament-meta-item">💰 {{ $tournament->prize_details }}</div>
                                <div class="tournament-meta-item">📅 {{ $tournament->starts_text }}</div>
                            </div>

                            <div class="tournament-slots">
                                <div class="tournament-slots-summary">
                                    <span class="slots-label">{{ __('ui.tournament.slots') }}:</span>
                                    <strong class="slots-value">{{ $tournament->slots_filled }}/{{ $tournament->slots_total }}</strong>
                                </div>
                                <div class="progress-bar-small">
                                    <div class="progress-fill" style="width: {{ $tournament->slots_percentage }}%"></div>
                                </div>
                            </div>

                            <a href="{{ $tournamentHref($tournament) }}" class="btn {{ $tournament->action_variant === 'primary' ? 'btn-primary' : 'btn-secondary' }} tournament-card-action">{{ $tournament->action_label }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="tournament-section tournament-browser-section" id="tournament-browser" data-tournament-browser>
        <div class="feed-heading tournament-browser-heading">
            <div>
                <p class="home-eyebrow">{{ __('ui.tournament.all_filter') }} / {{ __('ui.tournament.all_filter') }}</p>
                <h2>{{ __('ui.tournament.find_next_battle') }}</h2>
            </div>

            <div class="tournament-search-shell">
                <span class="tournament-search-icon">🔎</span>
                <input type="search" class="tournament-search-input" placeholder="{{ __('ui.tournament.search_placeholder') }}" data-tournament-search>
            </div>
        </div>

        <div class="tournament-filter-bar" role="tablist" aria-label="Tournament filters">
            <button type="button" class="tournament-filter-btn is-active" data-filter="all">{{ __('ui.tournament.all_filter') }} <span>{{ $allTournaments->count() }}</span></button>
            <button type="button" class="tournament-filter-btn" data-filter="upcoming">{{ __('ui.tournament.upcoming_filter') }} <span>{{ $upcomingTournaments->count() }}</span></button>
            <button type="button" class="tournament-filter-btn" data-filter="ongoing">{{ __('ui.tournament.ongoing_filter') }} <span>{{ $ongoingTournaments->count() }}</span></button>
            <button type="button" class="tournament-filter-btn" data-filter="completed">{{ __('ui.tournament.completed_filter') }} <span>{{ $completedTournaments->count() }}</span></button>
        </div>

        <div class="tournament-browser-meta">
            <span data-tournament-results>{{ $allTournaments->count() }} {{ __('ui.tournament.results_plural') }}</span>
            <span class="browser-tip">{{ __('ui.tournament.browser_tip') }}</span>
        </div>

        @if($allTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                @include('partials.empty-illustration', ['title' => __('ui.tournament.no_available') ?? 'No tournaments available yet', 'message' => __('ui.tournament.create_first')])
                <a href="{{ route('tournaments.create') }}" class="btn btn-primary">{{ __('ui.tournament.create') }}</a>
            </div>
        @else
            <div class="tournament-card-grid tournament-browser-grid" data-tournament-grid>
                @foreach($allTournaments as $tournament)
                    <article class="tournament-card tournament-card-modern" data-tournament-card data-status="{{ $tournament->display_status }}" data-name="{{ strtolower($tournament->name) }}">
                        <div class="tournament-banner tournament-banner-modern" style="--tournament-banner-start: {{ $tournament->banner_color ?? '#6366f1' }};">
                            <span class="tournament-status-badge status-{{ $tournament->status_class }}">{{ $tournament->status_label }}</span>
                        </div>

                        <div class="tournament-card-content tournament-card-content-modern">
                            <div class="tournament-card-header">
                                <h3>{{ $tournament->name }}</h3>
                                <span class="tournament-type-badge" data-type="{{ $tournament->tournament_type ?? 'standard' }}">{{ ucfirst($tournament->tournament_type ?? 'Tournament') }}</span>
                            </div>

                            <p class="tournament-description">{{ \Illuminate\Support\Str::limit($tournament->description, 72) }}</p>
                            <p class="tournament-organizer">Organized by: {{ $tournament->organizer?->name ?? 'Community' }}</p>

                            <div class="tournament-meta-list">
                                <div class="tournament-meta-item">💰 {{ $tournament->prize_details }}</div>
                                <div class="tournament-meta-item">📅 {{ $tournament->starts_text }}</div>
                            </div>

                            <div class="tournament-slots">
                                <div class="tournament-slots-summary">
                                    <span class="slots-label">Slots:</span>
                                    <strong class="slots-value">{{ $tournament->slots_filled }}/{{ $tournament->slots_total }}</strong>
                                </div>
                                <div class="progress-bar-small">
                                    <div class="progress-fill" style="width: {{ $tournament->slots_percentage }}%"></div>
                                </div>
                            </div>

                            <a href="{{ $tournamentHref($tournament) }}" class="btn {{ $tournament->action_variant === 'primary' ? 'btn-primary' : 'btn-secondary' }} tournament-card-action">{{ $tournament->action_label }}</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="tournament-empty-state is-hidden" data-tournament-empty-state>
                <div class="comment-empty-icon">🏸</div>
                <h3>{{ __('ui.tournament.no_match') }}</h3>
                <p>{{ __('ui.tournament.try_other') ?? 'Try a different tab or search term.' }}</p>
            </div>
        @endif
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const browser = document.querySelector('[data-tournament-browser]');
    if (!browser) return;

    const filterButtons = Array.from(browser.querySelectorAll('[data-filter]'));
    const searchInput = browser.querySelector('[data-tournament-search]');
    const cards = Array.from(browser.querySelectorAll('[data-tournament-card]'));
    const emptyState = browser.querySelector('[data-tournament-empty-state]');
    const resultsLabel = browser.querySelector('[data-tournament-results]');
    let activeFilter = 'all';

    function formatCountdown(startDate, status) {
        if (!startDate || isNaN(startDate.getTime())) return '';

        const now = new Date();
        const diffMs = startDate.getTime() - now.getTime();
        if (status === 'completed') return @json(__('ui.tournament.completed'));
        if (status === 'ongoing') return @json(__('ui.tournament.ongoing_now'));
        if (diffMs <= 0) return @json(__('ui.tournament.starting_soon'));

        const totalMinutes = Math.floor(diffMs / 60000);
        const days = Math.floor(totalMinutes / 1440);
        const hours = Math.floor((totalMinutes % 1440) / 60);
        const minutes = totalMinutes % 60;
        const parts = [];
        if (days > 0) parts.push(days + 'd');
        if (hours > 0) parts.push(hours + 'h');
        if (minutes > 0) parts.push(minutes + 'm');
        return @json(__('ui.tournament.starts_in')) + ' ' + (parts.length ? parts.slice(0, 3).join(' ') : @json(__('ui.tournament.less_than_1m')));
    }

    function updateCards() {
        const query = (searchInput && searchInput.value ? searchInput.value : '').trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const name = (card.dataset.name || '').toLowerCase();
            const status = (card.dataset.status || '').toLowerCase();
            const matchesFilter = activeFilter === 'all' || status === activeFilter;
            const matchesSearch = !query || name.includes(query);
            const show = matchesFilter && matchesSearch;

            card.classList.toggle('is-hidden', !show);
            if (show) visible += 1;
        });

        if (resultsLabel) {
            resultsLabel.textContent = visible + ' ' + (visible === 1 ? @json(__('ui.tournament.results_singular')) : @json(__('ui.tournament.results_plural')));
        }

        if (emptyState) {
            emptyState.classList.toggle('is-hidden', visible > 0);
        }
    }

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeFilter = button.dataset.filter || 'all';
            filterButtons.forEach(function (other) {
                other.classList.toggle('is-active', other === button);
            });
            updateCards();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', updateCards);
    }

    function refreshCountdowns() {
        document.querySelectorAll('[data-countdown]').forEach(function (el) {
            const status = (el.dataset.status || '').toLowerCase();
            const dateValue = el.dataset.startDate;
            const startDate = dateValue ? new Date(dateValue) : null;
            if (!startDate) return;
            el.textContent = formatCountdown(startDate, status);
        });
    }

    updateCards();
    refreshCountdowns();
    setInterval(refreshCountdowns, 60000);
});
</script>
@endpush
@endsection
