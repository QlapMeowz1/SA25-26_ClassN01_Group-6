<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameMatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StatisticsController extends Controller
{
    public function index()
    {
        $players = User::orderByDesc('elo_rating')
            ->limit(3)
            ->get()
            ->map(fn (User $user) => [
                'initials' => Str::of($user->name)->explode(' ')->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode(''),
                'name' => $user->name,
                'wins' => (int) $user->wins,
                'losses' => (int) $user->losses,
            ])
            ->all();

        if (config('app.demo_data') && count($players) < 3) {
            $existingNames = collect($players)->pluck('name')->map(fn ($name) => Str::lower($name))->all();
            $fallbackPlayers = array_values(array_filter(AdminMockData::players(), function ($player) use ($existingNames) {
                return !in_array(Str::lower($player['name']), $existingNames, true);
            }));

            $players = array_slice(array_merge($players, $fallbackPlayers), 0, 3);
        }

        $radarSets = collect($players)->map(function (array $player, int $index) {
            $wins = (int) ($player['wins'] ?? 0);
            $losses = (int) ($player['losses'] ?? 0);
            $rating = (int) ($player['rating'] ?? (2400 + ($index * 90)));
            $winRate = (int) round(($wins / max(1, $wins + $losses)) * 100);

            return [
                min(96, max(55, $winRate)),
                min(94, max(50, (int) round($rating / 32))),
                min(92, max(48, 60 + ($wins % 18))),
                min(90, max(45, 72 - min(22, $losses))),
                min(95, max(50, 58 + (($wins + $rating) % 24))),
            ];
        })->all();

        $months = collect(range(11, 0))->map(fn ($offset) => now()->subMonths($offset)->startOfMonth());
        $seasonLabels = $months->map(fn (Carbon $month) => $month->format('M'))->all();
        $seasonPlayers = $months->map(fn (Carbon $month) => User::where('created_at', '<=', $month->copy()->endOfMonth())->count())->all();
        $seasonMatches = $months->map(fn (Carbon $month) => GameMatch::whereBetween('match_date', [$month, $month->copy()->endOfMonth()])->count())->all();

        if (config('app.demo_data') && array_sum($seasonPlayers) < 50 && array_sum($seasonMatches) < 50) {
            $seasonLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $seasonPlayers = [860, 904, 966, 1015, 1078, 1140, 1186, 1235, 1284, 1322, 1375, 1428];
            $seasonMatches = [220, 184, 245, 206, 274, 318, 366, 342, 421, 458, 501, 536];
        }

        $rankLabels = ['Beginner', 'Intermediate', 'Advanced', 'Professional'];
        $rankCounts = collect($rankLabels)
            ->map(fn (string $rank) => User::where('rank', $rank)->count())
            ->all();

        if (config('app.demo_data') && array_sum($rankCounts) < 10) {
            $categoryLabels = ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", 'Mixed Doubles'];
            $categoryMatches = [512, 388, 160, 118, 235];
        } else {
            $categoryLabels = array_merge($rankLabels, ['Open Challenge', 'Tournament', 'Team Scrim']);
            $categoryMatches = array_merge($rankCounts, [
                GameMatch::where('status', 'open')->count(),
                GameMatch::whereNotNull('challenge_id')->count(),
                max(8, (int) round(array_sum($rankCounts) * 0.4)),
            ]);
        }

        return view('admin.statistics', compact(
            'players',
            'radarSets',
            'seasonLabels',
            'seasonPlayers',
            'seasonMatches',
            'categoryLabels',
            'categoryMatches'
        ));
    }
}
