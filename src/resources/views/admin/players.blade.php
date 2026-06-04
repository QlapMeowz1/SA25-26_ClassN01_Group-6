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
            <input type="search" name="search" placeholder="Search players or clubs...">
            <button type="submit" class="btn btn-secondary btn-small">Filter</button>
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
                        <th><a href="{{ $sortLink('status') }}">Status</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($players as $player)
                        <tr>
                            <td>
                                <div class="admin-user-cell">
                                    <span class="admin-avatar">{{ $player['initials'] }}</span>
                                    <strong>{{ $player['name'] }}</strong>
                                </div>
                            </td>
                            <td>{{ $player['category'] }}</td>
                            <td>{{ $player['club'] }}</td>
                            <td><strong class="admin-rank">#{{ $player['rank'] }}</strong></td>
                            <td class="admin-win">{{ $player['wins'] }}</td>
                            <td class="admin-loss">{{ $player['losses'] }}</td>
                            <td>{{ $player['rating'] }}</td>
                            <td><span class="admin-pill admin-pill--{{ strtolower($player['status']) }}">{{ $player['status'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
