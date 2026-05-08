@extends('layout')

@section('title', 'Teams - BadNet')

@section('content')
<div class="page-shell team-locker-shell">
    <div class="teams-header">
        <div>
            <p class="home-eyebrow">Team Locker Room</p>
            <h1>Find Your Squad</h1>
            <p class="page-subtitle">Browse teams, join the action, or create your own roster.</p>
        </div>
        <a href="{{ route('teams.create') }}" class="btn btn-primary">Create Team</a>
    </div>

    <section class="team-section-block" id="my-teams">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">My Teams</p>
                <h2>Active Squads</h2>
            </div>
        </div>
        @if($myTeams->isEmpty())
            <div class="empty-panel team-empty-panel">
                @include('partials.empty-illustration', ['title' => "You're not in any team yet", 'message' => 'Create one or browse teams to join!'])
                <div class="empty-panel-actions">
                    <a href="{{ route('teams.create') }}" class="btn btn-primary">Create Team</a>
                    <a href="#all-teams" class="btn btn-secondary">Browse Teams</a>
                </div>
            </div>
        @else
            <div class="team-grid">
                @foreach($myTeams as $team)
                    <article class="team-card">
                        <div class="team-card-banner">
                            @if($team->logo)
                                <img src="{{ asset('logos/' . $team->logo) }}" alt="{{ $team->name }}" class="team-banner-image">
                            @else
                                <div class="team-banner-placeholder">
                                    <span>{{ strtoupper(substr($team->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="team-card-content">
                            <div class="team-card-header">
                                <h3>{{ $team->name }}</h3>
                                <span class="team-badge" data-level="{{ strtolower($team->level ?? 'beginner') }}">
                                    {{ $team->level ?? 'N/A' }}
                                </span>
                            </div>
                            
                            <p class="team-slogan">{{ $team->slogan ?? 'A team with a shared passion' }}</p>
                            <p class="team-description">{{ \Illuminate\Support\Str::limit($team->description, 80) }}</p>
                            
                            <div class="team-meta-grid">
                                <div class="team-meta-item">
                                    <span class="meta-label">Members</span>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill" style="width: {{ min(100, ($team->members_count ?? 0) * 5) }}%"></div>
                                    </div>
                                    <span class="meta-value">{{ $team->members_count ?? 0 }}/{{ $team->max_members ?? 20 }}</span>
                                </div>
                                <div class="team-meta-item">
                                    <span class="meta-label">Location</span>
                                    <span class="meta-value">{{ $team->location ?? 'TBD' }}</span>
                                </div>
                            </div>

                            @if($team->tags)
                                <div class="team-tags">
                                    @foreach(json_decode($team->tags ?? '[]') as $tag)
                                        <span class="tag">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ route('teams.show', $team->id) }}" class="btn btn-primary btn-block">View Team</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="team-section-block" id="suggested-teams">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">For You</p>
                <h2>Suggested Teams</h2>
            </div>
        </div>
        @if($suggestedTeams->isEmpty())
            <div class="empty-panel team-empty-panel">
                @include('partials.empty-illustration', ['title' => 'No suggestions right now', 'message' => 'We will recommend teams based on your activity.'])
            </div>
        @else
            <div class="team-grid">
                @foreach($suggestedTeams as $team)
                    <article class="team-card">
                        <div class="team-card-banner">
                            @if(isset($team->logo) && $team->logo)
                                <img src="{{ asset('logos/' . $team->logo) }}" alt="{{ $team->name }}" class="team-banner-image">
                            @else
                                <div class="team-banner-placeholder">
                                    <span>{{ strtoupper(substr($team->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="team-card-content">
                            <div class="team-card-header">
                                <h3>{{ $team->name }}</h3>
                                <span class="team-badge" data-level="{{ strtolower($team->level) }}">
                                    {{ $team->level }}
                                </span>
                            </div>
                            
                            <p class="team-slogan">{{ $team->slogan }}</p>
                            <p class="team-description">{{ \Illuminate\Support\Str::limit($team->description, 80) }}</p>
                            
                            <div class="team-meta-grid">
                                <div class="team-meta-item">
                                    <span class="meta-label">Members</span>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill" style="width: {{ min(100, ($team->members_count ?? 0) * 5) }}%"></div>
                                    </div>
                                    <span class="meta-value">{{ $team->members_count }}/{{ $team->max_members }}</span>
                                </div>
                                <div class="team-meta-item">
                                    <span class="meta-label">Location</span>
                                    <span class="meta-value">{{ $team->location }}</span>
                                </div>
                            </div>

                            <div class="team-tags">
                                @foreach($team->tags as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                @endforeach
                            </div>

                            @if($team->is_sample ?? false)
                                <button type="button" class="btn btn-primary btn-block" onclick="alert('Sample team - Click View Team to learn more')">View Team</button>
                            @else
                                <a href="{{ route('teams.show', $team->id) }}" class="btn btn-primary btn-block">View Team</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="team-section-block" id="all-teams">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">All Teams</p>
                <h2>Find a Roster</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('teams.index') }}" class="team-search-bar">
            <div class="search-input-wrapper">
                <input type="text" name="search" value="{{ $search }}" placeholder="🔍 Search teams by name...">
            </div>
            <div class="team-filter-controls">
                <select name="level">
                    <option value="">All Levels</option>
                    @foreach(['Beginner', 'Intermediate', 'Advanced', 'Professional'] as $level)
                        <option value="{{ $level }}" @selected($levelFilter === $level)>{{ $level }}</option>
                    @endforeach
                </select>
                <select name="location">
                    <option value="">All Locations</option>
                    @foreach(['Saigon', 'Hanoi', 'Da Nang'] as $city)
                        <option value="{{ $city }}" @selected($locationFilter === $city)>{{ $city }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-small">Search</button>
                <a href="{{ route('teams.index') }}" class="btn btn-secondary btn-small">Reset</a>
            </div>
        </form>

        @if($allTeams->isEmpty())
            <div class="empty-panel team-empty-panel">
                @include('partials.empty-illustration', ['title' => 'No teams match your search', 'message' => 'Try adjusting your filters or create a new team.'])
                <a href="{{ route('teams.create') }}" class="btn btn-primary">Create Team</a>
            </div>
        @else
            <div class="team-grid">
                @foreach($allTeams as $team)
                    <article class="team-card">
                        <div class="team-card-banner">
                            @if($team->logo)
                                <img src="{{ asset('logos/' . $team->logo) }}" alt="{{ $team->name }}" class="team-banner-image">
                            @else
                                <div class="team-banner-placeholder">
                                    <span>{{ strtoupper(substr($team->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="team-card-content">
                            <div class="team-card-header">
                                <h3>{{ $team->name }}</h3>
                                <span class="team-badge" data-level="{{ strtolower($team->level ?? 'beginner') }}">
                                    {{ $team->level ?? 'N/A' }}
                                </span>
                            </div>
                            
                            <p class="team-slogan">{{ $team->slogan ?? 'A competitive team' }}</p>
                            <p class="team-description">{{ \Illuminate\Support\Str::limit($team->description, 80) }}</p>
                            
                            <div class="team-meta-grid">
                                <div class="team-meta-item">
                                    <span class="meta-label">Members</span>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill" style="width: {{ min(100, ($team->members_count ?? 0) * 5) }}%"></div>
                                    </div>
                                    <span class="meta-value">{{ $team->members_count ?? 0 }}/{{ $team->max_members ?? 20 }}</span>
                                </div>
                                <div class="team-meta-item">
                                    <span class="meta-label">Location</span>
                                    <span class="meta-value">{{ $team->location ?? 'TBD' }}</span>
                                </div>
                            </div>

                            @if($team->tags)
                                <div class="team-tags">
                                    @foreach(json_decode($team->tags ?? '[]') as $tag)
                                        <span class="tag">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ route('teams.show', $team->id) }}" class="btn btn-secondary btn-block">View Team</a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($allTeams->hasPages())
                <div class="pagination-wrapper">
                    {{ $allTeams->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
<script>
    (function(){
        const form = document.querySelector('.team-search-bar');
        if (!form) return;

        const searchInput = form.querySelector('input[name="search"]');
        const levelSelect = form.querySelector('select[name="level"]');
        const locationInput = form.querySelector('[name="location"]');

        function debounce(fn, wait) {
            let t;
            return function(...args){ clearTimeout(t); t = setTimeout(()=> fn.apply(this,args), wait); };
        }

        async function doSearch(){
            const params = new URLSearchParams();
            if (searchInput && searchInput.value) params.set('search', searchInput.value);
            if (levelSelect && levelSelect.value) params.set('level', levelSelect.value);
            if (locationInput && locationInput.value) params.set('location', locationInput.value);

            const url = window.location.pathname + '?' + params.toString();
            try {
                const res = await fetch(url, { credentials: 'same-origin' });
                const text = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(text, 'text/html');
                const newSection = doc.getElementById('all-teams');
                const current = document.getElementById('all-teams');
                if (newSection && current) current.innerHTML = newSection.innerHTML;
            } catch (e) {
                console.error('Team search failed', e);
            }
        }

        const debounced = debounce(doSearch, 300);

        if (searchInput) searchInput.addEventListener('input', debounced);
        if (levelSelect) levelSelect.addEventListener('change', debounced);
        if (locationInput) locationInput.addEventListener('change', debounced);
    })();
</script>
@endsection
