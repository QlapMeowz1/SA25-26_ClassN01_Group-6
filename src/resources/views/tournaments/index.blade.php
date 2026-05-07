@extends('layout')

@section('title', 'Tournaments - BadNet')

@section('content')
<div class="page-shell tournament-circuit-shell">
    <div class="tournaments-header">
        <div>
            <p class="home-eyebrow">Tournament Circuit</p>
            <h1>Compete & Climb</h1>
            <p class="page-subtitle">Register for tournaments, face off against rivals, and earn prizes on the circuit.</p>
        </div>
        <a href="{{ route('tournaments.create') }}" class="btn btn-primary">Create Tournament</a>
    </div>

    @if($featuredTournament)
        <section class="tournament-featured-section">
            <article class="tournament-card-featured">
                <div class="featured-banner" style="background: linear-gradient(135deg, var(--primary-color), var(--gradient-end));">
                    <span class="featured-badge">FEATURED</span>
                </div>
                
                <div class="featured-content">
                    <div class="featured-header">
                        <div>
                            <h2>{{ $featuredTournament->name }}</h2>
                            <p class="featured-organizer">{{ $featuredTournament->organizer->name }}</p>
                        </div>
                        <div class="featured-stats">
                            <div class="stat-item">
                                <span class="stat-label">Slots</span>
                                <span class="stat-value">{{ $featuredTournament->arena_slots_filled }}/{{ $featuredTournament->max_participants }}</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Prize Pool</span>
                                <span class="stat-value">{{ $featuredTournament->arena_prize_display }}</span>
                            </div>
                        </div>
                    </div>

                    <p class="featured-description">{{ $featuredTournament->description }}</p>

                    <div class="featured-meta">
                        <div class="meta-row">
                            <span class="meta-icon">⏰</span>
                            <div>
                                <span class="meta-label">Starts in</span>
                                <span class="meta-value countdown-feature">{{ $featuredTournament->arena_countdown }}</span>
                            </div>
                        </div>
                        <div class="meta-row">
                            <span class="meta-icon">📅</span>
                            <div>
                                <span class="meta-label">Registration Deadline</span>
                                <span class="meta-value">{{ $featuredTournament->arena_registration_deadline }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="featured-progress">
                        <div class="progress-bar-featured">
                            <div class="progress-fill" style="width: {{ min(100, ($featuredTournament->arena_slots_filled / $featuredTournament->max_participants) * 100) }}%"></div>
                        </div>
                        <span class="progress-text">{{ $featuredTournament->arena_slots_remaining }} slots available</span>
                    </div>

                    @if($featuredTournament->is_sample ?? false)
                        <button type="button" class="btn btn-primary btn-large" onclick="alert('Sample tournament - Click View to learn more')">View & Register</button>
                    @else
                        <a href="{{ route('tournaments.show', $featuredTournament->id) }}" class="btn btn-primary btn-large">View & Register</a>
                    @endif
                </div>
            </article>
        </section>
    @endif

    <section class="tournament-section" id="my-tournaments">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">My Tournaments</p>
                <h2>Your Entries</h2>
            </div>
        </div>
        
        @if($myTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                <h3>You haven't registered for any tournament yet</h3>
                <p>Check out the upcoming ones and join the competition!</p>
                <a href="#upcoming-tournaments" class="btn btn-primary">Browse Tournaments</a>
            </div>
        @else
            <div class="tournament-grid">
                @foreach($myTournaments as $tournament)
                    <article class="tournament-card">
                        <div class="tournament-banner">
                            <div class="tournament-banner-bg"></div>
                            <span class="tournament-status">{{ ucfirst($tournament->status) }}</span>
                        </div>
                        
                        <div class="tournament-card-content">
                            <h3>{{ $tournament->name }}</h3>
                            <p class="tournament-organizer">{{ $tournament->organizer->name }}</p>
                            
                            <div class="tournament-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Participants</span>
                                    <span class="meta-value">{{ $tournament->tournamentParticipants->count() }}/{{ $tournament->max_participants }}</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Prize</span>
                                    <span class="meta-value">💰 {{ number_format($tournament->prize_pool) }}</span>
                                </div>
                            </div>

                            <div class="progress-bar-small">
                                <div class="progress-fill" style="width: {{ min(100, ($tournament->tournamentParticipants->count() / $tournament->max_participants) * 100) }}%"></div>
                            </div>

                            <p class="tournament-date">📅 {{ $tournament->start_date->format('M d, Y') }}</p>

                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-primary btn-block">View Tournament</a>
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
                <h2>Open Brackets</h2>
            </div>
        </div>

        @if($upcomingTournaments->isEmpty())
            <div class="empty-panel tournament-empty-panel">
                <p>No upcoming tournaments at the moment. Check back later!</p>
            </div>
        @else
            <div class="tournament-grid">
                @foreach($upcomingTournaments as $tournament)
                    <article class="tournament-card">
                        <div class="tournament-banner">
                            <div class="tournament-banner-bg"></div>
                            <span class="tournament-status">{{ ucfirst($tournament->status) }}</span>
                        </div>
                        
                        <div class="tournament-card-content">
                            <h3>{{ $tournament->name }}</h3>
                            <p class="tournament-organizer">{{ $tournament->organizer->name }}</p>
                            <p class="tournament-description">{{ \Illuminate\Support\Str::limit($tournament->description, 60) }}</p>
                            
                            <div class="tournament-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Slots</span>
                                    <span class="meta-value">{{ isset($tournament->arena_slots_filled) ? $tournament->arena_slots_filled : $tournament->tournamentParticipants->count() }}/{{ $tournament->max_participants }}</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Prize</span>
                                    <span class="meta-value">💰 {{ isset($tournament->arena_prize_display) ? $tournament->arena_prize_display : number_format($tournament->prize_pool) }}</span>
                                </div>
                            </div>

                            <div class="progress-bar-small">
                                <div class="progress-fill" style="width: {{ min(100, ((isset($tournament->arena_slots_filled) ? $tournament->arena_slots_filled : $tournament->tournamentParticipants->count()) / $tournament->max_participants) * 100) }}%"></div>
                            </div>

                            <div class="tournament-countdown">
                                <span>⏰ {{ isset($tournament->arena_countdown) ? $tournament->arena_countdown : 'Coming soon' }}</span>
                            </div>

                            <p class="tournament-deadline">📋 Deadline: {{ isset($tournament->arena_registration_deadline) ? $tournament->arena_registration_deadline : $tournament->start_date->subDay()->format('M d, g\A') }}</p>

                            @if($tournament->is_sample ?? false)
                                <button type="button" class="btn btn-primary btn-block" onclick="alert('Sample tournament - Click to view details')">View & Register</button>
                            @else
                                <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-primary btn-block">View & Register</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            @if($upcomingTournaments->hasPages())
                <div class="pagination-wrapper">
                    {{ $upcomingTournaments->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
@endsection
