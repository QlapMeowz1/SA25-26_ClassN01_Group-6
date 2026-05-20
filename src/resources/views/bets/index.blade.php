@extends('layout')

@section('title', 'Your Bets - BadNet')

@section('content')
<div class="page-shell">
    <div class="feed-heading">
        <div>
            <p class="home-eyebrow">Bets</p>
            <h2>Your Bet History</h2>
        </div>
    </div>

    <div class="posts-feed">
        @forelse($history as $bet)
            <div class="post-card">
                <div class="post-header">
                    <div class="author-info">
                        <a href="#" class="author-name">Bet on: {{ $bet->betOnUser?->name ?? 'Player' }}</a>
                        <span class="post-time">{{ $bet->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="post-content">
                    Amount: {{ $bet->amount }} 🪙 — Status: {{ ucfirst($bet->status) }}
                </div>

                <div class="post-actions">
                    <a href="{{ route('bets.show', $bet->id) }}" class="btn btn-secondary">Details</a>
                </div>
            </div>
        @empty
            <div class="post-card feed-empty">
                <p>No bets yet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
