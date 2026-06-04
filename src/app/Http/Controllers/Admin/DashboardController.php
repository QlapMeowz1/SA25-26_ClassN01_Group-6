<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use App\Models\Tournament;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $playerCount = User::count();
        $matchCount = GameMatch::count();
        $activeTournamentCount = Tournament::whereIn('status', ['upcoming', 'in_progress'])->count();
        $newPlayers = User::where('created_at', '>=', now()->subDays(7))->count();
        $openQueues = GameMatch::where('status', 'open')->count();

        $stats = [
            ['label' => 'Total Players', 'value' => number_format($playerCount), 'change' => '+' . number_format($newPlayers), 'trend' => 'up'],
            ['label' => 'Active Tournaments', 'value' => number_format($activeTournamentCount), 'change' => '+' . number_format(Tournament::where('created_at', '>=', now()->subDays(7))->count()), 'trend' => 'up'],
            ['label' => 'Matches This Week', 'value' => number_format(GameMatch::where('match_date', '>=', now()->startOfWeek())->where('match_date', '<=', now()->endOfWeek())->count()), 'change' => $openQueues, 'trend' => $openQueues ? 'up' : 'down'],
            ['label' => 'Avg. Match Rating', 'value' => '4.7', 'change' => '+0.3', 'trend' => 'up'],
        ];

        $recentMatches = GameMatch::with(['player1', 'player2'])
            ->latest('match_date')
            ->limit(5)
            ->get()
            ->map(fn ($match) => [
                'id' => 'M-' . str_pad($match->id, 4, '0', STR_PAD_LEFT),
                'players' => ($match->player1?->name ?? 'Player 1') . ' vs ' . ($match->player2?->name ?? 'TBD'),
                'tournament' => $match->location ?? 'Open Queue',
                'score' => ($match->player1_score ?? '—') . ' / ' . ($match->player2_score ?? '—'),
                'status' => \Illuminate\Support\Str::headline($match->status),
            ])
            ->all();
        if (empty($recentMatches)) {
            $recentMatches = array_slice(AdminMockData::matches(), 0, 5);
        }

        $liveMatches = GameMatch::where('status', 'in_progress')->count();

        return view('admin.dashboard', compact('stats', 'recentMatches', 'liveMatches'));
    }
}
