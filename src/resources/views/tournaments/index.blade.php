@extends('layout')

@section('title', 'Tournaments - BadNet')

@section('content')
<div class="page-shell tournament-circuit-shell">
    <div class="tournaments-header">
        <div>
            <p class="home-eyebrow">Tournament Circuit</p>
            <h1>Compete & Conquer</h1>
            <p class="page-subtitle">Register for upcoming tournaments, track your entries, and claim your prizes.</p>
        </div>
        <a href="{{ route('tournaments.create') }}" class="btn btn-primary">Create Tournament</a>
    </div>

    @if(!empty($featuredTournament))
    <section class="featured-tournament-section">
        <div class="featured-tournament-card">
            <div class="featured-banner" style="background: linear-gradient(135deg, {{ $featuredTournament->banner_color ?? '#6366f1' }}, rgba(99, 102, 241, 0.6))">
                <div class="featured-badge">🔥 Featured</div>
                <div class="featured-countdown">
                    <span class="countdown-value">{{ $featuredTournament->countdown }}</span>
                </div>
            </div>
            
            <div class="featured-content">
                <div class="featured-header">
                    <h2>{{ $featuredTournament->name }}</h2>
                    <span class="tournament-type-badge" data-type="{{ $featuredTournament->tournament_type ?? 'standard' }}">
                        {{ ucfirst($featuredTournament->tournament_type ?? 'Tournament') }}
                    </span>
                </div>

                <p class="featured-description">{{ $featuredTournament->description }}</p>

                <div class="featured-meta-row">
                    <div class="featured-meta-block">
                        <span class="meta-icon">💰</span>
                        <div class="meta-text">
                            <span class="meta-label">Prize Pool</span>
                            <span class="meta-value">{{ $featuredTournament->prize_details ?? 'TBD' }}</span>
                        </div>
                    </div>
                    <div class="featured-meta-block">
                        <span class="meta-icon">👥</span>
                        <div class="meta-text">
                            <span class="meta-label">Slots Available</span>
                            <span class="meta-value">{{ $featuredTournament->slots_filled }}/{{ $featuredTournament->slots_total }}</span>
                        </div>
                    </div>
                    <div class="featured-meta-block">
                        <span class="meta-icon">📅</span>
                        <div class="meta-text">
                            <span class="meta-label">Starts</span>
                            <span class="meta-value">{{ $featuredTournament->start_date->format('M d, g\A') }}</span>
                        </div>
                    </div>
                </div>

                <div class="featured-slots-bar">
                    <div class="slots-label">Registration Progress</div>
                    <div class="progress-bar-large">
                        <div class="progress-fill" style="width: {{ $featuredTournament->slots_percentage }}%"></div>
                    </div>
                    <div class="slots-text">{{ $featuredTournament->slots_filled }} of {{ $featuredTournament->slots_total }} spots filled</div>
                </div>

                <div class="featured-actions">
                    <a href="{{ route('tournaments.show', $featuredTournament->id) }}" class="btn btn-primary btn-large">Register Now</a>
                    <p class="deadline-text">Register by {{ $featuredTournament->time_until_deadline }}</p>
                </div>
            </div>
        </div>
    </section>
    @endif

    <section class="tournament-section" id="my-tournaments">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">My Tournaments</p>
                <h2>Your Registrations</h2>
            </div>
        </div>
        @if($myTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                <h3>You haven't registered for any tournament yet</h3>
                <p>Check out the upcoming ones!</p>
                <a href="#upcoming-tournaments" class="btn btn-primary">Browse Tournaments</a>
            </div>
        @else
            <div class="tournament-card-grid">
                @foreach($myTournaments as $tournament)
                    <article class="tournament-card">
                        <div class="tournament-banner" style="background: linear-gradient(135deg, {{ $tournament->banner_color ?? '#6366f1' }}, rgba(99, 102, 241, 0.6))">
                            <span class="tournament-status-badge">{{ ucfirst($tournament->status) }}</span>
                            <span class="countdown-pill">{{ $tournament->countdown }}</span>
                        </div>

                        <div class="tournament-card-content">
                            <h3>{{ $tournament->name }}</h3>
                            <p class="tournament-organizer">Organized by: {{ $tournament->organizer?->name ?? 'Community' }}</p>

                            <div class="tournament-slots">
                                <span class="slots-label">Slots: {{ $tournament->slots_filled }}/{{ $tournament->slots_total }}</span>
                                <div class="progress-bar-small">
                                    <div class="progress-fill" style="width: {{ $tournament->slots_percentage }}%"></div>
                                </div>
                            </div>

                            <div class="tournament-prize">
                                <span class="prize-icon">💰</span>
                                <span class="prize-text">{{ $tournament->prize_details ?? 'Prize pool: ' . number_format($tournament->prize_pool ?? 0) . ' coins' }}</span>
                            </div>

                            <div class="tournament-deadline">
                                <span class="deadline-icon">⏰</span>
                                <span class="deadline-text">Register by: {{ $tournament->time_until_deadline ?? 'N/A' }}</span>
                            </div>

                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-primary btn-block">View Details</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="tournament-section" id="upcoming-tournaments">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">Upcoming Tournaments</p>
                <h2>Open Events</h2>
            </div>
        </div>
        @if($upcomingTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                <h3>No tournaments coming up</h3>
                <p>Create one to start the next big competition!</p>
                <a href="{{ route('tournaments.create') }}" class="btn btn-primary">Create Tournament</a>
            </div>
        @else
            <div class="tournament-card-grid">
                @foreach($upcomingTournaments as $tournament)
                    <article class="tournament-card">
                        <div class="tournament-banner" style="background: linear-gradient(135deg, {{ $tournament->banner_color ?? '#6366f1' }}, rgba(99, 102, 241, 0.6))">
                            <span class="tournament-status-badge">{{ ucfirst($tournament->status) }}</span>
                            <span class="countdown-pill">{{ $tournament->countdown }}</span>
                        </div>

                        <div class="tournament-card-content">
                            <h3>{{ $tournament->name }}</h3>
                            <p class="tournament-description">{{ \Illuminate\Support\Str::limit($tournament->description, 70) }}</p>
                            <p class="tournament-organizer">Organized by: {{ $tournament->organizer?->name ?? 'Community' }}</p>

                            <div class="tournament-slots">
                                <span class="slots-label">Slots: {{ $tournament->slots_filled }}/{{ $tournament->slots_total }}</span>
                                <div class="progress-bar-small">
                                    <div class="progress-fill" style="width: {{ $tournament->slots_percentage }}%"></div>
                                </div>
                            </div>

                            <div class="tournament-prize">
                                <span class="prize-icon">💰</span>
                                <span class="prize-text">{{ $tournament->prize_details ?? 'Prize pool: ' . number_format($tournament->prize_pool ?? 0) . ' coins' }}</span>
                            </div>

                            <div class="tournament-deadline">
                                <span class="deadline-icon">⏰</span>
                                <span class="deadline-text">Register by: {{ $tournament->time_until_deadline ?? 'N/A' }}</span>
                            </div>

                            @if(!auth()->check() || !$tournament->hasParticipant(auth()->id()))
                                <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-primary btn-block">Register</a>
                            @else
                                <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-secondary btn-block">View Details</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
