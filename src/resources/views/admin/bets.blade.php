@extends('layout')

@section('title', 'Admin Bets - BadNet')

@section('content')
<div class="page-shell admin-console-page">
    <section class="admin-page-header">
        <div>
            <p class="home-eyebrow">Admin Console</p>
            <h1>Betting Audit</h1>
            <p class="page-subtitle">Review ticket volume, pending exposure, and payout outcomes.</p>
        </div>
    </section>

    @include('admin.partials.nav')

    <section class="admin-stat-grid admin-stat-grid-compact">
        <article class="admin-stat-card"><span>Total</span><strong>{{ number_format($betStats['total']) }}</strong><small>Tickets</small></article>
        <article class="admin-stat-card"><span>Pending</span><strong>{{ number_format($betStats['pending']) }}</strong><small>Open tickets</small></article>
        <article class="admin-stat-card"><span>Won</span><strong>{{ number_format($betStats['won']) }}</strong><small>Settled wins</small></article>
        <article class="admin-stat-card"><span>Lost</span><strong>{{ number_format($betStats['lost']) }}</strong><small>Settled losses</small></article>
        <article class="admin-stat-card"><span>Volume</span><strong>{{ number_format($betStats['volume']) }}</strong><small>Coins wagered</small></article>
        <article class="admin-stat-card"><span>Payout</span><strong>{{ number_format($betStats['payout']) }}</strong><small>Coins paid</small></article>
    </section>

    <section class="admin-panel">
        <form method="GET" action="{{ route('admin.bets') }}" class="admin-filter-bar">
            <select name="status">
                <option value="">All statuses</option>
                @foreach(['pending', 'won', 'lost'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-small">Filter</button>
            <a href="{{ route('admin.bets') }}" class="btn btn-secondary btn-small">Reset</a>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Match</th>
                        <th>Pick</th>
                        <th>Stake</th>
                        <th>Status</th>
                        <th>Payout</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bets as $bet)
                        <tr>
                            <td>
                                <strong>#{{ str_pad($bet->id, 5, '0', STR_PAD_LEFT) }}</strong>
                                <small>{{ $bet->user?->name ?? 'Unknown' }}</small>
                            </td>
                            <td>{{ $bet->gameMatch?->player1?->name ?? 'Player 1' }} vs {{ $bet->gameMatch?->player2?->name ?? 'TBD' }}</td>
                            <td>{{ $bet->betOnUser?->name ?? 'Unknown' }}</td>
                            <td>{{ number_format($bet->amount) }}</td>
                            <td><span class="admin-pill">{{ \Illuminate\Support\Str::headline($bet->status) }}</span></td>
                            <td>{{ number_format($bet->payout ?? 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $bets->links() }}
        </div>
    </section>
</div>
@endsection
