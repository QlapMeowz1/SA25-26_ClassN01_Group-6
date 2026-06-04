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
            ->where('match_date', '>=', now())
        )
            ->orderBy('match_date')
            ->limit(8)
            ->get()
            ->map(fn($match) => $this->decorateMatch($match));

        if ($openMatches->count() < 6 && empty(array_filter($filters))) {
            $openMatches = $this->mergeSampleMatches($openMatches, $this->buildSampleMatches(), 6);
        }

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

        if ($upcomingMatches->count() < 5 && empty(array_filter($filters))) {
            $upcomingMatches = $this->mergeSampleMatches($upcomingMatches, $this->buildSampleUpcomingMatches(), 5);
        }

        $completedMatches = $applyFilters(GameMatch::with(['player1', 'player2', 'winner'])
            ->where(function ($query) use ($user) {
                $query->where('player1_id', $user->id)->orWhere('player2_id', $user->id);
            })
            ->where('status', 'completed')
        )
            ->latest()
            ->limit(8)
            ->get();

        if ($completedMatches->count() < 6 && empty(array_filter($filters))) {
            $completedMatches = $this->mergeSampleMatches($completedMatches, $this->buildSampleCompletedMatches(), 6);
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
            (object)[
                'id' => 'sample-4',
                'player1_id' => null,
                'player2_id' => null,
                'player1' => (object)['id' => null, 'name' => 'Sita Patil', 'rank' => 'Professional'],
                'player2' => null,
                'status' => 'open',
                'location' => 'Bangalore Indoor Stadium',
                'match_date' => Carbon::now()->addHours(5),
                'arena_location' => 'Bangalore Indoor Stadium',
                'arena_time' => 'Today 8PM',
                'arena_skill' => 'Professional',
                'arena_badge_class' => 'diamond',
                'joinRequests' => collect([]),
                'is_sample' => true,
            ],
            (object)[
                'id' => 'sample-5',
                'player1_id' => null,
                'player2_id' => null,
                'player1' => (object)['id' => null, 'name' => 'Vikram Nair', 'rank' => 'Advanced'],
                'player2' => null,
                'status' => 'open',
                'location' => 'District 7 Arena',
                'match_date' => Carbon::now()->addDays(2)->setTime(19, 30),
                'arena_location' => 'District 7 Arena',
                'arena_time' => 'Fri 7PM',
                'arena_skill' => 'Advanced',
                'arena_badge_class' => 'gold',
                'joinRequests' => collect([]),
                'is_sample' => true,
            ],
        ]);
    }

    private function buildSampleUpcomingMatches()
    {
        return collect([
            $this->sampleMatch('sample-upcoming-1', 'Priya Sharma', 'Ananya Roy', 'Women\'s Singles', 'scheduled', 'State Open Court 1', now()->addHours(3), 'Advanced'),
            $this->sampleMatch('sample-upcoming-2', 'Dev Khanna', 'Rohan Mehta', 'Men\'s Singles', 'in_progress', 'Club League Court 2', now()->addHours(1), 'Professional'),
            $this->sampleMatch('sample-upcoming-3', 'Sita Patil', 'Meera Joshi', 'Women\'s Singles', 'scheduled', 'Pune Aces Hall', now()->addDay()->setTime(10, 30), 'Intermediate'),
            $this->sampleMatch('sample-upcoming-4', 'Layla Hassan', 'Divya Chopra', 'Women\'s Singles', 'scheduled', 'Hyderabad Dome', now()->addDays(2)->setTime(15, 0), 'Advanced'),
            $this->sampleMatch('sample-upcoming-5', 'Vikram Nair', 'Arun Tiwari', 'Men\'s Singles', 'scheduled', 'Jaipur Jets Court', now()->addDays(3)->setTime(18, 0), 'Intermediate'),
        ]);
    }

    private function buildSampleCompletedMatches()
    {
        return collect([
            $this->sampleMatch('sample-completed-1', 'meowhunterz', 'HanoiBirdies', 'Community Match', 'completed', 'Central Court', now()->subDays(3), 'Beginner', 21, 15, 1),
            $this->sampleMatch('sample-completed-2', 'meowhunterz', 'ShuttleKing', 'Open Ladder', 'completed', 'Downtown Arena', now()->subDays(5), 'Intermediate', 18, 21, 2),
            $this->sampleMatch('sample-completed-3', 'Priya Sharma', 'Sita Patil', 'State Open', 'completed', 'Court 1', now()->subDays(2), 'Advanced', 21, 18, 1),
            $this->sampleMatch('sample-completed-4', 'Dev Khanna', 'Vikram Nair', 'Club League', 'completed', 'Court 2', now()->subDays(4), 'Professional', 21, 19, 1),
            $this->sampleMatch('sample-completed-5', 'Ananya Roy', 'Meera Joshi', 'Friendly', 'completed', 'Court 5', now()->subDays(6), 'Intermediate', 16, 21, 2),
            $this->sampleMatch('sample-completed-6', 'Rohan Mehta', 'Arun Tiwari', 'Weekend Ladder', 'completed', 'Court 3', now()->subWeek(), 'Advanced', 22, 20, 1),
        ]);
    }

    private function sampleMatch(string $id, string $p1, string $p2, string $label, string $status, string $location, Carbon $date, string $rank, ?int $score1 = null, ?int $score2 = null, ?int $winner = null)
    {
        return (object) [
            'id' => $id,
            'player1_id' => 1,
            'player2_id' => 2,
            'player1' => (object) ['id' => 1, 'name' => $p1, 'rank' => $rank],
            'player2' => (object) ['id' => 2, 'name' => $p2, 'rank' => $rank],
            'status' => $status,
            'location' => $location,
            'match_date' => $date,
            'player1_score' => $score1,
            'player2_score' => $score2,
            'winner_id' => $winner === 1 ? 1 : ($winner === 2 ? 2 : null),
            'arena_label' => $label,
            'is_sample' => true,
        ];
    }

    private function mergeSampleMatches($realMatches, $sampleMatches, int $limit)
    {
        $existing = $realMatches->map(fn ($match) => strtolower(($match->player1?->name ?? '') . ' vs ' . ($match->player2?->name ?? '')))->all();

        $samples = $sampleMatches->reject(function ($match) use ($existing) {
            return in_array(strtolower(($match->player1?->name ?? '') . ' vs ' . ($match->player2?->name ?? '')), $existing, true);
        });

        return $realMatches->concat($samples)->take($limit)->values();
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

        $defaultMatchDate = now()->addHour()->format('Y-m-d\TH:i');
        $minMatchDate = now()->addMinutes(5)->format('Y-m-d\TH:i');

        return view('matches.create', compact('users', 'defaultMatchDate', 'minMatchDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'player2_id' => 'nullable|exists:users,id|not_in:' . Auth::id(),
            'match_date' => 'required|date|after_or_equal:' . now()->addMinutes(5)->toDateTimeString(),
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

        return redirect()
            ->route('matches.show', $match->id)
            ->with('success', $isOpenMatch ? 'Open match created! Players can request to join.' : 'Match created!');
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

    public function updateOdds(GameMatch $match, Request $request)
    {
        if (!$this->canManageOdds($match)) {
            abort(403);
        }

        if (!$match->player1_id || !$match->player2_id) {
            return back()->with('error', 'Odds can be adjusted after both players are confirmed.');
        }

        if ($match->status === 'completed') {
            return back()->with('error', 'Odds cannot be changed after the match is completed.');
        }

        $validated = $request->validate([
            'player1_odds' => ['required', 'numeric', 'min:1.01', 'max:50'],
            'player2_odds' => ['required', 'numeric', 'min:1.01', 'max:50'],
        ]);

        $match->update([
            'player1_odds' => round((float) $validated['player1_odds'], 2),
            'player2_odds' => round((float) $validated['player2_odds'], 2),
            'odds_updated_by' => Auth::id(),
            'odds_updated_at' => now(),
        ]);

        return back()->with('success', 'Betting odds updated.');
    }

    public function deleteOdds(GameMatch $match)
    {
        if (!$this->canManageOdds($match)) {
            abort(403);
        }

        $match->update([
            'player1_odds' => null,
            'player2_odds' => null,
            'odds_updated_by' => Auth::id(),
            'odds_updated_at' => now(),
        ]);

        return back()->with('success', 'Manual odds removed. The system odds are active again.');
    }

    private function canManageOdds(GameMatch $match): bool
    {
        $user = Auth::user();

        return $user && ($user->isAdmin() || (int) $match->player1_id === (int) $user->id);
    }

    public function placeBet(GameMatch $match, PlaceBetRequest $request)
    {
        $validated = $request->validated();

        try {
            $bet = app(\App\Services\BetService::class)->placeBet(Auth::user(), $match, (int)$validated['amount'], (int)$validated['bet_on_user_id']);
        } catch (\Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('bets.show', $bet->id)->with('success', 'Bet placed!');
    }
}
