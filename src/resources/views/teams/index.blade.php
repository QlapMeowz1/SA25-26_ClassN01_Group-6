@extends('layout')

@section('title', 'Teams - BadNet')

@section('content')
<div class="page-shell team-locker-shell">
    <div class="teams-header">
        <div>
            <p class="home-eyebrow">{{ __('ui.team.my_teams') }}</p>
            <h1>{{ __('ui.team.title') }}</h1>
            <p class="page-subtitle">{{ __('ui.team.subtitle') }}</p>
        </div>
        <a href="{{ route('teams.create') }}" class="btn btn-primary">{{ __('ui.team.create') }}</a>
    </div>

    <section class="team-section-block" id="my-teams">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">{{ __('ui.team.my_teams') }}</p>
                <h2>{{ __('ui.team.active') }}</h2>
            </div>
        </div>
        @if($myTeams->isEmpty())
            <div class="empty-panel team-empty-panel">
                @include('partials.empty-illustration', ['title' => __('ui.team.no_team_yet'), 'message' => __('ui.team.create_or_browse')])
                <div class="empty-panel-actions">
                    <a href="{{ route('teams.create') }}" class="btn btn-primary">{{ __('ui.team.create') }}</a>
                    <a href="#all-teams" class="btn btn-secondary">{{ __('ui.team.browse') }}</a>
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
                                    <span class="meta-label">{{ __('ui.team.members') }}</span>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill" style="width: {{ min(100, ($team->members_count ?? 0) * 5) }}%"></div>
                                    </div>
                                    <span class="meta-value">{{ $team->members_count ?? 0 }}/{{ $team->max_members ?? 20 }}</span>
                                </div>
                                <div class="team-meta-item">
                                    <span class="meta-label">{{ __('ui.team.location') }}</span>
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

                            <a href="{{ route('teams.show', $team->id) }}" class="btn btn-primary btn-block">{{ __('ui.team.view_team') }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section class="team-section-block" id="suggested-teams">
        <div class="feed-heading">
            <div>
                <p class="home-eyebrow">{{ __('ui.team.for_you') }}</p>
                <h2>{{ __('ui.team.suggested') }}</h2>
            </div>
        </div>
        @if($suggestedTeams->isEmpty())
            <div class="empty-panel team-empty-panel">
                @include('partials.empty-illustration', ['title' => __('ui.team.no_suggestions'), 'message' => __('ui.team.recommendation_note')])
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
                                    <span class="meta-label">{{ __('ui.team.members') }}</span>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill" style="width: {{ min(100, ($team->members_count ?? 0) * 5) }}%"></div>
                                    </div>
                                    <span class="meta-value">{{ $team->members_count }}/{{ $team->max_members }}</span>
                                </div>
                                <div class="team-meta-item">
                                    <span class="meta-label">{{ __('ui.team.location') }}</span>
                                    <span class="meta-value">{{ $team->location }}</span>
                                </div>
                            </div>

                            <div class="team-tags">
                                @foreach($team->tags as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                @endforeach
                            </div>

                            @if($team->is_sample ?? false)
                                <button type="button" class="btn btn-primary btn-block sample-team-btn" data-team-id="{{ $team->id }}" data-team-name="{{ $team->name }}" data-team-level="{{ $team->level }}" data-team-location="{{ $team->location }}" data-team-members="{{ $team->members_count }}" data-team-max="{{ $team->max_members }}" data-team-slogan="{{ $team->slogan }}" data-team-description="{{ $team->description }}">{{ __('ui.team.view_team') }}</button>
                            @else
                                <a href="{{ route('teams.show', $team->id) }}" class="btn btn-primary btn-block">{{ __('ui.team.view_team') }}</a>
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
                <p class="home-eyebrow">{{ __('ui.team.all_teams') }}</p>
                <h2>{{ __('ui.team.find_roster') }}</h2>
            </div>
        </div>

        <form method="GET" action="{{ route('teams.index') }}" class="team-search-bar">
            <div class="search-input-wrapper">
                <input type="text" name="search" value="{{ $search }}" placeholder="🔍 {{ __('ui.team.search_placeholder') }}">
            </div>
            <div class="team-filter-controls">
                <select name="level">
                    <option value="">{{ __('ui.team.all_levels') }}</option>
                    @foreach(['Beginner' => __('ui.match.beginner'), 'Intermediate' => __('ui.match.intermediate'), 'Advanced' => __('ui.match.advanced'), 'Professional' => __('ui.match.professional')] as $levelValue => $levelLabel)
                        <option value="{{ $levelValue }}" @selected($levelFilter === $levelValue)>{{ $levelLabel }}</option>
                    @endforeach
                </select>
                <select name="location">
                    <option value="">{{ __('ui.team.all_locations') }}</option>
                    @foreach(['Saigon', 'Hanoi', 'Da Nang'] as $city)
                        <option value="{{ $city }}" @selected($locationFilter === $city)>{{ $city }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-small">{{ __('ui.team.search') }}</button>
                <a href="{{ route('teams.index') }}" class="btn btn-secondary btn-small">{{ __('ui.team.reset') }}</a>
            </div>
        </form>

        @if($allTeams->isEmpty())
            <div class="empty-panel team-empty-panel">
                @include('partials.empty-illustration', ['title' => __('ui.team.no_match'), 'message' => __('ui.team.adjust_filters')])
                <a href="{{ route('teams.create') }}" class="btn btn-primary">{{ __('ui.team.create') }}</a>
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

                            <a href="{{ route('teams.show', $team->id) }}" class="btn btn-secondary btn-block">{{ __('ui.team.view_team') }}</a>
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

<!-- Sample Team Modal -->
<div id="sampleTeamModal" class="modal-overlay" style="display: none;">
    <div class="modal-content sample-team-modal">
        <div class="modal-header">
            <h2>{{ __('ui.team.learn_more') }}</h2>
            <button type="button" class="modal-close" id="closeModal">&times;</button>
        </div>
        
        <div class="modal-body">
            <div class="sample-team-banner">
                <div class="team-banner-placeholder-lg">
                    <span id="modalTeamInitial">S</span>
                </div>
            </div>
            
            <div class="modal-team-info">
                <div class="modal-team-header">
                    <h1 id="modalTeamName">{{ __('ui.team.team_name') }}</h1>
                    <span class="team-badge" id="modalTeamLevel">{{ __('ui.team.level') }}</span>
                </div>
                
                <p class="modal-team-slogan" id="modalTeamSlogan">{{ __('ui.team.slogan') }}</p>
                <p class="modal-team-description" id="modalTeamDescription">{{ __('ui.team.description') }}</p>
                
                <div class="modal-team-meta">
                    <div class="meta-item">
                        <span class="meta-label">{{ __('ui.team.members') }}</span>
                        <span class="meta-value" id="modalTeamMembers">0/0</span>
                        <div class="progress-bar-small">
                            <div class="progress-fill" id="modalTeamProgress" style="width: 0%"></div>
                        </div>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">{{ __('ui.team.location') }}</span>
                        <span class="meta-value" id="modalTeamLocation">{{ __('ui.team.location') }}</span>
                    </div>
                </div>
                
                <div class="sample-team-notice">
                    <p class="notice-icon">ℹ️</p>
                    <p class="notice-text">{{ __('ui.team.sample_notice') }}</p>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="closeModalBtn">{{ __('ui.team.close') }}</button>
            <button type="button" class="btn btn-primary">{{ __('ui.team.join_team') }}</button>
        </div>
    </div>
</div>

<script>
    (function(){
        // Team Search Functionality
        const form = document.querySelector('.team-search-bar');
        if (form) {
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
        }

        // Sample Team Modal Functionality
        const modal = document.getElementById('sampleTeamModal');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        
        // Close modal
        function closeSampleTeamModal() {
            if (modal) modal.style.display = 'none';
        }
        
        // Open modal with team data
        function openSampleTeamModal(teamData) {
            if (!modal) return;
            
            document.getElementById('modalTeamInitial').textContent = teamData.name.charAt(0).toUpperCase();
            document.getElementById('modalTeamName').textContent = teamData.name;
            document.getElementById('modalTeamLevel').textContent = teamData.level;
            document.getElementById('modalTeamLevel').setAttribute('data-level', teamData.level.toLowerCase());
            document.getElementById('modalTeamSlogan').textContent = teamData.slogan;
            document.getElementById('modalTeamDescription').textContent = teamData.description;
            document.getElementById('modalTeamMembers').textContent = `${teamData.members}/${teamData.max}`;
            document.getElementById('modalTeamLocation').textContent = teamData.location;
            
            const progressPercent = Math.min(100, (teamData.members / teamData.max) * 100);
            document.getElementById('modalTeamProgress').style.width = progressPercent + '%';
            
            modal.style.display = 'flex';
        }
        
        // Close button handlers
        if (closeModal) closeModal.addEventListener('click', closeSampleTeamModal);
        if (closeModalBtn) closeModalBtn.addEventListener('click', closeSampleTeamModal);
        
        // Close when clicking outside modal
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeSampleTeamModal();
            });
        }
        
        // Handle sample team button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('sample-team-btn')) {
                const btn = e.target;
                const teamData = {
                    id: btn.getAttribute('data-team-id'),
                    name: btn.getAttribute('data-team-name'),
                    level: btn.getAttribute('data-team-level'),
                    location: btn.getAttribute('data-team-location'),
                    members: parseInt(btn.getAttribute('data-team-members')),
                    max: parseInt(btn.getAttribute('data-team-max')),
                    slogan: btn.getAttribute('data-team-slogan'),
                    description: btn.getAttribute('data-team-description')
                };
                openSampleTeamModal(teamData);
            }
        });
    })();
</script>
@endsection
