@extends('layout')

@section('title', 'Tournaments - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Tournaments</h1>
            <p class="page-subtitle">2 ongoing · 2 upcoming · 1 completed</p>
        </div>
        <a href="{{ route('admin.tournaments.create') }}" class="btn btn-primary">＋ New Tournament</a>
    </section>

    <section class="admin-tournament-list">
        @foreach($tournaments as $tournament)
            <article class="admin-tournament-card" id="{{ $tournament['id'] }}">
                <div class="admin-tournament-main">
                    <span class="admin-trophy">♕</span>
                    <div>
                        <h2>{{ $tournament['name'] }} <span class="admin-pill">{{ $tournament['tag'] }}</span></h2>
                        <p>{{ $tournament['venue'] }} · {{ $tournament['dates'] }}</p>
                    </div>
                </div>
                <div class="admin-tournament-meta">
                    <div><span>Players</span><strong>{{ $tournament['players'] }}</strong></div>
                    <div><span>Prize Pool</span><strong>{{ $tournament['prize'] }}</strong></div>
                    <span class="admin-pill admin-pill--{{ strtolower($tournament['status']) }}">{{ $tournament['status'] }}</span>
                    <a href="#{{ $tournament['id'] }}" aria-label="Expand {{ $tournament['name'] }}">›</a>
                </div>
                <div class="admin-progress-row">
                    <span>Bracket Progress</span>
                    <div class="admin-progress"><div style="width: {{ $tournament['progress'] }}%"></div></div>
                    <small>{{ $tournament['progress'] }}%</small>
                </div>
                <div class="admin-tournament-details">
                    <p>Registration, bracket seeding, prize pool, and match operations are monitored from this event card.</p>
                </div>
            </article>
        @endforeach
    </section>
</div>
@endsection
