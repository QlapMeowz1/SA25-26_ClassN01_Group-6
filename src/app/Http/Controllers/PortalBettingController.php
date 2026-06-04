<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\GameMatch;
use App\Services\BetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PortalBettingController extends Controller
{
    public function index(BetService $betService): JsonResponse
    {
        $user = auth()->user();

        $live = GameMatch::with(['player1', 'player2', 'bets'])
            ->where('status', 'in_progress')
            ->whereNotNull('player2_id')
            ->latest('match_date')
            ->limit(6)
            ->get()
            ->map(fn (GameMatch $match) => $this->marketPayload($match, $betService, true));

        $upcoming = GameMatch::with(['player1', 'player2', 'bets'])
            ->whereIn('status', ['scheduled', 'open'])
            ->whereNotNull('player2_id')
            ->where('match_date', '>=', now()->subDay())
            ->orderBy('match_date')
            ->limit(8)
            ->get()
            ->map(fn (GameMatch $match) => $this->marketPayload($match, $betService, false));

        $myBets = Bet::with(['gameMatch.player1', 'gameMatch.player2', 'betOnUser'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Bet $bet) => $this->ticketPayload($bet, $betService));

        return response()->json([
            'wallet' => (int) ($user->virtual_coins ?? 0),
            'live' => $live,
            'upcoming' => $upcoming,
            'myBets' => $myBets,
        ]);
    }

    public function store(Request $request, BetService $betService): JsonResponse
    {
        $data = $request->validate([
            'match_id' => ['required', 'integer', 'exists:matches,id'],
            'bet_on_user_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:10'],
        ]);

        $match = GameMatch::with(['player1', 'player2'])->findOrFail($data['match_id']);

        validator($data, [
            'bet_on_user_id' => [Rule::in([$match->player1_id, $match->player2_id])],
            'amount' => ['max:' . (int) (auth()->user()->virtual_coins ?? 0)],
        ])->validate();

        try {
            $bet = $betService->placeBet(
                auth()->user(),
                $match,
                (int) $data['amount'],
                (int) $data['bet_on_user_id']
            );
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        $bet->load(['gameMatch.player1', 'gameMatch.player2', 'betOnUser']);

        return response()->json([
            'wallet' => (int) auth()->user()->fresh()->virtual_coins,
            'ticket' => $this->ticketPayload($bet, $betService),
        ], 201);
    }

    private function marketPayload(GameMatch $match, BetService $betService, bool $isLive): array
    {
        $odds = $betService->getMatchOdds($match);
        $pool = (int) $match->bets->sum('amount');
        $p1Pool = (int) $match->bets->where('bet_on_user_id', $match->player1_id)->sum('amount');
        $p1pct = $pool > 0 ? (int) round(($p1Pool / $pool) * 100) : (int) round($odds['player1_probability'] ?? 50);

        return [
            'matchId' => $match->id,
            'id' => 'M-' . str_pad((string) $match->id, 4, '0', STR_PAD_LEFT),
            'p1Id' => $match->player1_id,
            'p2Id' => $match->player2_id,
            'p1' => $match->player1?->name ?? 'Player 1',
            'p2' => $match->player2?->name ?? 'Player 2',
            'score' => $isLive ? $this->scoreLabel($match) : null,
            'date' => $match->match_date?->format('M d · H:i') ?? 'TBD',
            'elapsed' => $match->match_date ? $match->match_date->diffForHumans(null, true) : 'live',
            'tournament' => Str::headline($match->status ?: 'match'),
            'court' => $match->location ?: 'Court TBD',
            'odds1' => (float) $odds['player1_odds'],
            'odds2' => (float) $odds['player2_odds'],
            'pool' => $pool,
            'bettors' => $match->bets->count(),
            'p1pct' => min(100, max(0, $p1pct)),
            'closesIn' => $match->match_date ? $match->match_date->diffForHumans(null, true) : 'TBD',
        ];
    }

    private function ticketPayload(Bet $bet, BetService $betService): array
    {
        $match = $bet->gameMatch;
        $odds = (float) $bet->odds;

        if (!$odds && $match) {
            $marketOdds = $betService->getMatchOdds($match);
            $odds = (int) $bet->bet_on_user_id === (int) $match->player1_id
                ? (float) $marketOdds['player1_odds']
                : (float) $marketOdds['player2_odds'];
        }

        $odds = $odds ?: 1;

        return [
            'id' => 'BT-' . str_pad((string) $bet->id, 3, '0', STR_PAD_LEFT),
            'match' => ($match?->player1?->name ?? 'Player 1') . ' vs ' . ($match?->player2?->name ?? 'Player 2'),
            'pick' => $bet->betOnUser?->name ?? 'Unknown',
            'amount' => (int) $bet->amount,
            'odds' => $odds,
            'potential' => (int) round($bet->amount * $odds),
            'status' => $this->ticketStatus($bet),
        ];
    }

    private function ticketStatus(Bet $bet): string
    {
        if ($bet->status === 'won') {
            return 'Won';
        }

        if ($bet->status === 'lost') {
            return 'Lost';
        }

        return $bet->gameMatch?->status === 'in_progress' ? 'Live' : 'Pending';
    }

    private function scoreLabel(GameMatch $match): string
    {
        if ($match->player1_score !== null || $match->player2_score !== null) {
            return 'Game · ' . ($match->player1_score ?? 0) . '-' . ($match->player2_score ?? 0);
        }

        return 'Live now';
    }
}
