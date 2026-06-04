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

        if ($players === []) {
            $players = array_slice(AdminMockData::players(), 0, 3);
        }

        $months = collect(range(5, 0))->map(fn ($offset) => now()->subMonths($offset)->startOfMonth());
        $seasonLabels = $months->map(fn (Carbon $month) => $month->format('M'))->all();
        $seasonPlayers = $months->map(fn (Carbon $month) => User::where('created_at', '<=', $month->copy()->endOfMonth())->count())->all();
        $seasonMatches = $months->map(fn (Carbon $month) => GameMatch::whereBetween('match_date', [$month, $month->copy()->endOfMonth()])->count())->all();

        if (array_sum($seasonPlayers) === 0 && array_sum($seasonMatches) === 0) {
            $seasonLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $seasonPlayers = [860, 900, 1010, 1120, 1210, 1284];
            $seasonMatches = [220, 180, 245, 205, 275, 310];
        }

        $categoryLabels = ['Beginner', 'Intermediate', 'Advanced', 'Professional'];
        $categoryMatches = collect($categoryLabels)
            ->map(fn (string $rank) => User::where('rank', $rank)->count())
            ->all();

        if (array_sum($categoryMatches) === 0) {
            $categoryLabels = ["Men's Singles", "Women's Singles", "Men's Doubles", "Women's Doubles", 'Mixed Doubles'];
            $categoryMatches = [512, 388, 160, 118, 235];
        }

        return view('admin.statistics', compact(
            'players',
            'seasonLabels',
            'seasonPlayers',
            'seasonMatches',
            'categoryLabels',
            'categoryMatches'
        ));
    }
}
