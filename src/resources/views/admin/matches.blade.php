@extends('layout')

@section('title', 'Admin Matches - BadNet')

@section('content')
<div class="page-shell admin-console-page">
    <section class="admin-page-header">
        <div>
            <p class="home-eyebrow">Admin Console</p>
            <h1>Matches</h1>
            <p class="page-subtitle">Monitor open queues, scheduled fixtures, results, and betting exposure.</p>
        </div>
    </section>

    @include('admin.partials.nav')

    <section class="admin-panel">
        <form method="GET" action="{{ route('admin.matches') }}" class="admin-filter-bar">
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['open', 'scheduled', 'in_progress', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-small">Filter</button>
            <a href="{{ route('admin.matches') }}" class="btn btn-secondary btn-small">Reset</a>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Result</th>
                        <th>Bets</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matches as $match)
                        <tr>
                            <td>
                                <strong>{{ $match->player1?->name ?? 'Player 1' }} vs {{ $match->player2?->name ?? 'TBD' }}</strong>
                                <small>{{ $match->location ?? 'Court TBD' }}</small>
                            </td>
                            <td><span class="admin-pill">{{ \Illuminate\Support\Str::headline($match->status) }}</span></td>
                            <td>{{ $match->match_date ? $match->match_date->format('M d, Y H:i') : 'No date' }}</td>
                            <td>{{ $match->winner?->name ?? 'N/A' }}<br><small>{{ $match->player1_score ?? '-' }} / {{ $match->player2_score ?? '-' }}</small></td>
                            <td>{{ $match->bets_count }}</td>
                            <td><a href="{{ route('matches.show', $match->id) }}" class="btn btn-secondary btn-small">Open</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $matches->links() }}
        </div>
    </section>
</div>
@endsection
