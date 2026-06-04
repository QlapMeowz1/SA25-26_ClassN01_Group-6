@extends('layout')

@section('title', 'Admin Console - BadNet')

@section('content')
@php
    $formatCount = fn ($count, $singular, $plural = null) => number_format($count) . ' ' . ($count == 1 ? $singular : ($plural ?? $singular . 's'));
    $formatStatus = fn ($status) => \Illuminate\Support\Str::headline((string) $status);
    $dashboardPosts = $recentPosts->filter(function ($post) {
        $content = trim((string) $post->content);
        return mb_strlen($content) >= 4 && !preg_match('/^\d+$/', $content);
    });
    $liveMatches = $upcomingMatches->where('status', 'in_progress')->count();
@endphp

<div class="page-shell admin-console-page">
    <section class="admin-hero">
        <div>
            <h1>Dashboard</h1>
            <p class="page-subtitle">{{ now()->format('l, F j, Y') }} — Week {{ now()->weekOfYear }}</p>
        </div>

        <a href="{{ route('admin.matches') }}" class="admin-live-pill">⌁ {{ $liveMatches }} live {{ \Illuminate\Support\Str::plural('match', $liveMatches) }}</a>
    </section>

    @include('admin.partials.nav')

    <section class="admin-stat-grid admin-dashboard-stats">
        <article class="admin-stat-card">
            <span>Total Players</span>
            <strong>{{ number_format($stats['users']) }}</strong>
            <small class="trend-up">↗ +{{ number_format($stats['new_users']) }} this week</small>
        </article>
        <article class="admin-stat-card">
            <span>Active Tournaments</span>
            <strong>{{ number_format($stats['active_tournaments']) }}</strong>
            <small class="trend-up">↗ {{ number_format($stats['tournaments']) }} total events</small>
        </article>
        <article class="admin-stat-card">
            <span>Matches This Week</span>
            <strong>{{ number_format($stats['matches']) }}</strong>
            <small class="trend-down">↘ {{ number_format($stats['open_matches']) }} open queues</small>
        </article>
        <article class="admin-stat-card">
            <span>Avg. Match Rating</span>
            <strong>{{ number_format(max(1, min(5, round(($stats['completed_matches'] / max(1, $stats['matches'])) * 5, 1))), 1) }}</strong>
            <small class="trend-up">↗ +{{ number_format($stats['new_posts']) }} feed signals</small>
        </article>
    </section>

    <section class="admin-chart-grid">
        <article class="admin-panel admin-chart-panel">
            <div class="admin-panel-heading">
                <h2>Match Activity — This Week</h2>
            </div>
            <div class="admin-line-chart" aria-hidden="true">
                <svg viewBox="0 0 700 170" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="adminChartFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#c6ff1a" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#c6ff1a" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path class="chart-fill" d="M0,120 C70,96 110,90 145,98 C210,113 215,138 280,126 C355,111 420,88 480,60 C550,28 595,16 700,48 L700,170 L0,170 Z"/>
                    <path class="chart-line" d="M0,120 C70,96 110,90 145,98 C210,113 215,138 280,126 C355,111 420,88 480,60 C550,28 595,16 700,48"/>
                </svg>
                <div class="chart-axis">
                    <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                </div>
            </div>
        </article>

        <article class="admin-panel admin-chart-panel">
            <div class="admin-panel-heading">
                <h2>Court Usage — Hours Today</h2>
            </div>
            <div class="admin-bar-chart" aria-hidden="true">
                @foreach([14, 11, 9, 13, 7, 10] as $hours)
                    <div class="admin-bar" style="--bar-height: {{ $hours * 8 }}px"><span></span></div>
                @endforeach
            </div>
            <div class="chart-axis">
                <span>Court 1</span><span>Court 2</span><span>Court 3</span><span>Court 4</span><span>Court 5</span><span>Court 6</span>
            </div>
        </article>
    </section>

    <section class="admin-panel admin-recent-matches-panel">
        <div class="admin-panel-heading">
            <h2>Recent Matches</h2>
            <a href="{{ route('admin.matches') }}" class="feed-link">View all →</a>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table admin-dashboard-table">
                <thead>
                    <tr>
                        <th>Match ID</th>
                        <th>Players</th>
                        <th>Tournament</th>
                        <th>Score</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($upcomingMatches as $match)
                        <tr>
                            <td>M-{{ str_pad($match->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td><strong>{{ $match->player1?->name ?? 'Player 1' }} vs {{ $match->player2?->name ?? 'TBD' }}</strong></td>
                            <td>{{ $match->location ?? 'Open Queue' }}</td>
                            <td>{{ $match->player1_score ?? '—' }} / {{ $match->player2_score ?? '—' }}</td>
                            <td><span class="admin-pill">{{ $formatStatus($match->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No active matches right now.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
                                <small>{{ $user->email }} - {{ \Illuminate\Support\Str::headline($user->role ?? 'user') }}</small>
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
                    @forelse($dashboardPosts as $post)
                        <div class="admin-content-row">
                            <div class="admin-post-copy">
                                <strong>{{ $post->user?->name ?? 'Unknown user' }}</strong>
                                <p>{{ \Illuminate\Support\Str::limit($post->content, 120) }}</p>
                            </div>
                            <span class="admin-pill admin-post-metrics">{{ $formatCount($post->likes_count, 'like') }} / {{ $formatCount($post->comments_count, 'comment') }}</span>
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
                            <span>{{ $formatStatus($match->status) }} - {{ $match->match_date ? $match->match_date->format('M d, H:i') : 'No date' }}</span>
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
                            <span>{{ $formatStatus($tournament->status ?? 'upcoming') }} - by {{ $tournament->organizer?->name ?? 'Unknown' }}</span>
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
                            <span>{{ $formatStatus($challenge->status) }} - {{ $challenge->created_at?->diffForHumans() }}</span>
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
