@extends('layout')

@section('title', 'Players - SMASH Admin')

@php
    $sortLink = function ($key) use ($sort, $dir) {
        return route('admin.players', ['sort' => $key, 'dir' => $sort === $key && $dir === 'asc' ? 'desc' : 'asc']);
    };
@endphp

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Players</h1>
            <p class="page-subtitle">{{ count($players) }} registered players</p>
        </div>
        <a href="{{ route('admin.players.create') }}" class="btn btn-primary">＋ Add Player</a>
    </section>

    <section class="admin-panel">
        <form method="GET" action="{{ route('admin.players') }}" class="admin-filter-bar">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search players, email, rank...">
            <button type="submit" class="btn btn-secondary btn-small">Filter</button>
            @if($search !== '')
                <a href="{{ route('admin.players') }}" class="btn btn-secondary btn-small">Reset</a>
            @endif
        </form>
    </section>

    <section class="admin-panel admin-table-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('name') }}">Player</a></th>
                        <th><a href="{{ $sortLink('category') }}">Category</a></th>
                        <th><a href="{{ $sortLink('club') }}">Club</a></th>
                        <th><a href="{{ $sortLink('rank') }}">Rank</a></th>
                        <th><a href="{{ $sortLink('wins') }}">W</a></th>
                        <th><a href="{{ $sortLink('losses') }}">L</a></th>
                        <th><a href="{{ $sortLink('rating') }}">Rating</a></th>
                        <th>Activity</th>
                        <th>Role</th>
                        <th><a href="{{ $sortLink('status') }}">Status</a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($players as $player)
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <span class="admin-avatar">{{ $player['initials'] }}</span>
                                    <div>
                                        <strong>{{ $player['name'] }}</strong>
                                        <small>{{ $player['email'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $player['category'] }}</td>
                            <td>{{ $player['club'] }}</td>
                            <td><strong class="admin-rank">#{{ $player['rank'] }}</strong></td>
                            <td class="admin-win">{{ $player['wins'] }}</td>
                            <td class="admin-loss">{{ $player['losses'] }}</td>
                            <td>{{ $player['rating'] }}</td>
                            <td>
                                <span class="admin-mini-stat">{{ $player['posts_count'] }} posts</span>
                                <span class="admin-mini-stat">{{ $player['bets_count'] }} bets</span>
                            </td>
                            <td>
                                @if($player['can_update_role'] ?? false)
                                    <form method="POST" action="{{ route('admin.players.role', $player['id']) }}" class="admin-inline-form admin-role-form">
                                        @csrf
                                        <select name="role" aria-label="Role for {{ $player['name'] }}">
                                            <option value="user" @selected(($player['role'] ?? 'user') === 'user')>User</option>
                                            <option value="admin" @selected(($player['role'] ?? 'user') === 'admin')>Admin</option>
                                        </select>
                                        <button type="submit" class="btn btn-secondary btn-small">Save</button>
                                    </form>
                                @else
                                    <span class="admin-pill">{{ \Illuminate\Support\Str::headline($player['role'] ?? 'admin') }}</span>
                                @endif
                            </td>
                            <td><span class="admin-pill admin-pill--{{ strtolower($player['status']) }}">{{ $player['status'] }}</span></td>
                            <td>
                                @if($player['can_manage'] ?? false)
                                    <div class="admin-row-actions">
                                        @if($player['is_banned'])
                                            <form method="POST" action="{{ route('admin.players.unban', $player['id']) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-secondary btn-small">Unban</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.players.ban', $player['id']) }}" onsubmit="return confirm('Ban this user?');">
                                                @csrf
                                                <input type="hidden" name="reason" value="Violated community rules">
                                                <button type="submit" class="btn btn-secondary btn-small">Ban</button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('admin.players.destroy', $player['id']) }}" onsubmit="return confirm('Delete this user and related data? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="admin-muted-note">Current admin</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-inline">No players found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
