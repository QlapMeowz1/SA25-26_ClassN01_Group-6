@extends('layout')

@section('title', $tournament->name . ' - BadNet')

@php
    $participantsCount = $tournament->tournamentParticipants->count();
    $maxParticipants = $tournament->max_participants ?? 0;
    $fillPercent = $maxParticipants > 0 ? min(100, round(($participantsCount / $maxParticipants) * 100)) : 0;
    $statusClass = strtolower(str_replace(' ', '-', (string) $tournament->status));
@endphp

@section('content')
<div class="page-shell tournament-detail-page">
    <section class="tournament-detail-hero">
        <div class="tournament-detail-copy">
            <p class="home-eyebrow">Tournament Detail</p>
            <h1>{{ $tournament->name }}</h1>
            <p class="page-subtitle">Review entry status, prize details, and the current standings.</p>
        </div>

        <span class="tournament-status-badge status-{{ $statusClass }}">{{ ucfirst($tournament->status) }}</span>
    </section>

    <div class="tournament-detail-layout">
        <main class="tournament-main-column">
            <section class="tournament-info-panel">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Overview</p>
                        <h2>Event Information</h2>
                    </div>
                </div>

                <p class="tournament-description">{{ $tournament->description ?: 'No tournament description has been added yet.' }}</p>

                <div class="tournament-detail-grid">
                    <div>
                        <span class="meta-label">Start</span>
                        <strong>{{ $tournament->start_date->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Organizer</span>
                        <strong>{{ $tournament->organizer->name }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Participants</span>
                        <strong>{{ $participantsCount }}/{{ $maxParticipants }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">{{ __('ui.tournament.prize_pool') }}</span>
                        <strong>{{ $tournament->prize_pool > 0 ? $tournament->prize_pool : 'No prize pool' }}</strong>
                    </div>
                </div>

                <div class="tournament-slots tournament-slots-large">
                    <div class="slots-row">
                        <span class="slots-label">Registration progress</span>
                        <span class="slots-value">{{ $participantsCount }}/{{ $maxParticipants }}</span>
                    </div>
                    <div class="progress-bar-small">
                        <div class="progress-fill" style="width: {{ $fillPercent }}%"></div>
                    </div>
                </div>
            </section>

            <section class="tournament-info-panel tournament-participants">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Standings</p>
                        <h2>Participants Leaderboard</h2>
                    </div>
                </div>

                <div class="participant-list tournament-participant-list">
                    @foreach($participants as $p)
                        <article class="tournament-participant-row">
                            <span class="tournament-position">{{ $p->position ?? 'N/A' }}</span>
                            <div class="tournament-participant-player">
                                <span class="team-avatar-small">{{ strtoupper(substr($p->user->name, 0, 1)) }}</span>
                                <div>
                                    <a href="{{ route('profile.show', $p->user->id) }}">{{ $p->user->name }}</a>
                                    <small>{{ $p->user->rank }} - {{ $p->user->elo_rating }} ELO</small>
                                </div>
                            </div>
                            <strong>{{ $p->points }} pts</strong>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="tournament-side-column">
            <section class="tournament-info-panel">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Entry</p>
                        <h2>Registration</h2>
                    </div>
                </div>

                @auth
                    @if(!$tournament->hasParticipant(auth()->id()))
                        @if(!$tournament->isFull())
                            <form action="{{ route('tournaments.join', $tournament->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block">Register</button>
                            </form>
                        @else
                            <p class="error-text">Tournament is full</p>
                        @endif
                    @else
                        <form action="{{ route('tournaments.leave', $tournament->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">Unregister</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-block">Login to Register</a>
                @endauth
            </section>
        </aside>
    </div>

    @if($participants->hasPages())
        <div class="pagination-wrapper">
            {{ $participants->links() }}
        </div>
    @endif
</div>
@endsection
