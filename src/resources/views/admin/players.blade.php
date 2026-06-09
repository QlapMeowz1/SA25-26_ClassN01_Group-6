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
        <div class="admin-row-actions">
            <a href="{{ route('admin.players.export') }}" class="btn btn-secondary">Export CSV</a>
            <a href="{{ route('admin.players.create') }}" class="btn btn-primary">＋ Add Player</a>
        </div>
    </section>

    <section class="admin-panel">
        <form method="GET" action="{{ route('admin.players') }}" class="admin-filter-bar">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search players, email, rank...">
            <button type="submit" class="btn btn-secondary btn-small">Filter</button>
            @if($search !== '')
                <a href="{{ route('admin.players') }}" class="btn btn-secondary btn-small">Reset</a>
            @endif
        </form>
        <form method="POST" action="{{ route('admin.players.bulk') }}" id="bulk-player-form" class="admin-filter-bar" onsubmit="return confirm('Apply this action to selected users?');">
            @csrf
            <select name="action" required>
                <option value="">Bulk action</option>
                <option value="ban">Ban</option>
                <option value="unban">Unban</option>
                <option value="delete">Move to trash</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-small">Apply Selected</button>
        </form>
    </section>

    <section class="admin-panel admin-table-panel">
        <div class="admin-table-wrap">
            <table class="admin-table admin-players-table">
                <colgroup>
                    <col class="admin-player-col-select">
                    <col class="admin-player-col-identity">
                    <col class="admin-player-col-category">
                    <col class="admin-player-col-club">
                    <col class="admin-player-col-rank">
                    <col class="admin-player-col-number">
                    <col class="admin-player-col-number">
                    <col class="admin-player-col-rating">
                    <col class="admin-player-col-wallet">
                    <col class="admin-player-col-activity">
                    <col class="admin-player-col-role">
                    <col class="admin-player-col-status">
                    <col class="admin-player-col-actions">
                </colgroup>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th><a href="{{ $sortLink('name') }}">Player</a></th>
                        <th><a href="{{ $sortLink('category') }}">Category</a></th>
                        <th><a href="{{ $sortLink('club') }}">Club</a></th>
                        <th><a href="{{ $sortLink('rank') }}">Rank</a></th>
                        <th><a href="{{ $sortLink('wins') }}">W</a></th>
                        <th><a href="{{ $sortLink('losses') }}">L</a></th>
                        <th><a href="{{ $sortLink('rating') }}">Rating</a></th>
                        <th><a href="{{ $sortLink('wallet') }}">Wallet</a></th>
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
                                @if(!$player['deleted_at'] && ($player['can_manage'] ?? false))
                                    <input type="checkbox" name="user_ids[]" value="{{ $player['id'] }}" form="bulk-player-form" aria-label="Select {{ $player['name'] }}">
                                @endif
                            </td>
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
                                <div class="admin-wallet-cell">
                                    <strong>{{ number_format($player['wallet'] ?? 0) }} pts</strong>
                                    @if(!$player['deleted_at'])
                                    <form
                                        method="POST"
                                        action="{{ route('admin.players.wallet', $player['id']) }}"
                                        class="admin-wallet-form"
                                        onsubmit="return confirm('Update wallet for {{ addslashes($player['name']) }}?');"
                                    >
                                        @csrf
                                        <select name="operation" data-wallet-operation aria-label="Wallet operation for {{ $player['name'] }}">
                                            <option value="set">Set</option>
                                            <option value="add">Add</option>
                                            <option value="subtract">Subtract</option>
                                        </select>
                                        <input type="number" name="amount" min="0" max="1000000000" step="1" value="{{ $player['wallet'] ?? 0 }}" data-wallet-amount data-current-wallet="{{ $player['wallet'] ?? 0 }}" required aria-label="Wallet amount for {{ $player['name'] }}">
                                        <input type="text" name="reason" maxlength="255" placeholder="Reason (optional)" aria-label="Reason for wallet adjustment">
                                        <button type="submit" class="btn btn-secondary btn-small">Apply</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="admin-mini-stat">{{ $player['posts_count'] }} posts</span>
                                <span class="admin-mini-stat">{{ $player['bets_count'] }} bets</span>
                            </td>
                            <td>
                                @if(!$player['deleted_at'] && ($player['can_update_role'] ?? false))
                                    <form method="POST" action="{{ route('admin.players.role', $player['id']) }}" class="admin-inline-form admin-role-form">
                                        @csrf
                                        <select name="role" aria-label="Role for {{ $player['name'] }}">
                                            <option value="user" @selected(($player['role'] ?? 'user') === 'user')>User</option>
                                            <option value="moderator" @selected(($player['role'] ?? 'user') === 'moderator')>Moderator</option>
                                            <option value="betting_manager" @selected(($player['role'] ?? 'user') === 'betting_manager')>Betting Manager</option>
                                            <option value="admin" @selected(($player['role'] ?? 'user') === 'admin')>Admin</option>
                                            <option value="super_admin" @selected(($player['role'] ?? 'user') === 'super_admin')>Super Admin</option>
                                        </select>
                                        <button type="submit" class="btn btn-secondary btn-small">Save</button>
                                    </form>
                                @else
                                    <span class="admin-pill">{{ \Illuminate\Support\Str::headline($player['role'] ?? 'admin') }}</span>
                                @endif
                            </td>
                            <td><span class="admin-pill admin-pill--{{ strtolower($player['status']) }}">{{ $player['status'] }}</span></td>
                            <td>
                                @if($player['deleted_at'])
                                    <form method="POST" action="{{ route('admin.players.restore', $player['id']) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-small">Restore</button>
                                    </form>
                                @elseif($player['can_manage'] ?? false)
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

                                        <form method="POST" action="{{ route('admin.players.destroy', $player['id']) }}" onsubmit="return confirm('Move this user to trash?');">
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
                            <td colspan="13">
                                <div class="empty-inline">No players found.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.admin-wallet-form').forEach(function (form) {
        const operation = form.querySelector('[data-wallet-operation]');
        const amount = form.querySelector('[data-wallet-amount]');
        if (!operation || !amount) return;

        operation.addEventListener('change', function () {
            amount.value = operation.value === 'set'
                ? (amount.dataset.currentWallet || '0')
                : '0';
            amount.select();
        });
    });
});
</script>
@endpush
@endsection
