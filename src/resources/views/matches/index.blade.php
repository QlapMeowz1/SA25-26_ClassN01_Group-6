@extends('layout')

@section('title', 'Matches - BadNet')

@section('content')
<div class="page-shell match-arena-shell">
    <div class="matches-header">
        <div>
            <p class="home-eyebrow">Match Arena</p>
            <h1>Find Your Next Match</h1>
            <p class="page-subtitle">Browse open matches, manage your schedule, and climb the ranks.</p>
        </div>
        <div class="matches-header-actions">
            <form action="{{ route('matches.quick') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary">Quick Match</button>
            </form>
            <a href="{{ route('matches.create') }}" class="btn btn-secondary">Create Match</a>
        </div>
    </div>

    <form method="GET" action="{{ route('matches.index') }}" class="filter-bar-simplified">
        <div class="filter-controls">
            <div class="filter-input-group">
                <input type="text" id="location" name="location" value="{{ $filters['location'] ?? '' }}" placeholder="📍 Location">
            </div>
            <div class="filter-chips">
                @foreach(['Beginner', 'Intermediate', 'Advanced', 'Professional'] as $level)
                    <label class="chip">
                        <input type="radio" name="skill_level" value="{{ $level }}" @checked(($filters['skill_level'] ?? '') === $level)>
                        <span>{{ $level }}</span>
                    </label>
                @endforeach
            </div>
            <div class="filter-date-picker">
                <select id="date" name="date">
                    <option value="">📅 Any time</option>
                    <option value="today" @selected(($filters['date'] ?? '') === 'today')>Today</option>
                    <option value="tomorrow" @selected(($filters['date'] ?? '') === 'tomorrow')>Tomorrow</option>
                    <option value="weekend" @selected(($filters['date'] ?? '') === 'weekend')>This Weekend</option>
                </select>
            </div>
            <button type="submit" class="btn btn-small btn-primary">Filter</button>
            <a href="{{ route('matches.index') }}" class="btn btn-small btn-secondary">Reset</a>
        </div>
    </form>

    <div class="matches-grid-tickets">
        <section class="match-section" id="open-matches">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">Open Matches</p>
                    <h2>Available Invitations</h2>
                </div>
            </div>

            @if($openMatches->isEmpty())
                <div class="empty-state-block match-empty-cta">
                    <h3>Be the first to create a match!</h3>
                    <p>No open matches yet. Challenge someone to a game and get started.</p>
                    <a href="{{ route('matches.create') }}" class="btn btn-primary">Create Match</a>
                </div>
            @else
                <div class="match-ticket-grid">
                    @foreach($openMatches as $match)
                        <article class="match-ticket">
                            <div class="ticket-perforated"></div>
                            
                            <div class="ticket-header">
                                <div class="ticket-badge" data-rank="{{ $match->arena_badge_class ?? 'beginner' }}">
                                    {{ $match->arena_skill ?? 'Beginner' }}
                                </div>
                                <span class="ticket-status">{{ $match->status === 'open' ? 'OPEN' : 'AVAILABLE' }}</span>
                            </div>

                            <div class="ticket-players">
                                <div class="player-stack">
                                    <div class="match-avatar">{{ strtoupper(substr($match->player1->name, 0, 1)) }}</div>
                                    <span class="player-name-ticket">{{ $match->player1->name }}</span>
                                </div>
                                <div class="vs-center">VS</div>
                                <div class="player-stack">
                                    <div class="match-avatar-waiting">?</div>
                                    <span class="player-name-ticket">Find opponent</span>
                                </div>
                            </div>

                            <div class="ticket-meta-row">
                                <span class="ticket-meta-item">📍 {{ $match->arena_location ?? $match->location }}</span>
                                <span class="ticket-meta-item">🕒 {{ $match->arena_time ?? $match->match_date->format('M d') }}</span>
                            </div>

                            @if(!($match->is_sample ?? false) && $match->joinRequests && $match->joinRequests->count() > 0)
                                <div class="ticket-requests">
                                    <p class="ticket-requests-header">{{ $match->joinRequests->count() }} join request{{ $match->joinRequests->count() !== 1 ? 's' : '' }}</p>
                                </div>
                            @endif

                            <div class="ticket-footer">
                                @if($match->is_sample ?? false)
                                    <button type="button" class="btn btn-primary btn-small" onclick="alert('Sample match data')">View</button>
                                @else
                                    <a href="{{ route('matches.show', $match->id) }}" class="btn btn-primary btn-small">View Match</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="match-section" id="upcoming-matches">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">My Upcoming Matches</p>
                    <h2>Scheduled Fixtures</h2>
                </div>
            </div>

            @if($upcomingMatches->isEmpty())
                <div class="empty-state-block match-empty-cta">
                    <h3>No upcoming matches</h3>
                    <p>Create or join a match to get on the schedule.</p>
                    <a href="{{ route('matches.create') }}" class="btn btn-primary">Create Match</a>
                </div>
            @else
                <div class="match-ticket-grid">
                    @foreach($upcomingMatches as $match)
                        <article class="match-ticket">
                            <div class="ticket-perforated"></div>
                            
                            <div class="ticket-header">
                                <div class="ticket-badge" data-rank="{{ strtolower($match->player1?->rank ?? 'beginner') }}">
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
                                    <span class="player-name-ticket">{{ $match->player2?->name ?? 'TBD' }}</span>
                                </div>
                            </div>

                            <div class="ticket-meta-row">
                                <span class="ticket-meta-item">📍 {{ $match->location ?? 'Court TBD' }}</span>
                                <span class="ticket-meta-item">🕒 {{ $match->match_date->format('M d, g\A') }}</span>
                            </div>

                            <div class="ticket-footer">
                                <a href="{{ route('matches.show', $match->id) }}" class="btn btn-primary btn-small">View Match</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="match-section" id="completed-matches">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">Completed Matches</p>
                    <h2>Match History</h2>
                </div>
            </div>

            @if($completedMatches->isEmpty())
                <div class="empty-state-block">
                    <p>No completed matches yet. Start playing to build your record.</p>
                </div>
            @else
                <div class="match-ticket-grid">
                    @foreach($completedMatches as $match)
                        <article class="match-ticket match-ticket-completed">
                            <div class="ticket-perforated"></div>
                            
                            <div class="ticket-header">
                                <div class="ticket-badge" data-rank="completed">Completed</div>
                                <span class="ticket-status">🏆 RESULT</span>
                            </div>

                            <div class="ticket-players">
                                <div class="player-stack">
                                    <div class="match-avatar">{{ strtoupper(substr($match->player1->name, 0, 1)) }}</div>
                                    <span class="player-name-ticket @if($match->player1_id === $match->winner_id) winner @endif">
                                        {{ $match->player1->name }}
                                    </span>
                                    <span class="match-score">{{ $match->player1_score ?? '—' }}</span>
                                </div>
                                <div class="vs-center">VS</div>
                                <div class="player-stack">
                                    <div class="match-avatar">{{ $match->player2 ? strtoupper(substr($match->player2->name, 0, 1)) : '?' }}</div>
                                    <span class="player-name-ticket @if($match->player2_id === $match->winner_id) winner @endif">
                                        {{ $match->player2?->name ?? 'N/A' }}
                                    </span>
                                    <span class="match-score">{{ $match->player2_score ?? '—' }}</span>
                                </div>
                            </div>

                            <div class="ticket-footer">
                                <a href="{{ route('matches.show', $match->id) }}" class="btn btn-primary btn-small">Details</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
