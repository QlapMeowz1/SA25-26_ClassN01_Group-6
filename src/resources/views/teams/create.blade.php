@extends('layout')

@section('title', 'Create Team - BadNet')

@section('content')
<div class="page-shell team-create-page">
    <section class="team-hero-panel">
        <div class="team-hero-copy">
            <p class="home-eyebrow">Team studio</p>
            <h1>Create New Team</h1>
            <p class="page-subtitle">Build a squad identity, add a logo, and invite players who fit your style.</p>
        </div>

        <div class="team-hero-actions">
            <a href="{{ route('teams.index') }}" class="btn btn-secondary">Back to teams</a>
        </div>
    </section>

    <div class="team-create-layout">
        <section class="team-form-panel">
            <form method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data" class="team-create-form">
                @csrf

                <div class="form-group">
                    <label for="name">Team Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="6" maxlength="1000" placeholder="Describe your team vibe, goals, and training cadence.">{{ old('description') }}</textarea>
                    @error('description') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="logo">Team Logo</label>
                    <input type="file" id="logo" name="logo" accept="image/*">
                    <p class="form-help">PNG or JPG works best for a crisp roster badge.</p>
                    @error('logo') <span class="error-text">{{ $message }}</span> @enderror
                </div>

                <div class="team-form-actions">
                    <button type="submit" class="btn btn-primary">Create Team</button>
                    <a href="{{ route('teams.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>

        <aside class="team-create-sidebar">
            <section class="team-section-block">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">Roster</p>
                        <h2>What Makes A Team Work</h2>
                    </div>
                </div>
                <div class="team-create-tips">
                    <div>
                        <strong>Clear identity</strong>
                        <p>Use the description to set expectations for level, cadence, and tone.</p>
                    </div>
                    <div>
                        <strong>Easy to scan</strong>
                        <p>A short name and a simple logo make the team easier to recognize.</p>
                    </div>
                    <div>
                        <strong>Leader first</strong>
                        <p>You will be added as the leader automatically after creation.</p>
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
