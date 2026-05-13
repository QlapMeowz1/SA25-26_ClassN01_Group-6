@extends('layout')

@section('title', 'Tournaments - BadNet')

@section('content')
<div class="page-shell tournament-circuit-shell tournament-index-shell">
    <section class="tournament-hero">
        <div class="tournament-hero-copy">
            <p class="home-eyebrow">Tournament Circuit</p>
            <h1>Compete & Conquer</h1>
            <p class="page-subtitle">Track the most competitive badminton events, register in seconds, and rise through the circuit with every match.</p>

            <div class="tournament-hero-actions">
                <a href="#tournament-browser" class="btn btn-primary btn-large">Browse Events</a>
                <a href="{{ route('tournaments.create') }}" class="btn btn-secondary btn-large">Create Tournament</a>
            </div>

            <div class="tournament-hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-value">{{ $allTournaments->count() }}</span>
                    <span class="hero-stat-label">Total events</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">{{ $upcomingTournaments->count() }}</span>
                    <span class="hero-stat-label">Upcoming</span>
                </div>
                <div class="hero-stat">
                    <span class="hero-stat-value">{{ $myTournaments->count() }}</span>
                    <span class="hero-stat-label">My registrations</span>
                </div>
            </div>
        </div>

        <div class="tournament-hero-visual">
            <div class="hero-racket-card">
                <span class="hero-racket-badge">Live Circuit</span>
                <h3>Climb the ladder. Win the badge.</h3>
                <p>Join upcoming battles, follow the bracket, and stay ready for your next showdown.</p>
                <div class="hero-racket-bars">
                    <span></span><span></span><span></span><span></span>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($featuredTournament))
        <section class="featured-tournament-section">
            <div class="featured-tournament-card tournament-featured-card" style="background: linear-gradient(135deg, {{ $featuredTournament->banner_color ?? '#6366f1' }}, rgba(17, 24, 39, 0.92));">
                <div class="featured-tournament-grid">
                    <div class="featured-tournament-copy">
                        <div class="featured-topline">
                            <span class="featured-badge">⭐ Featured Tournament</span>
                            <span class="tournament-status-badge status-{{ $featuredTournament->status_class }}">{{ $featuredTournament->status_label }}</span>
                        </div>

                        <h2>{{ $featuredTournament->name }}</h2>
                        <p class="featured-description">{{ $featuredTournament->description }}</p>

                        <div class="featured-meta-row">
                            <div class="featured-meta-block">
                                <span class="meta-icon">💰</span>
                                <div class="meta-text">
                                    <span class="meta-label">Prize Pool</span>
                                    <span class="meta-value">{{ $featuredTournament->prize_details }}</span>
                                </div>
                            </div>
                            <div class="featured-meta-block">
                                <span class="meta-icon">👥</span>
                                <div class="meta-text">
                                    <span class="meta-label">Slots</span>
                                    <span class="meta-value">{{ $featuredTournament->slots_filled }} / {{ $featuredTournament->slots_total }}</span>
                                </div>
                            </div>
                            <div class="featured-meta-block">
                                <span class="meta-icon">📅</span>
                                <div class="meta-text">
                                    <span class="meta-label">Starts</span>
                                    <span class="meta-value">{{ $featuredTournament->starts_text }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="featured-slots-bar">
                            <div class="slots-label">Registration Progress</div>
                            <div class="progress-bar-large">
                                <div class="progress-fill" style="width: {{ $featuredTournament->slots_percentage }}%"></div>
                            </div>
                            <div class="slots-text">{{ $featuredTournament->slots_filled }} of {{ $featuredTournament->slots_total }} filled</div>
                        </div>

                        <div class="featured-countdown" data-countdown data-status="{{ $featuredTournament->display_status }}" data-start-date="{{ optional($featuredTournament->start_date)->toIso8601String() }}">
                            {{ $featuredTournament->countdown_text }}
                        </div>

                        <div class="featured-actions">
                            <a href="{{ route('tournaments.show', $featuredTournament->id) }}" class="btn btn-primary btn-large">{{ $featuredTournament->action_label }}</a>
                            <span class="deadline-text">Registration closes before the opening round.</span>
                        </div>
                    </div>

                    <div class="featured-tournament-side">
                        <div class="featured-side-card">
                            <span class="featured-side-label">Organizer</span>
                            <strong>{{ $featuredTournament->organizer?->name ?? 'Community' }}</strong>
                        </div>
                        <div class="featured-side-card">
                            <span class="featured-side-label">Format</span>
                            <strong>{{ ucfirst($featuredTournament->tournament_type ?? 'Standard') }}</strong>
                        </div>
                        <div class="featured-side-card">
                            <span class="featured-side-label">Status</span>
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
                <p class="home-eyebrow">My Registrations</p>
                <h2>Your Tournaments</h2>
            </div>
        </div>

        @if($myTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                @include('partials.empty-illustration', ['title' => "You haven't registered for any tournament yet", 'message' => 'Browse the open events below and jump in.'])
                <a href="#tournament-browser" class="btn btn-primary">Browse Tournaments</a>
            </div>
        @else
            <div class="tournament-card-grid tournament-card-grid-tight">
                @foreach($myTournaments as $tournament)
                    <article class="tournament-card tournament-card-modern" data-tournament-card data-status="{{ $tournament->display_status }}" data-name="{{ strtolower($tournament->name) }}">
                        <div class="tournament-banner tournament-banner-modern" style="background: linear-gradient(135deg, {{ $tournament->banner_color ?? '#6366f1' }}, rgba(17, 24, 39, 0.9));">
                            <span class="tournament-status-badge status-{{ $tournament->status_class }}">{{ $tournament->status_label }}</span>
                            <span class="countdown-pill" data-countdown data-status="{{ $tournament->display_status }}" data-start-date="{{ optional($tournament->start_date)->toIso8601String() }}">{{ $tournament->countdown_text }}</span>
                        </div>

                        <div class="tournament-card-content tournament-card-content-modern">
                            <div class="tournament-card-header">
                                <h3>{{ $tournament->name }}</h3>
                                <span class="tournament-type-badge" data-type="{{ $tournament->tournament_type ?? 'standard' }}">{{ ucfirst($tournament->tournament_type ?? 'Tournament') }}</span>
                            </div>

                            <p class="tournament-organizer">Organized by: {{ $tournament->organizer?->name ?? 'Community' }}</p>

                            <div class="tournament-meta-list">
                                <div class="tournament-meta-item">💰 {{ $tournament->prize_details }}</div>
                                <div class="tournament-meta-item">📅 {{ $tournament->starts_text }}</div>
                            </div>

                            <div class="tournament-slots">
                                <div class="slots-row">
                                    <span class="slots-label">Slots</span>
                                    <span class="slots-value">{{ $tournament->slots_filled }}/{{ $tournament->slots_total }}</span>
                                </div>
                                <div class="progress-bar-small">
                                    <div class="progress-fill" style="width: {{ $tournament->slots_percentage }}%"></div>
                                </div>
                            </div>

                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn {{ $tournament->action_variant === 'primary' ? 'btn-primary' : 'btn-secondary' }} btn-block">{{ $tournament->action_label }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="tournament-section tournament-browser-section" id="tournament-browser" data-tournament-browser>
        <div class="feed-heading tournament-browser-heading">
            <div>
                <p class="home-eyebrow">Open Events / All Tournaments</p>
                <h2>Find Your Next Battle</h2>
            </div>

            <div class="tournament-search-shell">
                <span class="tournament-search-icon">🔎</span>
                <input type="search" class="tournament-search-input" placeholder="Search tournaments..." data-tournament-search>
            </div>
        </div>

        <div class="tournament-filter-bar" role="tablist" aria-label="Tournament filters">
            <button type="button" class="tournament-filter-btn is-active" data-filter="all">All <span>{{ $allTournaments->count() }}</span></button>
            <button type="button" class="tournament-filter-btn" data-filter="upcoming">Upcoming <span>{{ $upcomingTournaments->count() }}</span></button>
            <button type="button" class="tournament-filter-btn" data-filter="ongoing">Ongoing <span>{{ $ongoingTournaments->count() }}</span></button>
            <button type="button" class="tournament-filter-btn" data-filter="completed">Completed <span>{{ $completedTournaments->count() }}</span></button>
        </div>

        <div class="tournament-browser-meta">
            <span data-tournament-results>{{ $allTournaments->count() }} tournaments</span>
            <span class="browser-tip">Tip: click a tab or search by name to narrow the list.</span>
        </div>

        @if($allTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                @include('partials.empty-illustration', ['title' => 'No tournaments available yet', 'message' => 'Create the first competition and get the circuit started.'])
                <a href="{{ route('tournaments.create') }}" class="btn btn-primary">Create Tournament</a>
            </div>
        @else
            <div class="tournament-card-grid tournament-browser-grid" data-tournament-grid>
                @foreach($allTournaments as $tournament)
                    <article class="tournament-card tournament-card-modern" data-tournament-card data-status="{{ $tournament->display_status }}" data-name="{{ strtolower($tournament->name) }}">
                        <div class="tournament-banner tournament-banner-modern" style="background: linear-gradient(135deg, {{ $tournament->banner_color ?? '#6366f1' }}, rgba(17, 24, 39, 0.9));">
                            <span class="tournament-status-badge status-{{ $tournament->status_class }}">{{ $tournament->status_label }}</span>
                            <span class="countdown-pill" data-countdown data-status="{{ $tournament->display_status }}" data-start-date="{{ optional($tournament->start_date)->toIso8601String() }}">{{ $tournament->countdown_text }}</span>
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
                                <div class="slots-row">
                                    <span class="slots-label">Slots</span>
                                    <span class="slots-value">{{ $tournament->slots_filled }}/{{ $tournament->slots_total }}</span>
                                </div>
                                <div class="progress-bar-small">
                                    <div class="progress-fill" style="width: {{ $tournament->slots_percentage }}%"></div>
                                </div>
                            </div>

                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn {{ $tournament->action_variant === 'primary' ? 'btn-primary' : 'btn-secondary' }} btn-block">{{ $tournament->action_label }}</a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="tournament-empty-state is-hidden" data-tournament-empty-state>
                <div class="comment-empty-icon">🏸</div>
                <h3>No tournaments match your filter</h3>
                <p>Try a different tab or search term.</p>
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
        if (status === 'completed') return 'Completed';
        if (status === 'ongoing') return 'Ongoing now';
        if (diffMs <= 0) return 'Starting soon';

        const totalMinutes = Math.floor(diffMs / 60000);
        const days = Math.floor(totalMinutes / 1440);
        const hours = Math.floor((totalMinutes % 1440) / 60);
        const minutes = totalMinutes % 60;
        const parts = [];
        if (days > 0) parts.push(days + 'd');
        if (hours > 0) parts.push(hours + 'h');
        if (minutes > 0) parts.push(minutes + 'm');
        return 'Starts in ' + (parts.length ? parts.slice(0, 3).join(' ') : 'less than 1m');
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
            resultsLabel.textContent = visible + ' tournament' + (visible === 1 ? '' : 's');
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