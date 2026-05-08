<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

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

        $communityPosts = Post::with(['user', 'comments'])
            ->withCount('likes')
            ->latest()
            ->limit(6)
            ->get();

        if ($communityPosts->count() < 5) {
            $samplePosts = collect([
                [
                    'name' => 'meowhunterz',
                    'content' => 'Just finished an intense 3-set match at Central Court! That last smash was perfection 🏸',
                    'hours' => 2,
                    'likes' => 5,
                    'comments' => 2,
                ],
                [
                    'name' => 'ShuttleKing',
                    'content' => "Who's up for doubles this Saturday at Saigon Sports Complex? Need 3 more players!",
                    'hours' => 5,
                    'likes' => 8,
                    'comments' => 4,
                ],
                [
                    'name' => 'CourtKings',
                    'content' => 'Congratulations to Hanoi Birdies for winning the friendly tournament! Well played everyone 👏',
                    'days' => 1,
                    'likes' => 12,
                    'comments' => 6,
                ],
                [
                    'name' => 'SmashPro',
                    'content' => 'New personal record: 285 km/h smash speed! Getting closer to the pros 💪',
                    'days' => 2,
                    'likes' => 15,
                    'comments' => 9,
                ],
                [
                    'name' => 'RacketMaster',
                    'content' => 'Looking for a regular training partner. Intermediate level, District 7 area. DM me!',
                    'days' => 3,
                    'likes' => 3,
                    'comments' => 7,
                ],
            ])->map(function ($sample, $index) use ($user) {
                $createdAt = isset($sample['hours'])
                    ? Carbon::now()->subHours($sample['hours'])
                    : Carbon::now()->subDays($sample['days']);

                return (object) [
                    'id' => 'sample-post-' . ($index + 1),
                    'user' => (object) [
                        'id' => $user->id,
                        'name' => $sample['name'],
                    ],
                    'content' => $sample['content'],
                    'created_at' => $createdAt,
                    'likes_count' => $sample['likes'],
                    'comments' => collect(range(1, $sample['comments']))->map(fn () => (object) ['id' => null]),
                ];
            });

            $communityPosts = $communityPosts
                ->concat($samplePosts)
                ->take(5)
                ->values();
        }

        $leaderboard = User::orderBy('elo_rating', 'desc')->limit(10)->get();

        $notifications = $user->notifications()
                              ->latest()
                              ->limit(10)
                              ->get();

        return view('dashboard', compact('upcomingMatches', 'recentMatches', 'leaderboard', 'notifications', 'openMatches', 'communityPosts'));
    }
}
