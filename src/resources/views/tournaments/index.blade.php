@extends('layout')

@section('title', 'Tournaments - BadNet')

@section('content')
<div class="page-shell">
    <div class="tournaments-header">
        <div>
            <p class="home-eyebrow">Tournament Circuit</p>
            <h1>Tournaments</h1>
            <p class="page-subtitle">Register for upcoming brackets, monitor your current entries, and jump into the next event window.</p>
        </div>
        <a href="{{ route('tournaments.create') }}" class="btn btn-primary">Create Tournament</a>
    </div>

    <div class="filter-tabs">
        <a href="#my-tournaments" class="filter-tab">My Tournaments</a>
        <a href="#upcoming-tournaments" class="filter-tab">Upcoming Tournaments</a>
    </div>

    <div class="tournaments-grid">
        <section class="tournament-section" id="my-tournaments">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">My Tournaments</p>
                    <h2>Your entries</h2>
                </div>
            </div>
            @if($myTournaments->isEmpty())
                <div class="empty-panel">
                    <p class="empty-message">You haven't registered for any tournaments</p>
                    <a href="#upcoming-tournaments" class="btn btn-secondary btn-block">Browse upcoming events</a>
                </div>
            @else
                <div class="tournament-list">
                    @foreach($myTournaments as $tournament)
                        <article class="tournament-card tournament-card-rich">
                            <div class="tournament-topline">
                                <span class="match-status-pill">{{ ucfirst($tournament->status) }}</span>
                                <span class="rank-tag">{{ $tournament->tournamentParticipants->count() }}/{{ $tournament->max_participants }}</span>
                            </div>
                            <h3>{{ $tournament->name }}</h3>
                            <p class="tournament-organizer">By: {{ $tournament->organizer->name }}</p>
                            <p class="tournament-dates">📅 {{ $tournament->start_date->format('M d, Y') }} @if($tournament->end_date) - {{ $tournament->end_date->format('M d, Y') }} @endif</p>
                            <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-primary btn-block">View Tournament</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="tournament-section" id="upcoming-tournaments">
            <div class="feed-heading">
                <div>
                    <p class="home-eyebrow">Upcoming Tournaments</p>
                    <h2>Next brackets</h2>
                </div>
            </div>
            @if($upcomingTournaments->isEmpty())
                <div class="empty-panel">
                    <p class="empty-message">No upcoming tournaments</p>
                </div>
            @else
                <div class="tournament-list">
                    @foreach($upcomingTournaments as $tournament)
                        <article class="tournament-card tournament-card-rich">
                            <div class="tournament-topline">
                                <span class="match-status-pill">{{ ucfirst($tournament->status) }}</span>
                                <span class="rank-tag">{{ $tournament->tournamentParticipants->count() }}/{{ $tournament->max_participants }}</span>
                            </div>
                            <h3>{{ $tournament->name }}</h3>
                            <p class="tournament-description">{{ \Illuminate\Support\Str::limit($tournament->description, 80) }}</p>
                            <p class="tournament-organizer">By: {{ $tournament->organizer->name }}</p>
                            <p class="tournament-dates">� {{ $tournament->start_date->format('M d, Y') }}</p>
                            @if(!auth()->check() || !$tournament->hasParticipant(auth()->id()))
                                <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-primary btn-block">View & Register</a>
                            @else
                                <a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-secondary btn-block">View Tournament</a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    @if($upcomingTournaments->hasPages())
        <div>
            {{ $upcomingTournaments->links() }}
        </div>
    @endif
</div>
@endsection
