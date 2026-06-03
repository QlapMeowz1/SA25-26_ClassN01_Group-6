@extends('layout')

@section('title', $team->name . ' - BadNet')

@php
    $isLeader = auth()->check() && auth()->id() === $team->leader_id;
    $isMember = auth()->check() && $team->hasMember(auth()->id());
    $memberCount = $team->members_count ?? $members->total();
    $maxMembers = $team->max_members ?? 20;
    $capacityPercent = $maxMembers > 0 ? min(100, round(($memberCount / $maxMembers) * 100)) : 0;
    $wins = $members->sum('wins');
    $losses = $members->sum('losses');
    $averageElo = round($members->avg('elo_rating') ?? 0);
@endphp

@section('content')
<div class="page-shell team-detail-page">
    <section class="team-detail-hero">
        <div class="team-detail-copy">
            <p class="home-eyebrow">Team Detail</p>
            <h1>{{ $team->name }}</h1>
            <p class="page-subtitle">{{ $team->slogan ?? 'A squad built to compete, improve, and win together.' }}</p>

            <div class="team-pill-row">
                <span class="team-pill team-pill-primary">{{ $team->level ?? 'All Levels' }}</span>
                <span class="team-pill">{{ $team->location ?? 'Location TBD' }}</span>
                <span class="team-pill team-pill-muted">{{ $team->members_count }}/{{ $team->max_members ?? 20 }} members</span>
            </div>

            <div class="team-detail-score-strip">
                <div>
                    <span>Members</span>
                    <strong>{{ $memberCount }}/{{ $maxMembers }}</strong>
                </div>
                <div>
                    <span>Average ELO</span>
                    <strong>{{ $averageElo ?: 'N/A' }}</strong>
                </div>
                <div>
                    <span>Record</span>
                    <strong>{{ $wins }}/{{ $losses }}</strong>
                </div>
            </div>
        </div>

        <aside class="team-detail-summary">
            <div class="team-hero-logo">
                @if($team->logo)
                    <img src="{{ asset('logos/' . $team->logo) }}" alt="{{ $team->name }}" class="team-logo-large">
                @else
                    <div class="team-logo-large placeholder">{{ strtoupper(substr($team->name, 0, 1)) }}</div>
                @endif
            </div>

            <div class="team-detail-actions">
                @auth
                    @if(!$isLeader && !$isMember)
                        <form action="{{ route('teams.join', $team->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block">Join Team</button>
                        </form>
                    @elseif(!$isLeader && $isMember)
                        <form action="{{ route('teams.leave', $team->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">Leave Team</button>
                        </form>
                    @else
                        <span class="team-owner-note">You lead this team</span>
                    @endif
                @endauth
            </div>

            <div class="team-capacity-card">
                <div class="slots-row">
                    <span class="slots-label">Roster capacity</span>
                    <span class="slots-value">{{ $capacityPercent }}%</span>
                </div>
                <div class="progress-bar-small">
                    <div class="progress-fill" style="width: {{ $capacityPercent }}%"></div>
                </div>
                <p>{{ max(0, $maxMembers - $memberCount) }} open roster slots</p>
            </div>
        </aside>
    </section>

    <div class="team-detail-layout">
        <main class="team-main-column">
            <section class="team-section-block team-about-card">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">About</p>
                        <h2>Team Overview</h2>
                    </div>
                    <span class="team-about-badge">BadNet Squad</span>
                </div>
                <p class="team-about-description">{{ $team->description ?: 'No team description has been added yet.' }}</p>
            </section>

            <section class="team-section-block team-performance-panel">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">Performance</p>
                        <h2>Squad Metrics</h2>
                    </div>
                </div>

                <div class="team-performance-grid">
                    <div>
                        <span class="meta-label">Roster Size</span>
                        <strong>{{ $memberCount }}</strong>
                        <small>{{ $maxMembers }} max members</small>
                    </div>
                    <div>
                        <span class="meta-label">Average ELO</span>
                        <strong>{{ $averageElo ?: 'N/A' }}</strong>
                        <small>Based on visible members</small>
                    </div>
                    <div>
                        <span class="meta-label">Total Wins</span>
                        <strong>{{ $wins }}</strong>
                        <small>{{ $losses }} losses recorded</small>
                    </div>
                </div>
            </section>

            <section class="team-section-block team-members-panel">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">Roster</p>
                        <h2>Members</h2>
                    </div>
                </div>

                <div class="member-list">
                    @foreach($members as $member)
                        <article class="member-card team-member-row">
                            <div class="member-info">
                                <span class="team-avatar-small">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                <div>
                                    <a href="{{ route('profile.show', $member->id) }}" class="member-name">{{ $member->name }}</a>
                                    <span class="member-rank">{{ $member->rank }} - {{ $member->elo_rating }} ELO</span>
                                </div>
                                @if($team->leader_id === $member->id)
                                    <span class="badge-leader">Leader</span>
                                @endif
                            </div>
                            <div class="member-stats">
                                <span>{{ $member->wins }}W</span>
                                <span>{{ $member->losses }}L</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="team-side-column">
            <section class="team-section-block">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">Snapshot</p>
                        <h2>Team Stats</h2>
                    </div>
                </div>
                <div class="team-detail-stats">
                    <div>
                        <span class="meta-label">Leader</span>
                        <strong>{{ $team->leader->name }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Members</span>
                        <strong>{{ $team->members_count }}/{{ $team->max_members ?? 20 }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Level</span>
                        <strong>{{ $team->level ?? 'All Levels' }}</strong>
                    </div>
                    <div>
                        <span class="meta-label">Location</span>
                        <strong>{{ $team->location ?? 'TBD' }}</strong>
                    </div>
                </div>
            </section>

            <section class="team-section-block team-leader-panel">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">Leadership</p>
                        <h2>Team Lead</h2>
                    </div>
                </div>
                <div class="team-leader-card">
                    <span class="team-avatar-small">{{ strtoupper(substr($team->leader->name, 0, 1)) }}</span>
                    <div>
                        <a href="{{ route('profile.show', $team->leader->id) }}">{{ $team->leader->name }}</a>
                        <small>{{ $team->leader->rank }} - {{ $team->leader->elo_rating }} ELO</small>
                    </div>
                </div>
            </section>
        </aside>
    </div>

    @if($members->hasPages())
        <div class="pagination-wrapper">
            {{ $members->links() }}
        </div>
    @endif
</div>
@endsection
