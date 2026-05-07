@extends('layout')

@section('title', $tournament->name . ' - BadNet')

@section('content')
<div class="page-shell tournament-detail-shell">
    <div class="tournaments-header">
        <div>
            <p class="home-eyebrow">Tournament Detail</p>
            <h1>{{ $tournament->name }}</h1>
            <p class="page-subtitle">Review entry status, prize details, and the current bracket standings.</p>
        </div>
        <span class="match-status-pill">{{ ucfirst($tournament->status) }}</span>
    </div>

    <div class="dashboard-section tournament-header">
        <p class="tournament-description">{{ $tournament->description }}</p>
        <div class="tournament-meta">
            <span>📅 {{ $tournament->start_date->format('M d, Y h:i A') }}</span>
            <span>👤 Organizer: {{ $tournament->organizer->name }}</span>
            <span>👥 Participants: {{ $tournament->tournamentParticipants->count() }}/{{ $tournament->max_participants }}</span>
            @if($tournament->prize_pool > 0)
                <span>💰 Prize Pool: {{ $tournament->prize_pool }}</span>
            @endif
        </div>

        @auth
            @if(!$tournament->hasParticipant(auth()->id()))
                @if(!$tournament->isFull())
                    <form action="{{ route('tournaments.join', $tournament->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">Register</button>
                    </form>
                @else
                    <p class="error-text">Tournament is full</p>
                @endif
            @else
                <form action="{{ route('tournaments.leave', $tournament->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Unregister</button>
                </form>
            @endif
        @else
            <p><a href="{{ route('login') }}" class="btn btn-primary">Login to Register</a></p>
        @endauth
    </div>

    <section class="dashboard-section tournament-participants">
        <h2>Participants Leaderboard</h2>
        <div class="participant-list">
            <table class="table">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Player</th>
                        <th>Rank</th>
                        <th>ELO</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participants as $p)
                        <tr>
                            <td>{{ $p->position ?? 'N/A' }}</td>
                            <td><a href="{{ route('profile.show', $p->user->id) }}">{{ $p->user->name }}</a></td>
                            <td>{{ $p->user->rank }}</td>
                            <td>{{ $p->user->elo_rating }}</td>
                            <td>{{ $p->points }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if($participants->hasPages())
        <div>
            {{ $participants->links() }}
        </div>
    @endif
</div>
@endsection
