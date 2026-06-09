<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GameMatch;
use App\Services\BetService;
use Illuminate\Support\Collection;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show($id, BetService $betService)
    {
        $user = User::withCount(['posts', 'bets', 'teams', 'tournaments'])->findOrFail($id);
        $isOwner = Auth::id() === $user->id;

        $matches = GameMatch::with(['player1', 'player2', 'winner'])
            ->where(function ($query) use ($id) {
                $query->where('player1_id', $id)
                    ->orWhere('player2_id', $id);
            })
            ->latest('match_date')
            ->limit(10)
            ->get();

        $posts = $user->posts()
            ->with(['user', 'comments.user'])
            ->withCount(['comments'])
            ->latest()
            ->paginate(10);

        $bets = $betService->getUserBetHistory($user)->take(12)->values();
        $walletTransactions = $user->walletTransactions()->latest()->limit(15)->get();

        $playerBetMarkets = GameMatch::with(['player1', 'player2', 'bets.user', 'bets.betOnUser'])
            ->where(function ($query) use ($id) {
                $query->where('player1_id', $id)
                    ->orWhere('player2_id', $id);
            })
            ->whereHas('bets')
            ->latest('match_date')
            ->limit(8)
            ->get()
            ->map(function (GameMatch $match) use ($id) {
                $totalPool = (float) $match->bets->sum('amount');
                $playerPool = (float) $match->bets->where('bet_on_user_id', (int) $id)->sum('amount');
                $playerTickets = $match->bets->where('bet_on_user_id', (int) $id)->count();
                $poolShare = $totalPool > 0 ? (int) round(($playerPool / $totalPool) * 100) : 0;

                return [
                    'match' => $match,
                    'total_pool' => $totalPool,
                    'player_pool' => $playerPool,
                    'player_tickets' => $playerTickets,
                    'total_tickets' => $match->bets->count(),
                    'pool_share' => $poolShare,
                ];
            });

        $betStats = $this->buildBetStats($bets);
        $walletTier = $this->walletTier((int) ($user->virtual_coins ?? 0));
        $xp = $this->profileXp($user);
        $sportProfile = $this->sportProfile($user);
        $achievements = $this->achievementsFor($user, $matches, $bets);

        return view('profile.show', compact(
            'user',
            'isOwner',
            'matches',
            'posts',
            'bets',
            'walletTransactions',
            'playerBetMarkets',
            'betStats',
            'walletTier',
            'xp',
            'sportProfile',
            'achievements'
        ));
    }

    private function buildBetStats(Collection $bets): array
    {
        $total = $bets->count();
        $won = $bets->where('status', 'won')->count();
        $lost = $bets->where('status', 'lost')->count();
        $active = $bets->whereIn('status', ['pending', 'open', 'live'])->count();
        $staked = (float) $bets->sum('amount');
        $payout = (float) $bets->where('status', 'won')->sum('payout');

        return [
            'total' => $total,
            'won' => $won,
            'lost' => $lost,
            'active' => $active,
            'staked' => $staked,
            'payout' => $payout,
            'win_rate' => $total > 0 ? (int) round(($won / $total) * 100) : 0,
            'loss_rate' => $total > 0 ? (int) round(($lost / $total) * 100) : 0,
        ];
    }

    private function walletTier(int $coins): array
    {
        if ($coins >= 15000) {
            return ['label' => 'Gold', 'class' => 'tier-gold', 'next' => null];
        }

        if ($coins >= 7500) {
            return ['label' => 'Silver', 'class' => 'tier-silver', 'next' => max(0, 15000 - $coins)];
        }

        return ['label' => 'Bronze', 'class' => 'tier-bronze', 'next' => max(0, 7500 - $coins)];
    }

    private function profileXp(User $user): array
    {
        $score = ((int) ($user->elo_rating ?? 0))
            + (((int) ($user->wins ?? 0)) * 120)
            + (((int) ($user->posts_count ?? 0)) * 30)
            + (((int) ($user->bets_count ?? 0)) * 20);

        $level = max(1, (int) floor($score / 1000));
        $current = $score % 1000;

        return [
            'level' => $level,
            'current' => $current,
            'target' => 1000,
            'percent' => min(100, (int) round(($current / 1000) * 100)),
        ];
    }

    private function sportProfile(User $user): array
    {
        $styleMap = [
            'Beginner' => 'All-rounder',
            'Intermediate' => 'Attacking',
            'Advanced' => 'Counter attacker',
            'Professional' => 'Explosive net player',
        ];

        $rank = $user->rank ?: 'Beginner';
        $winRate = method_exists($user, 'getWinRate') ? $user->getWinRate() : 0;

        if ($winRate >= 75) {
            $form = 'Excellent';
        } elseif ($winRate >= 55) {
            $form = 'Good';
        } elseif (((int) ($user->wins ?? 0) + (int) ($user->losses ?? 0)) > 0) {
            $form = 'Developing';
        } else {
            $form = 'Unrated';
        }

        return [
            'handedness' => $user->handedness ?: 'Not set',
            'playing_style' => $user->playing_style ?: ($styleMap[$rank] ?? 'All-rounder'),
            'skill_level' => $rank,
            'form' => $form,
        ];
    }

    private function achievementsFor(User $user, Collection $matches, Collection $bets): array
    {
        $activityCount = (int) ($user->posts_count ?? 0) + $matches->count() + $bets->count();

        return [
            [
                'title' => 'First Win',
                'icon' => 'WIN',
                'unlocked' => (int) ($user->wins ?? 0) > 0,
                'hint' => 'Record your first confirmed match win.',
            ],
            [
                'title' => '5-Match Streak',
                'icon' => '5X',
                'unlocked' => (int) ($user->wins ?? 0) >= 5,
                'hint' => 'Reach at least five wins on your profile.',
            ],
            [
                'title' => 'Thousand-Point Tycoon',
                'icon' => '1K',
                'unlocked' => (int) ($user->virtual_coins ?? 0) >= 1000,
                'hint' => 'Hold at least 1,000 virtual points.',
            ],
            [
                'title' => 'Sharp Predictor',
                'icon' => 'BET',
                'unlocked' => $bets->where('status', 'won')->count() >= 3,
                'hint' => 'Win three betting predictions.',
            ],
            [
                'title' => 'Active Participation',
                'icon' => 'ACT',
                'unlocked' => $activityCount >= 5,
                'hint' => 'Join matches, post, or place bets at least five times.',
            ],
        ];
    }

    public function edit()
    {
        $user = Auth::user();
        $loginActivities = $user->loginActivities()->latest()->limit(8)->get();
        $notificationPreference = $user->notificationPreference()->firstOrCreate([]);

        return view('profile.edit', compact('user', 'loginActivities', 'notificationPreference'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'bio' => 'nullable|string|max:500',
            'handedness' => 'nullable|string|in:Right,Left,Ambidextrous',
            'playing_style' => 'nullable|string|in:All-rounder,Attacking,Defensive,Counter attacker,Net player,Explosive net player',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && file_exists(public_path('avatars/' . $user->avatar))) {
                unlink(public_path('avatars/' . $user->avatar));
            }

            $file = $request->file('avatar');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('avatars'), $filename);
            $validated['avatar'] = $filename;
        }

        $user->update($validated);

        return redirect()->route('profile.show', $user->id)->with('success', 'Profile updated successfully!');
    }
}
