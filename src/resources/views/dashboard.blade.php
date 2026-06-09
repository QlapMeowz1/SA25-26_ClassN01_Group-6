@extends('layout')

@section('title', 'Dashboard - BadNet')

@php
    $user = auth()->user();
    $winRate = $user->getWinRate();
    $rank = $user->rank ?? 'Unranked';
    $postCount = $user->posts()->count();
    $newPostCount = $communityPosts->filter(fn ($post) => $post->created_at->greaterThan(now()->subHours(3)))->count();
    $statusLabel = fn ($status) => \Illuminate\Support\Str::headline((string) $status);
@endphp

@section('content')
<div class="page-shell pulse-dashboard">
    <section class="pulse-hero">
        <div>
            <p class="home-eyebrow">System Pulse</p>
            <h1>Welcome back, {{ $user->name }}</h1>
            <p class="page-subtitle">Court openings, match invites, and community updates in one live board.</p>
        </div>

        <div class="pulse-hero-actions">
            <a href="{{ route('matches.create') }}" class="btn btn-primary">Create Match / Find Court</a>
            <a href="{{ route('bets.index') }}" class="btn btn-secondary">Betting Desk</a>
            <a href="#composer" class="btn btn-secondary">Post Update</a>
        </div>
    </section>

    <div class="pulse-layout">
        <main class="pulse-main">
            <section class="pulse-panel">
                <div class="pulse-panel-heading">
                    <div>
                        <p class="home-eyebrow">Community Matches</p>
                        <h2>Upcoming and open courts</h2>
                    </div>
                    <a href="{{ route('matches.index') }}">Find match</a>
                </div>

                <div class="pulse-match-grid">
                    @forelse($openMatches->concat($upcomingMatches)->take(6) as $match)
                        <article class="pulse-match-card">
                            <div>
                                <span class="app-status app-status--{{ $match->status }}">{{ $statusLabel($match->status) }}</span>
                                <strong>{{ $match->player1?->name ?? 'Player 1' }} vs {{ $match->player2?->name ?? 'Open slot' }}</strong>
                                <small>{{ $match->location ?: 'Court TBD' }} · {{ $match->match_date ? $match->match_date->format('M d, H:i') : 'Time TBD' }}</small>
                            </div>
                            @if(is_numeric($match->id))
                                <a href="{{ route('matches.show', $match->id) }}">View</a>
                            @endif
                        </article>
                    @empty
                        <div class="profile-empty-state profile-empty-state-small">
                            <div class="profile-empty-icon">VS</div>
                            <h3>No active matches</h3>
                            <p>Create a court request to bring players in.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section id="composer" class="pulse-panel pulse-composer">
                <div class="pulse-panel-heading">
                    <div>
                        <p class="home-eyebrow">Community Feed</p>
                        <h2>Post a discussion or video</h2>
                    </div>
                </div>

                <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="pulse-compose-form">
                    @csrf
                    <textarea name="content" rows="4" maxlength="1000" placeholder="Share a result, challenge, highlight, or court update..."></textarea>
                    <div class="pulse-compose-actions">
                        <label>
                            <input type="file" id="post-images-input" name="images[]" accept="image/*" multiple hidden>
                            Image
                        </label>
                        <label>
                            <input type="file" id="post-videos-input" name="videos[]" accept="video/*" multiple hidden>
                            Video
                        </label>
                        <button type="submit" class="btn btn-primary">Post Update</button>
                    </div>
                    <div class="composer-media-preview" id="composer-media-preview" aria-live="polite"></div>
                </form>
            </section>

            <section class="pulse-panel">
                <div class="pulse-panel-heading">
                    <div>
                        <p class="home-eyebrow">Discussion</p>
                        <h2>Posts and badminton videos</h2>
                    </div>
                    @if($newPostCount > 0)
                        <span class="app-status app-status--open">{{ $newPostCount }} new</span>
                    @endif
                </div>

                @if($communityPosts->isEmpty())
                    <div class="profile-empty-state">
                        <div class="profile-empty-icon">POST</div>
                        <h3>No posts yet</h3>
                        <p>Be the first to share a match story or court update.</p>
                    </div>
                @else
                    <div class="post-list">
                        @foreach($communityPosts as $post)
                            @include('partials.post_card', ['post' => $post, 'showCommentPreview' => false])
                        @endforeach
                    </div>
                @endif
            </section>
        </main>

        <aside class="pulse-sidebar">
            <section class="pulse-panel pulse-player-card">
                <div class="pulse-player-row">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    <div>
                        <strong>{{ $user->name }}</strong>
                        <span>{{ $rank }} · {{ number_format($user->elo_rating ?? 0) }} ELO</span>
                    </div>
                </div>
                <div class="pulse-side-stats">
                    <div><span>Wallet</span><strong>{{ number_format($user->virtual_coins ?? 0) }}</strong></div>
                    <div><span>Win Rate</span><strong>{{ $winRate }}%</strong></div>
                    <div><span>Posts</span><strong>{{ $postCount }}</strong></div>
                    <div><span>Open</span><strong>{{ $openMatches->count() }}</strong></div>
                </div>
            </section>

            <section class="pulse-panel">
                <div class="pulse-panel-heading">
                    <div>
                        <p class="home-eyebrow">Gamification</p>
                        <h2>Prediction Masters</h2>
                    </div>
                </div>

                <div class="pulse-rank-list">
                    @foreach($predictionMasters as $index => $player)
                        <a href="{{ route('profile.show', $player->id) }}" class="pulse-rank-row">
                            <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <strong>{{ $player->name }}</strong>
                                <small>{{ (int) $player->prediction_wins }} wins / {{ (int) $player->prediction_count }} tickets</small>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="pulse-panel">
                <div class="pulse-panel-heading">
                    <div>
                        <p class="home-eyebrow">Top Players</p>
                        <h2>This week</h2>
                    </div>
                </div>

                <div class="pulse-rank-list">
                    @foreach($leaderboard->take(6) as $index => $player)
                        <a href="{{ route('profile.show', $player->id) }}" class="pulse-rank-row {{ $player->id === $user->id ? 'is-you' : '' }}">
                            <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <div>
                                <strong>{{ $player->name }}</strong>
                                <small>{{ number_format($player->elo_rating ?? 0) }} ELO · {{ $player->rank }}</small>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('post-images-input');
    const videoInput = document.getElementById('post-videos-input');
    const preview = document.getElementById('composer-media-preview');

    function renderPreview() {
        if (!preview) return;
        const files = [
            ...(imageInput ? Array.from(imageInput.files || []) : []),
            ...(videoInput ? Array.from(videoInput.files || []) : []),
        ];

        preview.innerHTML = files.map((file) => `<span>${file.name}</span>`).join('');
    }

    if (imageInput) imageInput.addEventListener('change', renderPreview);
    if (videoInput) videoInput.addEventListener('change', renderPreview);
});
</script>
@endpush
@endsection
