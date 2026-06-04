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
                @empty
                    <div class="empty-inline">No matches on this day.</div>
                @endforelse
            </div>
        </main>
    </section>
</div>
@endsection
