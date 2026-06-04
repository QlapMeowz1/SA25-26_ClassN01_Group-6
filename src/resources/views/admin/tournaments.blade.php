@extends('layout')

@section('title', 'Admin Tournaments - BadNet')

@section('content')
<div class="page-shell admin-console-page">
    <section class="admin-page-header">
        <div>
            <p class="home-eyebrow">Admin Console</p>
            <h1>Tournaments</h1>
            <p class="page-subtitle">Track event status, registration capacity, and organizer activity.</p>
        </div>
    </section>

    @include('admin.partials.nav')

    <section class="admin-panel">
        <form method="GET" action="{{ route('admin.tournaments') }}" class="admin-filter-bar">
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['upcoming', 'in_progress', 'completed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-small">Filter</button>
            <a href="{{ route('admin.tournaments') }}" class="btn btn-secondary btn-small">Reset</a>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tournament</th>
                        <th>Status</th>
                        <th>Organizer</th>
                        <th>Capacity</th>
                        <th>Prize</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tournaments as $tournament)
                        <tr>
                            <td>
                                <strong>{{ $tournament->name }}</strong>
                                <small>{{ $tournament->start_date ? $tournament->start_date->format('M d, Y') : 'No start date' }}</small>
                            </td>
                            <td><span class="admin-pill">{{ ucfirst(str_replace('_', ' ', $tournament->status ?? 'upcoming')) }}</span></td>
                            <td>{{ $tournament->organizer?->name ?? 'Unknown' }}</td>
                            <td>{{ $tournament->tournament_participants_count }}/{{ $tournament->max_participants }}</td>
                            <td>{{ number_format($tournament->prize_pool ?? 0) }}</td>
                            <td><a href="{{ route('tournaments.show', $tournament->id) }}" class="btn btn-secondary btn-small">Open</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $tournaments->links() }}
        </div>
    </section>
</div>
@endsection
