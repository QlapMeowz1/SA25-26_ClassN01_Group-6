@extends('layout')

@section('title', 'Admin Dashboard - SMASH')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-hero">
        <div>
            <h1>Dashboard</h1>
            <p class="page-subtitle">Thursday, June 4, 2026 — Week 23</p>
        </div>
        <span class="admin-live-pill">⌁ {{ $liveMatches }} live {{ \Illuminate\Support\Str::plural('match', $liveMatches) }}</span>
    </section>

    <section class="admin-stat-grid admin-dashboard-stats">
        @foreach($stats as $stat)
            <article class="admin-stat-card">
                <span>{{ $stat['label'] }}</span>
                <strong>{{ $stat['value'] }}</strong>
                <small class="{{ $stat['trend'] === 'up' ? 'trend-up' : 'trend-down' }}">
                    {{ $stat['trend'] === 'up' ? '↗' : '↘' }} {{ $stat['change'] }} this week
                </small>
            </article>
        @endforeach
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
                            <stop offset="0%" stop-color="#c8f53a" stop-opacity="0.22"/>
                            <stop offset="100%" stop-color="#c8f53a" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <path class="chart-fill" d="M0,120 C70,96 110,90 145,98 C210,113 215,138 280,126 C355,111 420,88 480,60 C550,28 595,16 700,48 L700,170 L0,170 Z"/>
                    <path class="chart-line" d="M0,120 C70,96 110,90 145,98 C210,113 215,138 280,126 C355,111 420,88 480,60 C550,28 595,16 700,48"/>
                </svg>
                <div class="chart-axis"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
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
            <div class="chart-axis"><span>Court 1</span><span>Court 2</span><span>Court 3</span><span>Court 4</span><span>Court 5</span><span>Court 6</span></div>
        </article>
    </section>

    <section class="admin-panel admin-recent-matches-panel">
        <div class="admin-panel-heading">
            <h2>Recent Matches</h2>
            <a href="{{ route('admin.schedule') }}" class="feed-link">View all →</a>
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
                    @foreach($recentMatches as $match)
                        <tr>
                            <td>{{ $match['id'] }}</td>
                            <td><strong>{{ $match['players'] }}</strong></td>
                            <td>{{ $match['tournament'] }}</td>
                            <td>{{ $match['score'] }}</td>
                            <td><span class="admin-pill admin-pill--{{ strtolower($match['status']) }}">{{ $match['status'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
