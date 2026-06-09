@extends('layout')

@section('title', 'Audit Log - SMASH Admin')

@section('content')
<div class="page-shell admin-console-page">
    @include('admin.partials.nav')

    <section class="admin-page-header">
        <div>
            <h1>Audit Log</h1>
            <p class="page-subtitle">Trace security, betting, moderation, and account changes.</p>
        </div>
    </section>

    <section class="admin-panel">
        <form method="GET" class="admin-filter-bar">
            <input type="search" name="q" value="{{ $search }}" placeholder="Search action, actor, or subject...">
            <button class="btn btn-secondary btn-small">Filter</button>
        </form>
    </section>

    <section class="admin-panel admin-table-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Time</th><th>Actor</th><th>Action</th><th>Subject</th><th>IP</th><th>Changes</th></tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, H:i:s') }}</td>
                            <td>{{ $log->actor?->name ?? 'System' }}</td>
                            <td><strong>{{ \Illuminate\Support\Str::headline($log->action) }}</strong></td>
                            <td>{{ class_basename($log->subject_type ?: 'System') }} #{{ $log->subject_id ?: '-' }}</td>
                            <td>{{ $log->ip_address ?: '-' }}</td>
                            <td><small>{{ \Illuminate\Support\Str::limit(json_encode(['before' => $log->before, 'after' => $log->after]), 120) }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No audit entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
    </section>
</div>
@endsection
