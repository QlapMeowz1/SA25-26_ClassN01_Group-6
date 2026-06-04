@extends('layout')

@section('title', $tournament->name . ' - BadNet')

@php
    $participantsCount = $tournament->tournamentParticipants->count();
    $maxParticipants = $tournament->max_participants ?? 0;
    $fillPercent = $maxParticipants > 0 ? min(100, round(($participantsCount / $maxParticipants) * 100)) : 0;
    $openSlots = max(0, $maxParticipants - $participantsCount);
    $statusClass = strtolower(str_replace(' ', '-', (string) $tournament->status));
    $isFull = $maxParticipants > 0 && $participantsCount >= $maxParticipants;
    $hasStarted = $tournament->start_date && $tournament->start_date->isPast();
    $isCompleted = $tournament->status === 'completed' || ($tournament->end_date && $tournament->end_date->isPast());
    $entryState = $isCompleted ? 'Completed' : ($isFull ? 'Full' : ($hasStarted ? 'In progress' : 'Open'));
    $description = $tournament->description ?: 'This tournament is ready for a sharper event brief. Add format notes, court rules, prize breakdown, and schedule details to help players prepare.';
    $leader = $participants->getCollection()->sortByDesc('points')->first();
@endphp

@section('content')
<div class="page-shell tournament-detail-page tournament-detail-upgraded">
    <section class="tournament-detail-hero tournament-detail-hero-upgraded">
        <div class="tournament-detail-copy">
            <p class="home-eyebrow">Tournament Detail</p>
            <div class="tournament-title-line">
                <h1>{{ $tournament->name }}</h1>
                <span class="tournament-status-badge status-{{ $statusClass }}">{{ ucfirst($tournament->status) }}</span>
            </div>
            <p class="page-subtitle">Review entry status, prize details, schedule, and the current standings.</p>

            <div class="tournament-hero-metrics">
                <div>
                    <span>Participants</span>
                    <strong>{{ $participantsCount }}/{{ $maxParticipants }}</strong>
                    <small>{{ $openSlots }} seats available</small>
                </div>
                <div>
                    <span>Prize Pool</span>
                    <strong>{{ $tournament->prize_pool > 0 ? number_format($tournament->prize_pool) : 'None' }}</strong>
                    <small>Virtual coins</small>
                </div>
                <div>
                    <span>Entry</span>
                    <strong>{{ $entryState }}</strong>
                    <small>{{ $tournament->start_date->format('M d, h:i A') }}</small>
                </div>
            </div>
        </div>

        <aside class="tournament-hero-registration">
            <div class="tournament-capacity-ring" style="--value: {{ $fillPercent }}%">
                <strong>{{ $fillPercent }}%</strong>
                <span>filled</span>
            </div>
            <div class="tournament-capacity-copy">
                <span>Registration Capacity</span>
                <strong>{{ $participantsCount }} of {{ $maxParticipants }} players</strong>
                <p>{{ $openSlots > 0 ? $openSlots . ' roster seats still open.' : 'The bracket is currently full.' }}</p>
            </div>
        </aside>
    </section>

    <div class="tournament-detail-layout">
        <main class="tournament-main-column">
            <section class="tournament-info-panel tournament-story-panel">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Overview</p>
                        <h2>Event Brief</h2>
                    </div>
                </div>

                <div class="tournament-story-grid">
                    <p class="tournament-description">{{ $description }}</p>
                    <div class="tournament-story-facts">
                        <div>
                            <span>Format</span>
                            <strong>Standard bracket</strong>
                        </div>
                        <div>
                            <span>Organizer</span>
                            <strong>{{ $tournament->organizer->name }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="tournament-info-panel tournament-schedule-panel">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Schedule</p>
                        <h2>Event Information</h2>
                    </div>
                </div>

                <div class="tournament-detail-grid tournament-detail-grid-upgraded">
                    <div>
                        <span class="meta-label">Start</span>
                        <strong>{{ $tournament->start_date->format('M d, Y h:i A') }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">End</span>
                        <strong>{{ $tournament->end_date ? $tournament->end_date->format('M d, Y h:i A') : 'TBD' }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Participants</span>
                        <strong>{{ $participantsCount }}/{{ $maxParticipants }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">{{ __('ui.tournament.prize_pool') }}</span>
                        <strong>{{ $tournament->prize_pool > 0 ? number_format($tournament->prize_pool) . ' coins' : 'No prize pool' }}</strong>
                    </div>
                </div>

                <div class="tournament-slots tournament-slots-large tournament-registration-progress">
                    <div class="tournament-slots-summary">
                        <span class="slots-label">Registration progress:</span>
                        <strong class="slots-value">{{ $participantsCount }}/{{ $maxParticipants }}</strong>
                    </div>
                    <div class="progress-bar-small">
                        <div class="progress-fill" style="width: {{ $fillPercent }}%"></div>
                    </div>
                </div>
            </section>

            <section class="tournament-info-panel tournament-participants tournament-leaderboard-panel">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Standings</p>
                        <h2>Participants Leaderboard</h2>
                    </div>
                    <span class="tournament-roster-count">{{ $participantsCount }} players</span>
                </div>

                <div class="participant-list tournament-participant-list tournament-standings-list">
                    @forelse($participants as $p)
                        <article class="tournament-participant-row tournament-standings-row">
                            <span class="tournament-position">{{ $p->position ? '#' . $p->position : '#' . str_pad($loop->iteration + (($participants->currentPage() - 1) * $participants->perPage()), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="tournament-participant-player">
                                <span class="team-avatar-small">{{ strtoupper(substr($p->user->name, 0, 1)) }}</span>
                                <div>
                                    <a href="{{ route('profile.show', $p->user->id) }}">{{ $p->user->name }}</a>
                                    <small>{{ $p->user->rank }} - {{ $p->user->elo_rating }} ELO</small>
                                </div>
                            </div>
                            <div class="tournament-player-record">
                                <span>{{ $p->user->wins ?? 0 }}W</span>
                                <span>{{ $p->user->losses ?? 0 }}L</span>
                            </div>
                            <strong>{{ $p->points ?? 0 }} pts</strong>
                        </article>
                    @empty
                        <div class="empty-state compact-empty">
                            <h3>No participants yet</h3>
                            <p>Registration is open, but no players have joined this event.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="tournament-side-column">
            <section class="tournament-info-panel tournament-entry-panel">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Entry</p>
                        <h2>Registration</h2>
                    </div>
                </div>

                <div class="tournament-entry-summary">
                    <span>Status</span>
                    <strong>{{ $entryState }}</strong>
                    <p>{{ $isCompleted ? 'This event has finished.' : ($isFull ? 'No seats are currently available.' : 'You can register while seats remain open.') }}</p>
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

            <section class="tournament-info-panel tournament-side-snapshot">
                <div class="tournament-panel-heading">
                    <div>
                        <p class="home-eyebrow">Snapshot</p>
                        <h2>Event Stats</h2>
                    </div>
                </div>
                <div class="tournament-side-stat-grid">
                    <div><span>Seats</span><strong>{{ $participantsCount }}/{{ $maxParticipants }}</strong></div>
                    <div><span>Open</span><strong>{{ $openSlots }}</strong></div>
                    <div><span>Status</span><strong>{{ ucfirst($tournament->status) }}</strong></div>
                    <div><span>Leader</span><strong>{{ $leader?->user?->name ?? 'TBD' }}</strong></div>
                </div>
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
