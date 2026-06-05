<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\Challenge;
use App\Models\Comment;
use App\Models\GameMatch;
use App\Models\Post;
use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $sevenDaysAgo = now()->subDays(7);

        $stats = [
            'users' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'new_users' => User::where('created_at', '>=', $sevenDaysAgo)->count(),
            'posts' => Post::count(),
            'new_posts' => Post::where('created_at', '>=', $sevenDaysAgo)->count(),
            'comments' => Comment::count(),
            'matches' => GameMatch::count(),
            'open_matches' => GameMatch::where('status', 'open')->count(),
            'completed_matches' => GameMatch::where('status', 'completed')->count(),
            'teams' => Team::count(),
            'tournaments' => Tournament::count(),
            'active_tournaments' => Tournament::whereIn('status', ['upcoming', 'in_progress'])->count(),
            'bets' => Bet::count(),
            'pending_bets' => Bet::where('status', 'pending')->count(),
            'bet_volume' => Bet::sum('amount'),
            'pending_bet_volume' => Bet::where('status', 'pending')->sum('amount'),
        ];

        $recentUsers = User::latest()->limit(6)->get();
        $recentPosts = Post::with('user')->withCount(['likes', 'comments'])->latest()->limit(5)->get();
        $topPlayers = User::orderBy('elo_rating', 'desc')->limit(5)->get();
        $upcomingMatches = GameMatch::with(['player1', 'player2'])
            ->whereIn('status', ['open', 'scheduled', 'in_progress'])
            ->orderBy('match_date')
            ->limit(6)
            ->get();
        $recentTournaments = Tournament::with('organizer')->latest()->limit(5)->get();
        $pendingChallenges = Challenge::with(['challenger', 'opponent'])
            ->whereIn('status', ['open', 'pending'])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recentUsers',
            'recentPosts',
            'topPlayers',
            'upcomingMatches',
            'recentTournaments',
            'pendingChallenges'
        ));
    }

    public function users(Request $request)
    {
        $query = User::query()
            ->withCount(['posts', 'bets', 'teamMembers'])
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');
                $builder->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), function ($builder) use ($request) {
                $builder->where('role', $request->string('role'));
            });

        $users = $query->latest()->paginate(12)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['user', 'admin'])],
        ]);

        if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
            return back()->with('error', 'You cannot remove your own admin role.');
        }

        if ($user->role === 'admin' && $validated['role'] !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'At least one admin account is required.');
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('success', 'User role updated.');
    }

    public function content(Request $request)
    {
        $posts = Post::with('user')
            ->withCount(['likes', 'comments'])
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = $request->string('search');
                $builder->where(function ($q) use ($search) {
                    $q->where('content', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $comments = Comment::with(['user', 'post.user'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.content', compact('posts', 'comments'));
    }

    public function destroyPost(Post $post)
    {
        DB::transaction(function () use ($post) {
            $commentIds = $post->comments()->pluck('id');

            if (Schema::hasTable('comment_likes') && $commentIds->isNotEmpty()) {
                DB::table('comment_likes')->whereIn('comment_id', $commentIds)->delete();
            }

            if (Schema::hasTable('post_likes')) {
                DB::table('post_likes')->where('post_id', $post->id)->delete();
            }

            $post->comments()->delete();
            $post->delete();
        });

        return back()->with('success', 'Post removed by admin.');
    }

    public function destroyComment(Comment $comment)
    {
        DB::transaction(function () use ($comment) {
            $commentIds = collect([$comment->id]);

            if (Schema::hasColumn('comments', 'parent_id')) {
                $commentIds = $commentIds->merge(Comment::where('parent_id', $comment->id)->pluck('id'));
            }

            if (Schema::hasTable('comment_likes')) {
                DB::table('comment_likes')->whereIn('comment_id', $commentIds)->delete();
            }

            if (Schema::hasColumn('comments', 'parent_id')) {
                Comment::where('parent_id', $comment->id)->delete();
            }

            $comment->delete();
        });

        return back()->with('success', 'Comment removed by admin.');
    }

    public function matches(Request $request)
    {
        $matches = GameMatch::with(['player1', 'player2', 'winner'])
            ->withCount('bets')
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.matches', compact('matches'));
    }

    public function tournaments(Request $request)
    {
        $tournaments = Tournament::with('organizer')
            ->withCount('tournamentParticipants')
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.tournaments', compact('tournaments'));
    }

    public function bets(Request $request)
    {
        $bets = Bet::with(['user', 'betOnUser', 'gameMatch.player1', 'gameMatch.player2'])
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $betStats = [
            'total' => Bet::count(),
            'pending' => Bet::where('status', 'pending')->count(),
            'won' => Bet::where('status', 'won')->count(),
            'lost' => Bet::where('status', 'lost')->count(),
            'volume' => Bet::sum('amount'),
            'payout' => Bet::sum('payout'),
        ];

        return view('admin.bets', compact('bets', 'betStats'));
    }
}
