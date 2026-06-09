@extends('layout')

@section('title', 'Schedule - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Schedule</h1>
            <p class="page-subtitle">{{ count($matches) }} matches on {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}</p>
        </div>
    </section>

    <section class="admin-schedule-layout">
        <aside class="admin-panel admin-calendar">
            <div class="admin-calendar-head">
                <span>‹</span>
                <strong>{{ $month->format('F Y') }}</strong>
                <span>›</span>
            </div>
            <div class="admin-calendar-grid admin-calendar-weekdays">
                @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
                    <span>{{ $day }}</span>
                @endforeach
            </div>
            <div class="admin-calendar-grid">
                @foreach($calendarDays as $day)
                    <a href="{{ route('admin.schedule', ['date' => $day->toDateString()]) }}" class="{{ $day->toDateString() === $selectedDate ? 'is-selected' : '' }} {{ $day->month !== $month->month ? 'is-muted' : '' }}">
                        {{ $day->day }}
                    </a>
                @endforeach
            </div>
        </aside>

        <main class="admin-panel admin-day-list">
            <div class="admin-panel-heading">
                <h2>{{ \Carbon\Carbon::parse($selectedDate)->format('D, F j') }} — {{ count($matches) }} matches</h2>
            </div>
            <div class="admin-row-list">
                @forelse($matches as $match)
                    <article class="admin-schedule-match">
                        <time>{{ $match['time'] }}</time>
                        <div>
                            <strong>{{ $match['players'] }}</strong>
                            <span>{{ $match['court'] }} · {{ $match['tournament'] }}</span>
                        </div>
                        <span>{{ $match['id'] }}</span>
                    </article>
                    @if(($match['raw_status'] ?? null) === 'disputed')
                        <form method="POST" action="{{ route('admin.matches.resolve-dispute', $match['database_id']) }}" class="admin-record-form">
                            @csrf
                            <p><strong>Dispute:</strong> {{ $match['dispute_reason'] }}</p>
                            <div class="admin-form-grid">
                                <input type="number" name="player1_score" min="0" placeholder="Player 1 score" required>
                                <input type="number" name="player2_score" min="0" placeholder="Player 2 score" required>
                                <select name="winner_id" required>
                                    <option value="">Winner</option>
                                    <option value="{{ $match['player1_id'] }}">Player 1</option>
                                    <option value="{{ $match['player2_id'] }}">Player 2</option>
                                </select>
                                <button class="btn btn-primary" type="submit" onclick="return confirm('Finalize this disputed result and settle bets?');">Resolve Dispute</button>
                            </div>
                        </form>
                    @endif
                @empty
                    <div class="empty-inline">No matches on this day.</div>
                @endforelse
            </div>
        </main>
    </section>
</div>
@endsection
