<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $upcomingMatches = GameMatch::with(['player1', 'player2'])
            ->where(function ($q) use ($user) {
                $q->where('player1_id', $user->id)->orWhere('player2_id', $user->id);
            })
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->limit(5)
            ->get();

        $recentMatches = GameMatch::where(function ($q) use ($user) {
            $q->where('player1_id', $user->id)->orWhere('player2_id', $user->id);
        })
            ->where('status', 'completed')
            ->latest()
            ->limit(5)
            ->get();

        $openMatches = GameMatch::with(['player1'])
            ->where('status', 'open')
            ->where('player1_id', '!=', $user->id)
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->limit(4)
            ->get();

        $communityPosts = Post::with([
            'user',
            'comments' => function ($q) {
                $q->latest()->take(2);
            },
            'comments.user',
        ])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(6);

        $leaderboard = User::orderBy('elo_rating', 'desc')->limit(10)->get();

        $predictionMasters = User::query()
            ->select('users.*')
            ->selectSub(function ($query) {
                $query->from('bets')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('bets.user_id', 'users.id');
            }, 'prediction_count')
            ->selectSub(function ($query) {
                $query->from('bets')
                    ->selectRaw("SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END)")
                    ->whereColumn('bets.user_id', 'users.id');
            }, 'prediction_wins')
            ->orderByDesc('prediction_wins')
            ->orderByDesc('prediction_count')
            ->limit(6)
            ->get();

        $onlinePlayers = User::where('id', '!=', $user->id)
            ->orderBy('elo_rating', 'desc')
            ->limit(8)
            ->get();

        $notifications = $user->notifications()
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'upcomingMatches',
            'recentMatches',
            'leaderboard',
            'notifications',
            'openMatches',
            'communityPosts',
            'onlinePlayers',
            'predictionMasters'
        ));
    }
}
