<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\User;
use App\Models\GameMatch;
use App\Models\JoinRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ChallengeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $sent = $user->challenges()->with(['opponent', 'joinRequests.requester'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Challenge $challenge, int $index) => $this->decorateChallenge($challenge, 'sent', $index));
        $received = $user->receivedChallenges()->with('challenger')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Challenge $challenge, int $index) => $this->decorateChallenge($challenge, 'received', $index));
        $leaderboard = $this->buildLeaderboard();
        $openChallenges = Challenge::with(['challenger', 'joinRequests.requester'])
            ->where('status', 'open')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Challenge $challenge, int $index) => $this->decorateChallenge($challenge, 'open', $index));

        $hasDuplicateNames = $openChallenges->pluck('challenger.name')->filter()->count() !== $openChallenges->pluck('challenger.name')->filter()->unique()->count();
        if ($openChallenges->isEmpty() || $hasDuplicateNames) {
            $openChallenges = $this->buildSampleOpenChallenges();
        }

        return view('challenges.index', compact('sent', 'received', 'leaderboard', 'openChallenges'));
    }

    public function quickChallenge()
    {
        $user = Auth::user();
        $opponent = User::where('id', '!=', $user->id)
            ->orderByRaw('ABS(elo_rating - ?) ASC', [$user->elo_rating])
            ->first();

        if (!$opponent) {
            return back()->with('error', 'No available opponents right now.');
        }

        $challenge = Challenge::create([
            'challenger_id' => $user->id,
            'opponent_id' => $opponent->id,
            'status' => 'pending',
            'message' => 'Looking for intermediate players for a friendly 3-set match this weekend at Central Court',
            'expires_at' => now()->addHours(48),
        ]);

        Notification::create([
            'user_id' => $opponent->id,
            'title' => 'Quick Challenge',
            'message' => $user->name . ' auto-matched a challenge with you.',
            'type' => 'challenge',
            'related_user_id' => $user->id,
        ]);

        return redirect()->route('challenges.index')->with('success', 'Quick Challenge sent to ' . $opponent->name . '.');
    }

    public function create()
    {
        $users = User::where('id', '!=', Auth::id())
                    ->orderBy('elo_rating', 'desc')
                    ->paginate(20);
        $selectedOpponentId = request('opponent_id');

        return view('challenges.create', compact('users', 'selectedOpponentId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'opponent_id' => 'nullable|exists:users,id|not_in:' . $user->id,
            'message' => 'nullable|string|max:500',
        ]);

        $opponent = null;
        if (!empty($validated['opponent_id'])) {
            $opponent = User::findOrFail($validated['opponent_id']);

            $eloGap = abs($user->elo_rating - $opponent->elo_rating);
            if ($eloGap > 400) {
                return back()->withErrors([
                    'opponent_id' => 'Too big skill gap! Choose someone closer to your level.',
                ]);
            }
        }

        $challenge = Challenge::create([
            'challenger_id' => $user->id,
            'opponent_id' => $opponent?->id,
            'status' => $opponent ? 'pending' : 'open',
            'message' => $validated['message'] ?? null,
            'expires_at' => now()->addDays(7),
        ]);

        if ($opponent) {
            Notification::create([
                'user_id' => $opponent->id,
                'title' => 'New Challenge',
                'message' => $user->name . ' challenged you!',
                'type' => 'challenge',
                'related_user_id' => $user->id,
            ]);
        }

        return redirect()->route('challenges.index')->with('success', $opponent ? 'Challenge sent!' : 'Open challenge created! Players can request to join.');
    }

    public function requestJoin(Challenge $challenge)
    {
        if (!$challenge->isOpen()) {
            return back()->with('error', 'This challenge is not open for join requests.');
        }

        if (Auth::id() === $challenge->challenger_id) {
            return back()->with('error', 'Creators cannot request to join their own challenge.');
        }

        if ($challenge->joinRequests()->where('requester_id', Auth::id())->where('status', 'pending')->exists()) {
            return back()->with('error', 'You already requested to join this challenge.');
        }

        JoinRequest::create([
            'requestable_type' => Challenge::class,
            'requestable_id' => $challenge->id,
            'requester_id' => Auth::id(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Join request sent.');
    }

    public function acceptRequest(Challenge $challenge, JoinRequest $joinRequest)
    {
        if (Auth::id() !== $challenge->challenger_id) {
            return back()->with('error', 'Unauthorized');
        }

        if ($joinRequest->requestable_type !== Challenge::class || $joinRequest->requestable_id !== $challenge->id) {
            return back()->with('error', 'Invalid join request.');
        }

        if ($joinRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        DB::transaction(function () use ($challenge, $joinRequest) {
            $challenge->opponent_id = $joinRequest->requester_id;
            $challenge->status = 'accepted';
            $challenge->save();

            $joinRequest->status = 'accepted';
            $joinRequest->save();

            $challenge->joinRequests()
                ->where('id', '!=', $joinRequest->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            GameMatch::create([
                'player1_id' => $challenge->challenger_id,
                'player2_id' => $joinRequest->requester_id,
                'challenge_id' => $challenge->id,
                'status' => 'scheduled',
                'match_date' => now()->addDays(1),
            ]);

            Notification::create([
                'user_id' => $joinRequest->requester_id,
                'title' => 'Challenge Accepted',
                'message' => Auth::user()->name . ' accepted your request to join the challenge!',
                'type' => 'challenge',
                'related_user_id' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Challenge accepted and match created.');
    }

    public function rejectRequest(Challenge $challenge, JoinRequest $joinRequest)
    {
        if (Auth::id() !== $challenge->challenger_id) {
            return back()->with('error', 'Unauthorized');
        }

        if ($joinRequest->requestable_type !== Challenge::class || $joinRequest->requestable_id !== $challenge->id) {
            return back()->with('error', 'Invalid join request.');
        }

        $joinRequest->status = 'rejected';
        $joinRequest->save();

        return back()->with('success', 'Join request rejected.');
    }

    public function accept(Challenge $challenge)
    {
        if (Auth::id() !== $challenge->opponent_id) {
            return back()->with('error', 'Unauthorized');
        }

        if ($challenge->status !== 'pending') {
            return back()->with('error', 'Challenge is no longer available');
        }

        $challenge->status = 'accepted';
        $challenge->save();

        $match = GameMatch::create([
            'player1_id' => $challenge->challenger_id,
            'player2_id' => $challenge->opponent_id,
            'challenge_id' => $challenge->id,
            'status' => 'scheduled',
            'match_date' => now()->addDays(1),
        ]);

        Notification::create([
            'user_id' => $challenge->challenger_id,
            'title' => 'Challenge Accepted',
            'message' => $challenge->opponent->name . ' accepted your challenge!',
            'type' => 'challenge',
            'related_user_id' => $challenge->opponent_id,
        ]);

        return redirect()->route('matches.show', $match->id)->with('success', 'Challenge accepted! Match created.');
    }

    public function reject(Challenge $challenge)
    {
        if (Auth::id() !== $challenge->opponent_id) {
            return back()->with('error', 'Unauthorized');
        }

        $challenge->status = 'rejected';
        $challenge->save();

        Notification::create([
            'user_id' => $challenge->challenger_id,
            'title' => 'Challenge Rejected',
            'message' => $challenge->opponent->name . ' rejected your challenge.',
            'type' => 'challenge',
            'related_user_id' => $challenge->opponent_id,
        ]);

        return redirect()->route('challenges.index')->with('success', 'Challenge rejected.');
    }

    private function decorateChallenge(Challenge $challenge, string $context, int $index)
    {
        $challenge->arena_description = $challenge->message ?: 'Looking for intermediate players for a friendly 3-set match this weekend at Central Court';
        $challenge->arena_time_limit = $challenge->expires_at ? $this->formatCountdown($challenge->expires_at) : '48h window';
        $challenge->arena_required_level = $challenge->opponent?->rank ?? $challenge->challenger?->rank ?? 'Intermediate';
        $challenge->arena_location = 'Central Court';
        $challenge->arena_countdown = $challenge->expires_at
            ? $challenge->expires_at->diffForHumans(now(), true) . ' left'
            : 'Ends soon';
        $challenge->arena_context = $context;
        $challenge->arena_priority = $index === 0 ? 'Featured challenge' : 'Active challenge';

        return $challenge;
    }

    private function buildLeaderboard()
    {
        return collect([
            ['id' => null, 'name' => 'CourtKings', 'rank' => 'Beast', 'elo_rating' => 2074],
            ['id' => null, 'name' => 'SmashPro', 'rank' => 'Advanced', 'elo_rating' => 2012],
            ['id' => null, 'name' => 'NetNinjas', 'rank' => 'Advanced', 'elo_rating' => 1916],
            ['id' => null, 'name' => 'HanoiBirdies', 'rank' => 'Advanced', 'elo_rating' => 1845],
            ['id' => null, 'name' => 'SaigonSmashers', 'rank' => 'Intermediate', 'elo_rating' => 1798],
            ['id' => null, 'name' => 'WeekendWarriors', 'rank' => 'Intermediate', 'elo_rating' => 1640],
            ['id' => null, 'name' => 'ShuttleKing', 'rank' => 'Intermediate', 'elo_rating' => 1588],
            ['id' => null, 'name' => 'meowhunterz', 'rank' => 'Beginner', 'elo_rating' => 1200],
        ])->map(function (array $player) {
            $player['badge_class'] = $this->rankBadgeClass($player['rank']);
            $player['profile_url'] = null;
            return (object) $player;
        });
    }

    private function buildSampleOpenChallenges()
    {
        return collect([
            (object) [
                'id' => 'sample-open-1',
                'challenger_id' => null,
                'challenger' => (object) ['name' => 'meowhunterz'],
                'status' => 'open',
                'arena_description' => 'meowhunterz looking for intermediate players for a friendly 3-set match this weekend at Central Court',
                'arena_priority' => 'Featured challenge',
                'arena_time_limit' => '6d 3h left',
                'arena_required_level' => 'Intermediate',
                'arena_location' => 'Central Court',
                'arena_countdown' => '6d 3h left',
                'expires_at' => now()->addDays(6)->addHours(3),
                'joinRequests' => collect(),
            ],
            (object) [
                'id' => 'sample-open-2',
                'challenger_id' => null,
                'challenger' => (object) ['name' => 'ShuttleKing'],
                'status' => 'open',
                'arena_description' => 'ShuttleKing seeking beginner players for doubles practice session at Downtown Court',
                'arena_priority' => 'Active challenge',
                'arena_time_limit' => '1d 19h left',
                'arena_required_level' => 'Beginner',
                'arena_location' => 'Downtown Court',
                'arena_countdown' => '1d 19h left',
                'expires_at' => now()->addDays(1)->addHours(19),
                'joinRequests' => collect(),
            ],
        ]);
    }

    private function rankBadgeClass(string $rank): string
    {
        return match (strtolower($rank)) {
            'beginner' => 'beginner',
            'intermediate' => 'silver',
            'advanced' => 'gold',
            'beast' => 'diamond',
            default => 'silver',
        };
    }

    private function formatCountdown(Carbon $expiresAt): string
    {
        $remaining = now()->diffInMinutes($expiresAt, false);

        if ($remaining <= 0) {
            return '0m left';
        }

        $days = intdiv($remaining, 1440);
        $hours = intdiv($remaining % 1440, 60);
        $minutes = $remaining % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . 'd';
        }

        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }

        if ($minutes > 0 && count($parts) < 2) {
            $parts[] = $minutes . 'm';
        }

        if (empty($parts)) {
            $parts[] = 'less than 1h';
        }

        return implode(' ', $parts) . ' left';
    }
}
