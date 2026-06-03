<?php

namespace App\Http\Controllers;

use App\Models\GameMatch;
use App\Models\Bet;
use App\Models\JoinRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\EloService;
use Illuminate\Http\Request;
use App\Http\Requests\PlaceBetRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = $request->only(['location', 'skill_level', 'date']);

        $applyFilters = function ($query) use ($filters) {
            $query->when(!empty($filters['location']), function ($locationQuery) use ($filters) {
                $locationQuery->where('location', 'like', '%' . $filters['location'] . '%');
            })
            ->when(!empty($filters['date']), function ($dateQuery) use ($filters) {
                if ($filters['date'] === 'today') {
                    $dateQuery->whereDate('match_date', now()->toDateString());
                } elseif ($filters['date'] === 'tomorrow') {
                    $dateQuery->whereDate('match_date', now()->addDay()->toDateString());
                } elseif ($filters['date'] === 'weekend') {
                    $dateQuery->whereDate('match_date', '>=', now()->next('Saturday')->toDateString())
                              ->whereDate('match_date', '<=', now()->next('Sunday')->addDay()->toDateString());
                }
            })
            ->when(!empty($filters['skill_level']), function ($skillQuery) use ($filters) {
                $skillQuery->where(function ($rankQuery) use ($filters) {
                    $rankQuery->whereHas('player1', function ($playerQuery) use ($filters) {
                        $playerQuery->where('rank', $filters['skill_level']);
                    })->orWhereHas('player2', function ($playerQuery) use ($filters) {
                        $playerQuery->where('rank', $filters['skill_level']);
                    });
                });
            });

            return $query;
        };

        $openMatches = $applyFilters(GameMatch::with(['player1', 'joinRequests.requester'])
            ->where('status', 'open')
            ->where('player1_id', '!=', $user->id)
            ->where('match_date', '>=', now())
        )
            ->orderBy('match_date')
            ->limit(8)
            ->get()
            ->map(fn($match) => $this->decorateMatch($match));

        $upcomingMatches = $applyFilters(GameMatch::with(['player1', 'player2'])
            ->where(function ($query) use ($user) {
                $query->where('player1_id', $user->id)->orWhere('player2_id', $user->id);
            })
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->whereNotNull('player2_id')
            ->where('match_date', '>=', now())
        )
            ->orderBy('match_date')
            ->limit(8)
            ->get();

        $completedMatches = $applyFilters(GameMatch::with(['player1', 'player2', 'winner'])
            ->where(function ($query) use ($user) {
                $query->where('player1_id', $user->id)->orWhere('player2_id', $user->id);
            })
            ->where('status', 'completed')
        )
            ->latest()
            ->limit(8)
            ->get();

        if ($completedMatches->isEmpty()) {
            $completedMatches = collect([
                (object) [
                    'id' => 'sample-completed-1',
                    'player1' => (object) ['name' => 'meowhunterz'],
                    'player2' => (object) ['name' => 'HanoiBirdies'],
                    'player1_score' => 21,
                    'player2_score' => 15,
                    'winner_id' => 1,
                    'player1_id' => 1,
                    'player2_id' => 2,
                    'location' => 'Central Court',
                    'match_date' => now()->subDays(3),
                ],
                (object) [
                    'id' => 'sample-completed-2',
                    'player1' => (object) ['name' => 'meowhunterz'],
                    'player2' => (object) ['name' => 'ShuttleKing'],
                    'player1_score' => 18,
                    'player2_score' => 21,
                    'winner_id' => 2,
                    'player1_id' => 1,
                    'player2_id' => 2,
                    'location' => 'Downtown Arena',
                    'match_date' => now()->subDays(5),
                ],
            ]);
        }

        return view('matches.index', compact('upcomingMatches', 'completedMatches', 'openMatches', 'filters'));
    }

    public function quickMatch()
    {
        $user = Auth::user();
        
        $availablePlayer = User::where('id', '!=', $user->id)
            ->orderBy(DB::raw('ABS(elo_rating - ' . $user->elo_rating . ')'))
            ->first();

        if (!$availablePlayer) {
            return back()->with('error', 'No players available for quick match. Try again later!');
        }

        $sampleLocations = [
            'Central Sports Complex, Court 3',
            'Downtown Badminton Club',
            'Westside Arena',
        ];
        $location = $sampleLocations[array_rand($sampleLocations)];

        $match = GameMatch::create([
            'player1_id' => $user->id,
            'player2_id' => $availablePlayer->id,
            'status' => 'scheduled',
            'match_date' => Carbon::now()->addHours(rand(2, 48)),
            'location' => $location,
        ]);

        Notification::create([
            'user_id' => $availablePlayer->id,
            'title' => 'Quick Match Request',
            'message' => "{$user->name} has scheduled a quick match with you!",
            'type' => 'match_request',
        ]);

        return redirect()->route('matches.show', $match->id)
            ->with('success', 'Quick match created! An opponent has been notified.');
    }

    private function decorateMatch($match)
    {
        $p1Rank = $match->player1?->rank ?? 'Beginner';
        $p2Rank = $match->player2?->rank ?? 'Unknown';

        $match->arena_location = $match->location ?? 'Court TBD';
        $match->arena_time = $this->formatMatchTime($match->match_date);
        $match->arena_skill = $p1Rank;
        $match->arena_badge_class = $this->rankBadgeClass($p1Rank);

        return $match;
    }

    private function buildSampleMatches()
    {
        return collect([
            (object)[
                'id' => 'sample-1',
                'player1_id' => null,
                'player2_id' => null,
                'player1' => (object)['id' => null, 'name' => 'Alex Chen', 'rank' => 'Intermediate'],
                'player2' => null,
                'status' => 'open',
                'location' => 'Central Sports Complex, Court 3',
                'match_date' => Carbon::now()->setTime(18, 0),
                'arena_location' => 'Central Sports Complex, Court 3',
                'arena_time' => 'Today 6PM',
                'arena_skill' => 'Intermediate',
                'arena_badge_class' => 'silver',
                'joinRequests' => collect([]),
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-2',
                'player1_id' => null,
                'player2_id' => null,
                'player1' => (object)['id' => null, 'name' => 'Jordan Smith', 'rank' => 'Beginner'],
                'player2' => null,
                'status' => 'open',
                'location' => 'Downtown Badminton Club',
                'match_date' => Carbon::now()->addDay()->setTime(16, 0),
                'arena_location' => 'Downtown Badminton Club',
                'arena_time' => 'Tomorrow 4PM',
                'arena_skill' => 'Beginner',
                'arena_badge_class' => 'beginner',
                'joinRequests' => collect([]),
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-3',
                'player1_id' => null,
                'player2_id' => null,
                'player1' => (object)['id' => null, 'name' => 'Morgan Lee', 'rank' => 'Advanced'],
                'player2' => null,
                'status' => 'open',
                'location' => 'Westside Arena',
                'match_date' => Carbon::now()->next('Saturday')->setTime(14, 0),
                'arena_location' => 'Westside Arena',
                'arena_time' => 'This Weekend',
                'arena_skill' => 'Advanced',
                'arena_badge_class' => 'gold',
                'joinRequests' => collect([]),
                'is_sample' => true,
            ],
        ]);
    }

    private function rankBadgeClass($rank)
    {
        return match(strtolower($rank)) {
            'beginner' => 'beginner',
            'intermediate' => 'silver',
            'advanced' => 'gold',
            'professional', 'beast' => 'diamond',
            default => 'beginner',
        };
    }

    private function formatMatchTime($date)
    {
        $today = now()->toDateString();
        $matchDate = $date->toDateString();
        $nextSat = now()->next('Saturday')->toDateString();
        $nextSun = now()->next('Sunday')->toDateString();

        if ($matchDate === $today) {
            return 'Today ' . $date->format('g\A');
        } elseif ($matchDate === now()->addDay()->toDateString()) {
            return 'Tomorrow ' . $date->format('g\A');
        } elseif ($matchDate >= $nextSat && $matchDate <= $nextSun) {
            return 'This Weekend';
        } else {
            return $date->format('M d');
        }
    }

    public function show(GameMatch $match)
    {
        $match->load(['player1', 'player2', 'winner', 'joinRequests.requester']);
        $betService = app(\App\Services\BetService::class);
        $odds = $betService->getMatchOdds($match);
        $betInsights = $betService->getMatchInsights($match);
        $betSlip = $betService->getBetSlipData($match);

        return view('matches.show', compact('match', 'odds', 'betInsights', 'betSlip'));
    }

    public function create()
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderBy('elo_rating', 'desc')
            ->paginate(20);

        return view('matches.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'player2_id' => 'nullable|exists:users,id|not_in:' . Auth::id(),
            'match_date' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
        ]);

        $isOpenMatch = empty($validated['player2_id']);

        $match = GameMatch::create([
            'player1_id' => Auth::id(),
            'player2_id' => $validated['player2_id'] ?? null,
            'status' => $isOpenMatch ? 'open' : 'scheduled',
            'match_date' => $validated['match_date'],
            'location' => $validated['location'] ?? null,
        ]);

        return redirect()->route('matches.show', $match->id)->with('success', $isOpenMatch ? 'Open match created! Players can request to join.' : 'Match created!');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
        ]);

        $match = GameMatch::create([
            'player1_id' => Auth::id(),
            'player2_id' => null,
            'status' => 'open',
            'match_date' => Carbon::now()->addMinutes(30),
            'location' => $validated['location'] ?? 'Community Court',
        ]);

        return redirect()->route('matches.show', $match->id)
            ->with('success', 'Quick Match created in under 30 seconds! Share it so others can request to join.');
    }

    public function requestJoin(GameMatch $match)
    {
        if (!$match->isOpen()) {
            return back()->with('error', 'This match is not open for join requests.');
        }

        if (Auth::id() === $match->player1_id) {
            return back()->with('error', 'Creators cannot request to join their own match.');
        }

        if ($match->player2_id) {
            return back()->with('error', 'This match already has a player.');
        }

        if ($match->joinRequests()->where('requester_id', Auth::id())->where('status', 'pending')->exists()) {
            return back()->with('error', 'You already requested to join this match.');
        }

        JoinRequest::create([
            'requestable_type' => GameMatch::class,
            'requestable_id' => $match->id,
            'requester_id' => Auth::id(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Join request sent.');
    }

    public function acceptRequest(GameMatch $match, JoinRequest $joinRequest)
    {
        if (Auth::id() !== $match->player1_id) {
            return back()->with('error', 'Unauthorized');
        }

        if ($joinRequest->requestable_type !== GameMatch::class || $joinRequest->requestable_id !== $match->id) {
            return back()->with('error', 'Invalid join request.');
        }

        if ($joinRequest->status !== 'pending') {
            return back()->with('error', 'This request is no longer pending.');
        }

        if ($match->player2_id || !$match->isOpen()) {
            return back()->with('error', 'This match already has a player.');
        }

        DB::transaction(function () use ($match, $joinRequest) {
            $match->player2_id = $joinRequest->requester_id;
            $match->status = 'scheduled';
            $match->save();

            $joinRequest->status = 'accepted';
            $joinRequest->save();

            $match->joinRequests()
                ->where('id', '!=', $joinRequest->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);

            Notification::create([
                'user_id' => $joinRequest->requester_id,
                'title' => 'Join Request Accepted',
                'message' => Auth::user()->name . ' accepted your request to join the match.',
                'type' => 'match',
                'related_user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('matches.show', $match->id)->with('success', 'Player added to match.');
    }

    public function rejectRequest(GameMatch $match, JoinRequest $joinRequest)
    {
        if (Auth::id() !== $match->player1_id) {
            return back()->with('error', 'Unauthorized');
        }

        if ($joinRequest->requestable_type !== GameMatch::class || $joinRequest->requestable_id !== $match->id) {
            return back()->with('error', 'Invalid join request.');
        }

        $joinRequest->status = 'rejected';
        $joinRequest->save();

        return back()->with('success', 'Join request rejected.');
    }

    public function startMatch(GameMatch $match)
    {
        if (Auth::id() !== $match->player1_id && Auth::id() !== $match->player2_id) {
            return back()->with('error', 'Unauthorized');
        }

        $match->status = 'in_progress';
        $match->save();

        return redirect()->route('matches.show', $match->id)->with('success', 'Match started!');
    }

    public function submitResult(GameMatch $match, Request $request)
    {
        if (Auth::id() !== $match->player1_id && Auth::id() !== $match->player2_id) {
            return back()->with('error', 'Unauthorized');
        }

        $validated = $request->validate([
            'player1_score' => 'required|integer|min:0',
            'player2_score' => 'required|integer|min:0',
            'winner_id' => 'required|in:' . $match->player1_id . ',' . $match->player2_id,
        ]);

        DB::transaction(function () use ($match, $validated) {
            $match->player1_score = $validated['player1_score'];
            $match->player2_score = $validated['player2_score'];
            $match->winner_id = $validated['winner_id'];
            $match->status = 'completed';
            $match->save();

            EloService::updatePlayerRatings($match);

            // Settle bets using BetService
            app(\App\Services\BetService::class)->settleBetsAfterMatch($match);

            Notification::create([
                'user_id' => $match->player1_id === Auth::id() ? $match->player2_id : $match->player1_id,
                'title' => 'Match Result',
                'message' => Auth::user()->name . ' submitted match result.',
                'type' => 'result',
                'related_user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('matches.show', $match->id)->with('success', 'Match result submitted!');
    }

    public function placeBet(GameMatch $match, PlaceBetRequest $request)
    {
        $validated = $request->validated();

        try {
            app(\App\Services\BetService::class)->placeBet(Auth::user(), $match, (int)$validated['amount'], (int)$validated['bet_on_user_id']);
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('success', 'Bet placed!');
    }
}
