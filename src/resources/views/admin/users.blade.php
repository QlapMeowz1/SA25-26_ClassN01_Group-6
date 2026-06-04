@extends('layout')

@section('title', 'Admin Users - BadNet')

@section('content')
<div class="page-shell admin-console-page">
    <section class="admin-page-header">
        <div>
            <p class="home-eyebrow">Admin Console</p>
            <h1>Users</h1>
            <p class="page-subtitle">Search accounts, inspect activity, and control admin access.</p>
        </div>
    </section>

    @include('admin.partials.nav')

    <section class="admin-panel">
        <form method="GET" action="{{ route('admin.users') }}" class="admin-filter-bar">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email">
            <select name="role">
                <option value="">All roles</option>
                <option value="user" @selected(request('role') === 'user')>Users</option>
                <option value="admin" @selected(request('role') === 'admin')>Admins</option>
            </select>
            <button type="submit" class="btn btn-primary btn-small">Filter</button>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary btn-small">Reset</a>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Performance</th>
                        <th>Activity</th>
                        <th>Access</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <span class="admin-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        <small>{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="admin-pill">{{ ucfirst($user->role ?? 'user') }}</span></td>
                            <td>{{ number_format($user->elo_rating ?? 0) }} ELO<br><small>{{ $user->wins }}W / {{ $user->losses }}L</small></td>
                            <td>{{ $user->posts_count }} posts<br><small>{{ $user->bets_count }} bets / {{ $user->team_members_count }} teams</small></td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.role', $user->id) }}" class="admin-inline-form">
                                    @csrf
                                    <select name="role" @disabled($user->id === auth()->id())>
                                        <option value="user" @selected($user->role === 'user')>User</option>
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-secondary btn-small" @disabled($user->id === auth()->id())>Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $users->links() }}
        </div>
    </section>
</div>
@endsection
