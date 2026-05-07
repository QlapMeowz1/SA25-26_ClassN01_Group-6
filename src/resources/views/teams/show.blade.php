@extends('layout')

@section('title', $team->name . ' - BadNet')

@section('content')
<div class="page-shell team-detail-shell">
    <div class="teams-header">
        <div>
            <p class="home-eyebrow">Team Detail</p>
            <h1>{{ $team->name }}</h1>
            <p class="page-subtitle">Check the lineup, leader, and membership controls for this team.</p>
        </div>
    </div>

    <div class="dashboard-section team-header">
        @if($team->logo)
            <img src="{{ asset('logos/' . $team->logo) }}" alt="{{ $team->name }}" class="team-logo-large">
        @else
            <div class="team-logo-large placeholder">{{ strtoupper(substr($team->name, 0, 1)) }}</div>
        @endif
        <div class="team-info">
            <h1>{{ $team->name }}</h1>
            <p class="team-description">{{ $team->description }}</p>
            <p class="team-stats">
                <span>Leader: <strong>{{ $team->leader->name }}</strong></span>
                <span>Members: <strong>{{ $team->members_count }}</strong></span>
            </p>
        </div>

        @auth
            @if(auth()->id() !== $team->leader_id && !$team->hasMember(auth()->id()))
                <form action="{{ route('teams.join', $team->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">Join Team</button>
                </form>
            @elseif(auth()->id() !== $team->leader_id && $team->hasMember(auth()->id()))
                <form action="{{ route('teams.leave', $team->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">Leave Team</button>
                </form>
            @endif
        @endauth
    </div>

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
