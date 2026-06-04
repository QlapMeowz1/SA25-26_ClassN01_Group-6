@extends('layout')

@section('title', 'Teams - BadNet')

@php
    $myTeamCount = $myTeams->count();
    $suggestedCount = $suggestedTeams->count();
    $allTeamCount = $allTeams->count();
    $cleanTeamText = function ($value, $fallback = '') {
        $text = trim((string) $value);
        return mb_strlen($text) >= 8 && preg_match('/^[\pL\s.,!?\'"-]+$/u', $text) ? $text : $fallback;
    };
    $teamLevelLabel = function ($value) {
        $level = trim((string) $value);
        return $level && strtolower($level) !== 'n/a' ? $level : null;
    };
@endphp

@section('content')
<div class="page-shell team-page">
    <section class="team-hero-panel">
        <div class="team-hero-copy">
            <p class="home-eyebrow">{{ __('ui.team.my_teams') }}</p>
            <h1>{{ __('ui.team.title') }}</h1>
            <p class="page-subtitle">{{ __('ui.team.subtitle') }}</p>
        </div>

        <div class="team-hero-actions">
            <a href="{{ route('teams.create') }}" class="btn btn-primary">{{ __('ui.team.create') }}</a>
            <a href="#all-teams" class="btn btn-secondary">{{ __('ui.team.browse') }}</a>
        </div>
    </section>

    <div class="team-summary-grid">
        <a href="#my-teams" class="team-summary-card">
            <span>{{ __('ui.team.my_teams') }}</span>
            <strong>{{ $myTeamCount }}</strong>
            <small>{{ __('ui.team.active') }}</small>
        </a>
        <a href="#suggested-teams" class="team-summary-card">
            <span>{{ __('ui.team.for_you') }}</span>
            <strong>{{ $suggestedCount }}</strong>
            <small>{{ __('ui.team.suggested') }}</small>
        </a>
        <a href="#all-teams" class="team-summary-card">
            <span>{{ __('ui.team.all_teams') }}</span>
            <strong>{{ $allTeamCount }}</strong>
            <small>{{ __('ui.team.find_roster') }}</small>
        </a>
    </div>

    <div class="team-layout">
        <main class="team-main-column">
            <section class="team-section-block" id="my-teams">
                <div class="team-section-heading">
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
                            <article class="team-card team-card-modern">
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
                                        @if($levelLabel = $teamLevelLabel($team->level))
                                            <span class="team-badge" data-level="{{ strtolower($levelLabel) }}">{{ $levelLabel }}</span>
                                        @endif
                                    </div>

                                    <p class="team-slogan">{{ $cleanTeamText($team->slogan, 'A team with a shared passion') }}</p>
                                    @if($cleanTeamText($team->description))
                                        <p class="team-description">{{ \Illuminate\Support\Str::limit($cleanTeamText($team->description), 90) }}</p>
                                    @endif

                                    <div class="team-meta-grid">
                                        <div class="team-meta-item team-meta-members">
                                            <span class="meta-label">{{ __('ui.team.members') }}</span>
                                            <div class="progress-bar-small">
                                                <div class="progress-fill" style="width: {{ min(100, ($team->members_count ?? 0) * 5) }}%"></div>
                                            </div>
                                            <span class="meta-value">{{ $team->members_count ?? 0 }}/{{ $team->max_members ?? 20 }}</span>
                                        </div>
                                        <div class="team-meta-item team-meta-location">
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

            <section class="team-section-block" id="all-teams">
                <div class="team-section-heading">
                    <div>
                        <p class="home-eyebrow">{{ __('ui.team.all_teams') }}</p>
                        <h2>{{ __('ui.team.find_roster') }}</h2>
                    </div>
                </div>

                <form method="GET" action="{{ route('teams.index') }}" class="team-search-bar">
                    <div class="search-input-wrapper">
                        <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.team.search_placeholder') }}">
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
                            <article class="team-card team-card-modern">
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
                                        @if($levelLabel = $teamLevelLabel($team->level))
                                            <span class="team-badge" data-level="{{ strtolower($levelLabel) }}">{{ $levelLabel }}</span>
                                        @endif
                                    </div>

                                    <p class="team-slogan">{{ $cleanTeamText($team->slogan, 'A competitive team') }}</p>
                                    @if($cleanTeamText($team->description))
                                        <p class="team-description">{{ \Illuminate\Support\Str::limit($cleanTeamText($team->description), 90) }}</p>
                                    @endif

                                    <div class="team-meta-grid">
                                        <div class="team-meta-item team-meta-members">
                                            <span class="meta-label">{{ __('ui.team.members') }}</span>
                                            <div class="progress-bar-small">
                                                <div class="progress-fill" style="width: {{ min(100, ($team->members_count ?? 0) * 5) }}%"></div>
                                            </div>
                                            <span class="meta-value">{{ $team->members_count ?? 0 }}/{{ $team->max_members ?? 20 }}</span>
                                        </div>
                                        <div class="team-meta-item team-meta-location">
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
        </main>

        <aside class="team-side-column">
            <section class="team-section-block" id="suggested-teams">
                <div class="team-section-heading">
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
                    <div class="team-list-compact">
                        @foreach($suggestedTeams as $team)
                            <article class="team-compact-card">
                                <div class="team-compact-top">
                                    <span class="team-avatar-small">{{ strtoupper(substr($team->name, 0, 1)) }}</span>
                                    <div class="team-compact-meta">
                                        <strong>{{ $team->name }}</strong>
                                        <small>{{ $teamLevelLabel($team->level) ?? __('ui.team.level') }} - {{ $team->location }}</small>
                                    </div>
                                    @if($levelLabel = $teamLevelLabel($team->level))
                                        <span class="team-badge" data-level="{{ strtolower($levelLabel) }}">{{ $levelLabel }}</span>
                                    @endif
                                </div>
                                <p class="team-description">{{ \Illuminate\Support\Str::limit($team->description, 96) }}</p>
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
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </aside>
    </div>
</div>

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

        const modal = document.getElementById('sampleTeamModal');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');

        function closeSampleTeamModal() {
            if (modal) modal.style.display = 'none';
        }

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

        if (closeModal) closeModal.addEventListener('click', closeSampleTeamModal);
        if (closeModalBtn) closeModalBtn.addEventListener('click', closeSampleTeamModal);

        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeSampleTeamModal();
            });
        }

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
