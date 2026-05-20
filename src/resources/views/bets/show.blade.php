@extends('layout')

@section('title', 'Bet Details - BadNet')

@section('content')
<div class="page-shell">
    <div class="post-card">
        <div class="post-header">
            <div class="author-info">
                <a href="#" class="author-name">Bet on: {{ $bet->betOnUser?->name ?? 'Player' }}</a>
                <span class="post-time">{{ $bet->created_at->diffForHumans() }}</span>
            </div>
        </div>

        <div class="post-content">
            <p>Match: {{ $bet->gameMatch?->player1?->name }} vs {{ $bet->gameMatch?->player2?->name }}</p>
            <p>Amount: {{ $bet->amount }} 🪙</p>
            <p>Status: {{ ucfirst($bet->status) }}</p>
            <p>Payout: {{ $bet->payout ?? 0 }} 🪙</p>
        </div>
    </div>
</div>
@endsection
