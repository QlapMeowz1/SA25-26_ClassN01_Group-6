@extends('layout')

@section('title', $team->name . ' - BadNet')

@php
    $isLeader = auth()->check() && auth()->id() === $team->leader_id;
    $isMember = auth()->check() && $team->hasMember(auth()->id());
    $memberCollection = $members->getCollection();
    $memberCount = $team->members_count ?? $members->total();
    $maxMembers = $team->max_members ?? 20;
    $capacityPercent = $maxMembers > 0 ? min(100, round(($memberCount / $maxMembers) * 100)) : 0;
    $openSlots = max(0, $maxMembers - $memberCount);
    $wins = $memberCollection->sum('wins');
    $losses = $memberCollection->sum('losses');
    $totalGames = $wins + $losses;
    $winRate = $totalGames > 0 ? round(($wins / $totalGames) * 100) : 0;
    $averageElo = round($memberCollection->avg('elo_rating') ?? 0);
    $topMember = $memberCollection->sortByDesc('elo_rating')->first();
    $teamInitial = strtoupper(substr($team->name, 0, 1));
    $teamDescription = $team->description ?: 'This squad is ready for a stronger identity. Add a short story, training focus, or weekly play style to help new players understand the team.';
    $createdLabel = $team->created_at ? $team->created_at->format('M Y') : 'New squad';
    $capacityLabel = $capacityPercent >= 90 ? 'Nearly full' : ($capacityPercent >= 50 ? 'Growing roster' : 'Open roster');
@endphp

@section('content')
<div class="page-shell team-detail-page">
    <section class="team-detail-hero team-detail-hero-upgraded">
        <div class="team-detail-copy">
            <p class="home-eyebrow">Team Detail</p>
            <div class="team-title-line">
                <h1>{{ $team->name }}</h1>
                <span class="team-status-chip">{{ $capacityLabel }}</span>
            </div>
            <p class="page-subtitle">{{ $team->slogan ?? 'A squad built to compete, improve, and win together.' }}</p>

            <div class="team-pill-row">
                <span class="team-pill team-pill-primary">{{ $team->level ?? 'All Levels' }}</span>
                <span class="team-pill">{{ $team->location ?? 'Location TBD' }}</span>
                <span class="team-pill team-pill-muted">Founded {{ $createdLabel }}</span>
            </div>

            <div class="team-hero-insight-grid">
                <div>
                    <span>Members</span>
                    <strong>{{ $memberCount }}/{{ $maxMembers }}</strong>
                    <small>{{ $openSlots }} open slots</small>
                </div>
                <div>
                    <span>Average ELO</span>
                    <strong>{{ $averageElo ?: 'N/A' }}</strong>
                    <small>{{ $topMember ? 'Top: ' . $topMember->name : 'No players yet' }}</small>
                </div>
                <div>
                    <span>Win Rate</span>
                    <strong>{{ $winRate }}%</strong>
                    <small>{{ $wins }}W / {{ $losses }}L</small>
                </div>
            </div>
        </div>

        <aside class="team-detail-summary">
            <div class="team-identity-card">
                <div class="team-hero-logo">
                @if($team->logo)
                    <img src="{{ asset('logos/' . $team->logo) }}" alt="{{ $team->name }}" class="team-logo-large">
                @else
                    <div class="team-logo-large placeholder">{{ $teamInitial }}</div>
                @endif
                </div>
                <div>
                    <strong>{{ $team->leader->name }}</strong>
                    <span>Team lead</span>
                </div>
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
            <section class="team-section-block team-about-card team-story-panel">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">About</p>
                        <h2>Team Overview</h2>
                    </div>
                    <span class="team-about-badge">BadNet Squad</span>
                </div>

                <div class="team-story-layout">
                    <p class="team-about-description">{{ $teamDescription }}</p>
                    <div class="team-story-facts">
                        <div>
                            <span>Play Style</span>
                            <strong>{{ $team->level ?? 'All Levels' }}</strong>
                        </div>
                        <div>
                            <span>Home Court</span>
                            <strong>{{ $team->location ?? 'TBD' }}</strong>
                        </div>
                    </div>
                </div>
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
                        <small>{{ $openSlots }} available positions</small>
                    </div>
                    <div>
                        <span class="meta-label">Average ELO</span>
                        <strong>{{ $averageElo ?: 'N/A' }}</strong>
                        <small>Based on visible members</small>
                    </div>
                    <div>
                        <span class="meta-label">Match Form</span>
                        <strong>{{ $wins }}/{{ $losses }}</strong>
                        <small>{{ $winRate }}% win rate</small>
                    </div>
                </div>

                <div class="team-readiness-row">
                    <div class="team-readiness-copy">
                        <span>Roster Readiness</span>
                        <strong>{{ $capacityLabel }}</strong>
                        <p>{{ $openSlots > 0 ? $openSlots . ' more players can join before the squad reaches capacity.' : 'The roster is currently full.' }}</p>
                    </div>
                    <div class="team-readiness-meter" aria-label="Roster capacity {{ $capacityPercent }} percent">
                        <span style="width: {{ $capacityPercent }}%"></span>
                    </div>
                </div>
            </section>

            <section class="team-section-block team-members-panel">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">Roster</p>
                        <h2>Members</h2>
                    </div>
                    <span class="team-about-badge">{{ $memberCount }} players</span>
                </div>

                @if($canManageTeam)
                    <form method="POST" action="{{ route('teams.members.add', $team->id) }}" class="member-manage-form">
                        @csrf
                        <label for="team_member_user_id">Add member</label>
                        <select id="team_member_user_id" name="user_id" required>
                            <option value="">Choose a player...</option>
                            @foreach($availableUsers as $candidate)
                                <option value="{{ $candidate->id }}">{{ $candidate->name }} - {{ $candidate->email }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary btn-small" @disabled($availableUsers->isEmpty())>Add</button>
                    </form>
                @endif

                <div class="team-roster-list">
                    @forelse($members as $member)
                        @php
                            $memberWins = (int) ($member->wins ?? 0);
                            $memberLosses = (int) ($member->losses ?? 0);
                            $memberTotal = $memberWins + $memberLosses;
                            $memberWinRate = $memberTotal > 0 ? round(($memberWins / $memberTotal) * 100) : 0;
                            $memberRole = $member->pivot?->role ?? ($team->leader_id === $member->id ? 'leader' : 'member');
                            $joinedAt = $member->pivot?->created_at;
                        @endphp
                        <article class="team-roster-row">
                            <span class="team-roster-rank">#{{ str_pad($loop->iteration + (($members->currentPage() - 1) * $members->perPage()), 2, '0', STR_PAD_LEFT) }}</span>
                            <div class="member-info">
                                <span class="team-avatar-small">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                <div>
                                    <a href="{{ route('profile.show', $member->id) }}" class="member-name">{{ $member->name }}</a>
                                    <span class="member-rank">{{ $member->rank ?? 'Unranked' }} - {{ $member->elo_rating ?? 0 }} ELO</span>
                                    <span class="member-contact">{{ $member->email }}</span>
                                    <span class="member-joined">{{ $joinedAt ? 'Joined ' . $joinedAt->diffForHumans() : 'Recently joined' }}</span>
                                </div>
                            </div>
                            <div class="team-roster-role">
                                @if($memberRole === 'leader')
                                    <span class="badge-leader">Leader</span>
                                @else
                                    <span class="team-role-badge">Member</span>
                                @endif
                            </div>
                            <div class="member-stats">
                                <span>{{ $memberWins }}W</span>
                                <span>{{ $memberLosses }}L</span>
                                <span>{{ $memberWinRate }}%</span>
                            </div>
                            @if($canManageTeam && $memberRole !== 'leader')
                                <form method="POST" action="{{ route('teams.members.remove', [$team->id, $member->id]) }}" class="member-remove-form" onsubmit="return confirm('Remove this member from the team?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-small">Remove</button>
                                </form>
                            @endif
                        </article>
                    @empty
                        <div class="empty-state compact-empty">
                            <h3>No members yet</h3>
                            <p>This team is waiting for its first roster additions.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="team-side-column">
            <section class="team-section-block team-profile-card">
                <div class="team-profile-mark">{{ $teamInitial }}</div>
                <div>
                    <p class="home-eyebrow">Squad Profile</p>
                    <h2>{{ $team->name }}</h2>
                    <p>{{ $team->slogan ?? 'Built for regular play and steady improvement.' }}</p>
                </div>
            </section>

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

            <section class="team-section-block team-next-step-panel">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">Next Step</p>
                        <h2>Grow This Team</h2>
                    </div>
                </div>
                <div class="team-next-step-list">
                    <span>Add a sharper team description</span>
                    <span>Invite players with matching level</span>
                    <span>Schedule a challenge from Matches</span>
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
