@extends('layout')

@section('title', 'Matches - BadNet')

@php
    $openCount = $openMatches->count();
    $upcomingCount = $upcomingMatches->count();
    $completedCount = $completedMatches->count();

    $rankClass = function ($rank) {
        return strtolower((string) $rank ?: 'beginner');
    };
@endphp

@section('content')
<div class="page-shell match-arena-shell match-page">
    <section class="match-hero-panel">
        <div class="match-hero-copy">
            <p class="home-eyebrow">{{ __('ui.match.open') }}</p>
            <h1>{{ __('ui.match.title') }}</h1>
            <p class="page-subtitle">{{ __('ui.match.subtitle') }}</p>
        </div>

        <div class="matches-header-actions">
            <a href="{{ route('bets.index') }}" class="btn btn-secondary">Bets</a>
            <form action="{{ route('matches.quick') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary">{{ __('ui.match.quick') }}</button>
            </form>
            <a href="{{ route('matches.create') }}" class="btn btn-secondary">{{ __('ui.match.create') }}</a>
        </div>
    </section>

    <div class="match-summary-grid">
        <a href="#open-matches" class="match-summary-card">
            <span>{{ __('ui.match.open') }}</span>
            <strong>{{ $openCount }}</strong>
            <small>{{ __('ui.match.available') }}</small>
        </a>
        <a href="#upcoming-matches" class="match-summary-card">
            <span>{{ __('ui.match.my_upcoming') }}</span>
            <strong>{{ $upcomingCount }}</strong>
            <small>{{ __('ui.match.scheduled') }}</small>
        </a>
        <a href="#completed-matches" class="match-summary-card">
            <span>{{ __('ui.match.completed') }}</span>
            <strong>{{ $completedCount }}</strong>
            <small>{{ __('ui.match.history') }}</small>
        </a>
    </div>

    <form method="GET" action="{{ route('matches.index') }}" class="match-filter-panel">
        <div class="match-filter-field">
            <label for="location">{{ __('ui.challenge.location') }}</label>
            <input type="text" id="location" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="{{ __('ui.match.court_tbd') }}">
        </div>

        <div class="match-filter-field">
            <label for="date">{{ __('ui.match.any_time') }}</label>
            <select id="date" name="date">
                <option value="">{{ __('ui.match.any_time') }}</option>
                <option value="today" @selected(($filters['date'] ?? '') === 'today')>{{ __('ui.match.today') }}</option>
                <option value="tomorrow" @selected(($filters['date'] ?? '') === 'tomorrow')>{{ __('ui.match.tomorrow') }}</option>
                <option value="weekend" @selected(($filters['date'] ?? '') === 'weekend')>{{ __('ui.match.this_weekend') }}</option>
            </select>
        </div>

        <div class="match-filter-levels" role="radiogroup" aria-label="Skill level">
            @foreach(['Beginner' => __('ui.match.beginner'), 'Intermediate' => __('ui.match.intermediate'), 'Advanced' => __('ui.match.advanced'), 'Professional' => __('ui.match.professional')] as $levelValue => $levelLabel)
                <label class="match-chip">
                    <input type="radio" name="skill_level" value="{{ $levelValue }}" @checked(($filters['skill_level'] ?? '') === $levelValue)>
                    <span>{{ $levelLabel }}</span>
                </label>
            @endforeach
        </div>

        <div class="match-filter-actions">
            <button type="submit" class="btn btn-primary btn-small">{{ __('ui.match.filter') }}</button>
            <a href="{{ route('matches.index') }}" class="btn btn-secondary btn-small">{{ __('ui.match.reset') }}</a>
        </div>
    </form>

    <div class="match-layout">
        <main class="match-main-column">
            <section class="match-section" id="open-matches">
                <div class="match-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.match.open') }}</p>
                        <h2>{{ __('ui.match.available') }}</h2>
                    </div>
                </div>

                @if($openMatches->isEmpty())
                    <div class="empty-state-block match-empty-cta">
                        <h3>{{ __('ui.match.first_match') }}</h3>
                        <p>{{ __('ui.match.open_none') }}</p>
                        <a href="{{ route('matches.create') }}" class="btn btn-primary">{{ __('ui.match.create_match') }}</a>
                    </div>
                @else
                    <div class="match-ticket-grid">
                        @foreach($openMatches as $match)
                            <article class="match-ticket match-ticket-open">
                                <div class="ticket-header">
                                    <div class="ticket-badge" data-rank="{{ $match->arena_badge_class ?? 'beginner' }}">
                                        {{ $match->arena_skill ?? 'Beginner' }}
                                    </div>
                                    <span class="ticket-status">{{ __('ui.match.open') }}</span>
                                </div>

                                <div class="ticket-players">
                                    <div class="player-stack">
                                        <div class="match-avatar">{{ strtoupper(substr($match->player1->name, 0, 1)) }}</div>
                                        <span class="player-name-ticket">{{ $match->player1->name }}</span>
                                    </div>
                                    <div class="vs-center">VS</div>
                                    <div class="player-stack">
                                        <div class="match-avatar match-avatar-waiting">?</div>
                                        <span class="player-name-ticket">{{ __('ui.match.find_opponent') }}</span>
                                    </div>
                                </div>

                                <div class="ticket-meta-row">
                                    <span class="ticket-meta-item">{{ $match->arena_location ?? $match->location ?? __('ui.match.court_tbd') }}</span>
                                    <span class="ticket-meta-item">{{ $match->arena_time ?? $match->match_date->format('M d') }}</span>
                                </div>

                                @if(!($match->is_sample ?? false) && $match->joinRequests && $match->joinRequests->count() > 0)
                                    <div class="ticket-requests">
                                        <p class="ticket-requests-header">{{ __('ui.match.join_requests', ['count' => $match->joinRequests->count()]) }}</p>
                                    </div>
                                @endif

                                <div class="ticket-footer">
                                    @if(!($match->is_sample ?? false))
                                        <a href="{{ route('matches.show', $match->id) }}" class="btn btn-primary btn-small">{{ __('ui.match.view_match') }}</a>
                                        <a href="{{ route('bets.slip', $match->id) }}" class="btn btn-secondary btn-small">Bet Slip</a>
                                    @else
                                        <a href="{{ route('matches.create') }}" class="btn btn-secondary btn-small">{{ __('ui.match.create_match') }}</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="match-section" id="upcoming-matches">
                <div class="match-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.match.my_upcoming') }}</p>
                        <h2>{{ __('ui.match.scheduled') }}</h2>
                    </div>
                </div>

                @if($upcomingMatches->isEmpty())
                    <div class="empty-state-block match-empty-cta">
                        <h3>{{ __('ui.match.no_upcoming') }}</h3>
                        <p>{{ __('ui.match.upcoming_none') }}</p>
                        <a href="{{ route('matches.create') }}" class="btn btn-primary">{{ __('ui.match.create_match') }}</a>
                    </div>
                @else
                    <div class="match-ticket-grid">
                        @foreach($upcomingMatches as $match)
                            <article class="match-ticket">
                                <div class="ticket-header">
                                    <div class="ticket-badge" data-rank="{{ $rankClass($match->player1?->rank ?? 'beginner') }}">
                                        {{ $match->player1?->rank ?? 'Beginner' }}
                                    </div>
                                    <span class="ticket-status">{{ strtoupper($match->status) }}</span>
                                </div>

                                <div class="ticket-players">
                                    <div class="player-stack">
                                        <div class="match-avatar">{{ strtoupper(substr($match->player1->name, 0, 1)) }}</div>
                                        <span class="player-name-ticket">{{ $match->player1->name }}</span>
                                    </div>
                                    <div class="vs-center">VS</div>
                                    <div class="player-stack">
                                        <div class="match-avatar">{{ $match->player2 ? strtoupper(substr($match->player2->name, 0, 1)) : '?' }}</div>
                                        <span class="player-name-ticket">{{ $match->player2?->name ?? __('ui.dashboard.tbd') }}</span>
                                    </div>
                                </div>

                                <div class="ticket-meta-row">
                                    <span class="ticket-meta-item">{{ $match->location ?? __('ui.match.court_tbd') }}</span>
                                    <span class="ticket-meta-item">{{ $match->match_date->format('M d, g A') }}</span>
                                </div>

                                <div class="ticket-footer">
                                    <a href="{{ route('matches.show', $match->id) }}" class="btn btn-primary btn-small">{{ __('ui.match.view_match') }}</a>
                                    @if($match->status !== 'completed')
                                        <a href="{{ route('bets.slip', $match->id) }}" class="btn btn-secondary btn-small">Bet Slip</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>

        <aside class="match-side-column">
            <section class="match-section" id="completed-matches">
                <div class="match-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.match.completed') }}</p>
                        <h2>{{ __('ui.match.history') }}</h2>
                    </div>
                </div>

                @if($completedMatches->isEmpty())
                    <div class="empty-state-block">
                        <p>{{ __('ui.match.no_completed') }}</p>
                    </div>
                @else
                    <div class="match-history-list">
                        @foreach($completedMatches as $match)
                            <article class="match-history-card">
                                <div class="match-history-score">
                                    <span class="@if($match->player1_id === $match->winner_id) winner @endif">{{ $match->player1_score ?? '-' }}</span>
                                    <small>VS</small>
                                    <span class="@if($match->player2_id === $match->winner_id) winner @endif">{{ $match->player2_score ?? '-' }}</span>
                                </div>
                                <div class="match-history-body">
                                    <strong>{{ $match->player1->name }} vs {{ $match->player2?->name ?? 'N/A' }}</strong>
                                    <small>{{ $match->location ?? __('ui.match.court_tbd') }} - {{ $match->match_date->format('M d') }}</small>
                                </div>
                                @if(is_numeric($match->id))
                                    <a href="{{ route('matches.show', $match->id) }}" class="btn btn-secondary btn-small">{{ __('ui.match.details') }}</a>
                                @endif
                                @if($match->status !== 'completed')
                                    <a href="{{ route('bets.slip', $match->id) }}" class="btn btn-primary btn-small">Bet Slip</a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </div>
</div>
@endsection
