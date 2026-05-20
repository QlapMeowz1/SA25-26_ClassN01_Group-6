@extends('layout')

@section('title', $team->name . ' - BadNet')

@section('content')
<div class="page-shell team-detail-shell">
    <section class="team-hero-card">
        <div class="team-hero-copy">
            <p class="home-eyebrow">Team Detail</p>
            <h1>{{ $team->name }}</h1>
            <p class="page-subtitle">{{ $team->slogan ?? 'A squad built to compete, improve, and win together.' }}</p>

            <div class="team-pill-row">
                <span class="team-pill team-pill-primary">{{ $team->level ?? 'All Levels' }}</span>
                <span class="team-pill">{{ $team->location ?? 'Location TBD' }}</span>
                <span class="team-pill team-pill-muted">{{ $team->members_count }}/{{ $team->max_members ?? 20 }} members</span>
            </div>
        </div>

        <div class="team-hero-summary">
            <div class="team-hero-logo">
                @if($team->logo)
                    <img src="{{ asset('logos/' . $team->logo) }}" alt="{{ $team->name }}" class="team-logo-large">
                @else
                    <div class="team-logo-large placeholder">{{ strtoupper(substr($team->name, 0, 1)) }}</div>
                @endif
            </div>

            <div class="team-hero-meta">
                <div class="team-meta-card">
                    <span class="meta-label">Leader</span>
                    <strong>{{ $team->leader->name }}</strong>
                </div>
                <div class="team-meta-card">
                    <span class="meta-label">Members</span>
                    <strong>{{ $team->members_count }}</strong>
                </div>
                <div class="team-meta-card">
                    <span class="meta-label">Active Level</span>
                    <strong>{{ $team->level ?? 'All Levels' }}</strong>
                </div>
            </div>

            <div class="team-hero-actions">
                @auth
                    @if(auth()->id() !== $team->leader_id && !$team->hasMember(auth()->id()))
                        <form action="{{ route('teams.join', $team->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block">Join Team</button>
                        </form>
                    @elseif(auth()->id() !== $team->leader_id && $team->hasMember(auth()->id()))
                        <form action="{{ route('teams.leave', $team->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">Leave Team</button>
                        </form>
                    @endif
                @endauth
            </div>
        </div>
    </section>

    <section class="dashboard-section team-about-card">
        <div class="team-about-header">
            <div>
                <p class="home-eyebrow">About</p>
                <h2>Team Overview</h2>
            </div>
            <span class="team-about-badge">BadNet Squad</span>
        </div>
        <p class="team-about-description">{{ $team->description }}</p>
    </section>

    <section class="dashboard-section team-members">
        <h2>Members</h2>
        <div class="member-list">
            @foreach($members as $member)
                <div class="member-card">
                    <div class="member-info">
                        <a href="{{ route('profile.show', $member->id) }}" class="member-name">
                            {{ $member->name }}
                        </a>
                        <span class="member-rank">{{ $member->rank }}</span>
                        @if($team->leader_id === $member->id)
                            <span class="badge-leader">Leader</span>
                        @endif
                    </div>
                    <div class="member-stats">
                        <span>{{ $member->elo_rating }} ELO</span>
                        <span>W/L: {{ $member->wins }}/{{ $member->losses }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    @if($members->hasPages())
        <div>
            {{ $members->links() }}
        </div>
    @endif
</div>
@endsection
