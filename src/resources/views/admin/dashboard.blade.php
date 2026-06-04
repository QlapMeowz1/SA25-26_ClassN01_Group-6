@extends('layout')

@section('title', 'Admin Dashboard - SMASH')

@php
    $matchActivity = [
        ['day' => 'Mon', 'x' => 0, 'y' => 70, 'value' => 22],
        ['day' => 'Tue', 'x' => 20, 'y' => 58, 'value' => 31],
        ['day' => 'Wed', 'x' => 40, 'y' => 75, 'value' => 18],
        ['day' => 'Thu', 'x' => 57, 'y' => 62, 'value' => 28],
        ['day' => 'Fri', 'x' => 74, 'y' => 44, 'value' => 42],
        ['day' => 'Sat', 'x' => 86, 'y' => 23, 'value' => 60],
        ['day' => 'Sun', 'x' => 100, 'y' => 35, 'value' => 49],
    ];
    $courtUsage = [
        ['court' => 'Court 1', 'hours' => 14],
        ['court' => 'Court 2', 'hours' => 11],
        ['court' => 'Court 3', 'hours' => 9],
        ['court' => 'Court 4', 'hours' => 13],
        ['court' => 'Court 5', 'hours' => 7],
        ['court' => 'Court 6', 'hours' => 10],
    ];
@endphp

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
            <div
                class="admin-line-chart"
                aria-label="Match activity this week"
                data-admin-cursor-chart="{{ collect($matchActivity)->map(fn ($point) => $point['day'] . ': ' . $point['value'] . ' matches')->implode('|') }}"
            >
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
                @foreach($matchActivity as $point)
                    <span
                        class="admin-chart-point admin-chart-point--lime"
                        style="--x: {{ $point['x'] }}%; --y: {{ $point['y'] }}%;"
                        aria-hidden="true"
                    ></span>
                @endforeach
                <div class="admin-chart-hover-zones">
                    @foreach($matchActivity as $point)
                        <span
                            tabindex="0"
                            data-tooltip="{{ $point['day'] }}: {{ $point['value'] }} matches"
                            title="{{ $point['day'] }}: {{ $point['value'] }} matches"
                        ></span>
                    @endforeach
                </div>
                <div class="chart-axis"><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span></div>
            </div>
        </article>

        <article class="admin-panel admin-chart-panel">
            <div class="admin-panel-heading">
                <h2>Court Usage — Hours Today</h2>
            </div>
            <div
                class="admin-bar-chart"
                aria-label="Court usage hours today"
                data-admin-cursor-chart="{{ collect($courtUsage)->map(fn ($court) => $court['court'] . ': ' . $court['hours'] . ' hours')->implode('|') }}"
            >
                @foreach($courtUsage as $court)
                    <div
                        class="admin-bar"
                        tabindex="0"
                        style="--bar-height: {{ $court['hours'] * 8 }}px"
                        data-tooltip="{{ $court['court'] }}: {{ $court['hours'] }} hours"
                        title="{{ $court['court'] }}: {{ $court['hours'] }} hours"
                    >
                        <span></span>
                    </div>
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
