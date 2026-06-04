@extends('layout')

@section('title', 'Admin Console - BadNet')

@section('content')
<div class="page-shell admin-console-page">
    <section class="admin-hero">
        <div>
            <p class="home-eyebrow">Admin Console</p>
            <h1>BadNet Operations</h1>
            <p class="page-subtitle">Monitor community activity, match flow, tournaments, and betting signals from one control surface.</p>
        </div>

        <div class="admin-hero-card">
            <span>Admins</span>
            <strong>{{ number_format($stats['admins']) }}</strong>
            <small>{{ number_format($stats['users']) }} total users</small>
        </div>
    </section>

    @include('admin.partials.nav')

    <section class="admin-health-strip">
        <div>
            <span>New users / 7d</span>
            <strong>{{ number_format($stats['new_users']) }}</strong>
        </div>
        <div>
            <span>New posts / 7d</span>
            <strong>{{ number_format($stats['new_posts']) }}</strong>
        </div>
        <div>
            <span>Completed matches</span>
            <strong>{{ number_format($stats['completed_matches']) }}</strong>
        </div>
        <div>
            <span>Bet volume</span>
            <strong>{{ number_format($stats['bet_volume']) }}</strong>
        </div>
        <div>
            <span>Pending stake</span>
            <strong>{{ number_format($stats['pending_bet_volume']) }}</strong>
        </div>
    </section>

    <section class="admin-stat-grid">
        <article class="admin-stat-card">
            <span>Users</span>
            <strong>{{ number_format($stats['users']) }}</strong>
            <small>{{ number_format($stats['admins']) }} admin accounts</small>
        </article>
        <article class="admin-stat-card">
            <span>Community</span>
            <strong>{{ number_format($stats['posts']) }}</strong>
            <small>{{ number_format($stats['comments']) }} comments</small>
        </article>
        <article class="admin-stat-card">
            <span>Matches</span>
            <strong>{{ number_format($stats['matches']) }}</strong>
            <small>{{ number_format($stats['open_matches']) }} open queues</small>
        </article>
        <article class="admin-stat-card">
            <span>Tournaments</span>
            <strong>{{ number_format($stats['tournaments']) }}</strong>
            <small>{{ number_format($stats['active_tournaments']) }} active events</small>
        </article>
        <article class="admin-stat-card">
            <span>Teams</span>
            <strong>{{ number_format($stats['teams']) }}</strong>
            <small>Squads created</small>
        </article>
        <article class="admin-stat-card">
            <span>Bets</span>
            <strong>{{ number_format($stats['bets']) }}</strong>
            <small>{{ number_format($stats['pending_bets']) }} pending tickets</small>
        </article>
    </section>

    <div class="admin-layout">
        <main class="admin-main-column">
            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Quick Actions</p>
                        <h2>Management Shortcuts</h2>
                    </div>
                </div>

                <div class="admin-action-grid">
                    <a href="{{ route('admin.users') }}">Review users <span>Roles, ELO, accounts</span></a>
                    <a href="{{ route('admin.content') }}">Moderate content <span>Posts and comments</span></a>
                    <a href="{{ route('admin.matches') }}">Track matches <span>Queue and results</span></a>
                    <a href="{{ route('admin.bets') }}">Audit bets <span>Tickets and payout flow</span></a>
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Users</p>
                        <h2>Recent Accounts</h2>
                    </div>
                    <a href="{{ route('admin.users') }}" class="feed-link">Manage</a>
                </div>

                <div class="admin-row-list">
                    @foreach($recentUsers as $user)
                        <div class="admin-user-row">
                            <span class="admin-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <small>{{ $user->email }} - {{ ucfirst($user->role ?? 'user') }}</small>
                            </div>
                            <span class="admin-pill">{{ number_format($user->elo_rating ?? 0) }} ELO</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Content</p>
                        <h2>Recent Posts</h2>
                    </div>
                    <a href="{{ route('admin.content') }}" class="feed-link">Moderate</a>
                </div>

                <div class="admin-row-list">
                    @forelse($recentPosts as $post)
                        <div class="admin-content-row">
                            <div>
                                <strong>{{ $post->user?->name ?? 'Unknown user' }}</strong>
                                <p>{{ \Illuminate\Support\Str::limit($post->content, 120) }}</p>
                            </div>
                            <span class="admin-pill">{{ $post->likes_count }} likes / {{ $post->comments_count }} comments</span>
                        </div>
                    @empty
                        <div class="empty-panel">
                            @include('partials.empty-illustration', ['title' => 'No posts yet', 'message' => 'Community posts will appear here.'])
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="admin-side-column">
            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Leaderboard</p>
                        <h2>Top Players</h2>
                    </div>
                </div>

                <div class="admin-row-list">
                    @foreach($topPlayers as $index => $player)
                        <div class="admin-match-row">
                            <strong>#{{ $index + 1 }} {{ $player->name }}</strong>
                            <span>{{ $player->rank }} - {{ number_format($player->elo_rating) }} ELO - {{ $player->getWinRate() }}% win rate</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Matches</p>
                        <h2>Live Queue</h2>
                    </div>
                    <a href="{{ route('matches.index') }}" class="feed-link">Open</a>
                </div>

                <div class="admin-row-list">
                    @forelse($upcomingMatches as $match)
                        <a href="{{ route('matches.show', $match->id) }}" class="admin-match-row">
                            <strong>{{ $match->player1?->name ?? 'Player 1' }} vs {{ $match->player2?->name ?? 'TBD' }}</strong>
                            <span>{{ ucfirst($match->status) }} - {{ $match->match_date ? $match->match_date->format('M d, H:i') : 'No date' }}</span>
                        </a>
                    @empty
                        <div class="empty-inline">No active matches right now.</div>
                    @endforelse
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Tournaments</p>
                        <h2>Recent Events</h2>
                    </div>
                    <a href="{{ route('tournaments.index') }}" class="feed-link">Browse</a>
                </div>

                <div class="admin-row-list">
                    @forelse($recentTournaments as $tournament)
                        <a href="{{ route('tournaments.show', $tournament->id) }}" class="admin-match-row">
                            <strong>{{ $tournament->name }}</strong>
                            <span>{{ ucfirst($tournament->status ?? 'upcoming') }} - by {{ $tournament->organizer?->name ?? 'Unknown' }}</span>
                        </a>
                    @empty
                        <div class="empty-inline">No tournaments yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="admin-panel">
                <div class="admin-panel-heading">
                    <div>
                        <p class="home-eyebrow">Challenges</p>
                        <h2>Needs Attention</h2>
                    </div>
                </div>

                <div class="admin-row-list">
                    @forelse($pendingChallenges as $challenge)
                        <div class="admin-match-row">
                            <strong>{{ $challenge->challenger?->name ?? 'Unknown' }} to {{ $challenge->opponent?->name ?? 'Open' }}</strong>
                            <span>{{ ucfirst($challenge->status) }} - {{ $challenge->created_at?->diffForHumans() }}</span>
                        </div>
                    @empty
                        <div class="empty-inline">No pending challenges.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
