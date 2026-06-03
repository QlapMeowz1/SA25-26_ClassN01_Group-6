@extends('layout')

@section('title', 'Social - BadNet')

@section('content')
<div class="page-shell">
    <div class="posts-header">
        <div>
            <p class="home-eyebrow">{{ __('ui.home.community') }}</p>
            <h1>{{ __('ui.nav.home') }}</h1>
            <p class="page-subtitle">{{ __('ui.post.archive_intro') }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">{{ __('ui.home.dashboard_feed') }}</a>
    </div>

    <div class="dashboard-section">
        @auth
            <section class="post-creator">
                <div class="creator-header">
                    <div class="creator-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <form action="{{ route('posts.store') }}" method="POST" class="creator-form">
                        @csrf
                        <textarea name="content" placeholder="{{ __('ui.post.share_placeholder') }}" maxlength="500" required></textarea>
                        <button type="submit" class="btn btn-primary">{{ __('ui.post.publish') }}</button>
                    </form>
                </div>
            </section>
        @endauth

        <section class="posts-feed">
        @if($posts->isEmpty())
            <div class="empty-panel">
                <p class="empty-message">{{ __('ui.post.no_posts') }}</p>
            </div>
        @else
            @foreach($posts as $post)
                @include('partials.post_card', ['post' => $post])
            @endforeach
        @endif
        </section>

        @if($posts->hasPages())
            <div>
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
