@extends('layout')

@section('title', 'Statistics - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Statistics</h1>
            <p class="page-subtitle">Season 2026 · Updated Jun 4, 2026</p>
        </div>
    </section>

    <section class="admin-stats-profiles">
        @foreach($players as $index => $player)
            <article class="admin-panel admin-radar-card">
                <div class="admin-user-cell">
                    <span class="admin-avatar">{{ $player['initials'] }}</span>
                    <div>
                        <strong>{{ $player['name'] }}</strong>
                        <small>{{ $player['wins'] }}W / {{ $player['losses'] }}L · {{ round(($player['wins'] / max(1, $player['wins'] + $player['losses'])) * 100) }}% win rate</small>
                    </div>
                </div>
                <canvas id="radar{{ $index }}"></canvas>
            </article>
        @endforeach
    </section>

    <section class="admin-chart-grid">
        <article class="admin-panel">
            <div class="admin-panel-heading"><h2>Season Trends</h2></div>
            <canvas id="seasonTrend"></canvas>
        </article>
        <article class="admin-panel">
            <div class="admin-panel-heading"><h2>Matches By Category</h2></div>
            <canvas id="categoryBars"></canvas>
        </article>
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const adminChartGrid = 'rgba(255,255,255,0.06)';
const adminMuted = '#6b7280';
const radarSets = [
    [82, 74, 65, 78, 70],
    [76, 81, 62, 84, 74],
    [72, 68, 79, 73, 77],
];

radarSets.forEach((values, index) => {
    new Chart(document.getElementById(`radar${index}`), {
        type: 'radar',
        data: {
            labels: ['Win Rate', 'Smash', 'Net Play', 'Endurance', 'Rally'],
            datasets: [{
                data: values,
                borderColor: index === 0 ? '#c8f53a' : (index === 1 ? '#38bdf8' : '#a78bfa'),
                backgroundColor: index === 0 ? 'rgba(200,245,58,.14)' : (index === 1 ? 'rgba(56,189,248,.14)' : 'rgba(167,139,250,.14)'),
                pointRadius: 0
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { r: { angleLines: { color: adminChartGrid }, grid: { color: adminChartGrid }, pointLabels: { color: adminMuted, font: { size: 10 } }, ticks: { display: false } } }
        }
    });
});

new Chart(document.getElementById('seasonTrend'), {
    type: 'line',
    data: {
        labels: @json($seasonLabels),
        datasets: [
            { label: 'Players', data: @json($seasonPlayers), borderColor: '#38bdf8', backgroundColor: 'transparent', tension: .35 },
            { label: 'Matches', data: @json($seasonMatches), borderColor: '#c8f53a', backgroundColor: 'transparent', tension: .35 }
        ]
    },
    options: { plugins: { legend: { labels: { color: '#e8eaf0' } } }, scales: { x: { ticks: { color: adminMuted }, grid: { color: adminChartGrid } }, y: { ticks: { color: adminMuted }, grid: { color: adminChartGrid } } } }
});

new Chart(document.getElementById('categoryBars'), {
    type: 'bar',
    data: { labels: @json($categoryLabels), datasets: [{ data: @json($categoryMatches), backgroundColor: '#a78bfa', borderRadius: 3 }] },
    options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { color: adminMuted }, grid: { color: adminChartGrid } }, y: { ticks: { color: adminMuted }, grid: { display: false } } } }
});
</script>
@endpush
