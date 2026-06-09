<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\GameMatch;
use App\Services\BetService;

class BetController extends Controller
{
    protected $service;

    public function __construct(BetService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $user = auth()->user();
        $history = $this->service->getUserBetHistory($user);
        $pendingExposure = (int) $history->whereIn('status', ['pending', 'live'])->sum('amount');
        $settled = $history->whereIn('status', ['won', 'lost']);
        $avgStake = $history->count() > 0 ? (int) round($history->avg('amount')) : 0;

        $stats = [
            'total' => $history->count(),
            'pending' => $history->whereIn('status', ['pending', 'live'])->count(),
            'won' => $history->where('status', 'won')->count(),
            'lost' => $history->where('status', 'lost')->count(),
            'refunded' => $history->where('status', 'refunded')->count(),
            'coins' => $user->virtual_coins,
            'wagered' => (int) $history->sum('amount'),
            'payout' => (int) $history->sum('payout'),
            'pending_exposure' => $pendingExposure,
            'avg_stake' => $avgStake,
            'settled' => $settled->count(),
        ];

        $favoritePicks = $history
            ->loadMissing('betOnUser')
            ->groupBy('bet_on_user_id')
            ->map(fn ($bets) => [
                'name' => $bets->first()->betOnUser?->name ?? 'Unknown',
                'count' => $bets->count(),
                'won' => $bets->where('status', 'won')->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(3);

        $openMarkets = GameMatch::with(['player1', 'player2'])
            ->whereNotNull('player2_id')
            ->whereIn('status', ['open', 'scheduled', 'in_progress'])
            ->orderByRaw('match_date IS NULL')
            ->orderBy('match_date')
            ->take(4)
            ->get();

        $hotBetMarkets = GameMatch::with(['player1', 'player2', 'bets'])
            ->whereNotNull('player2_id')
            ->whereIn('status', ['open', 'scheduled', 'in_progress'])
            ->where(function ($query) {
                $query->whereNull('betting_status')
                    ->orWhereIn('betting_status', ['open', 'approved', 'locked']);
            })
            ->withSum('bets as pool_total', 'amount')
            ->orderByDesc('pool_total')
            ->orderBy('match_date')
            ->limit(6)
            ->get()
            ->map(function (GameMatch $match) {
                $pool = $this->service->getPoolData($match);
                $odds = $this->service->getMatchOdds($match);

                $match->pulse_pool = $pool['total_pool'];
                $match->pulse_player1_percent = $pool['percent_a'];
                $match->pulse_player2_percent = $pool['percent_b'];
                $match->pulse_player1_odds = (float) ($odds['player1_odds'] ?? 1);
                $match->pulse_player2_odds = (float) ($odds['player2_odds'] ?? 1);
                $match->pulse_bettors = $pool['bettor_count'];
                $match->pulse_state = $pool['market_state'] === 'locked' ? 'Locked' : 'Open';

                return $match;
            });

        return view('bets.index', compact('history', 'stats', 'favoritePicks', 'openMarkets', 'hotBetMarkets'));
    }

    public function show($id)
    {
        $bet = Bet::with(['gameMatch.player1', 'gameMatch.player2', 'betOnUser', 'user'])->findOrFail($id);

        return view('bets.show', ['bet' => $bet]);
    }

    public function slip(GameMatch $match)
    {
        $match->load(['player1', 'player2', 'winner']);

        if (!$match->player1_id || !$match->player2_id) {
            return redirect()
                ->route('matches.show', $match->id)
                ->with('error', 'Betting opens after the match has two confirmed players.');
        }

        if ($match->status === 'completed') {
            return redirect()
                ->route('matches.show', $match->id)
                ->with('error', 'Betting is closed for completed matches.');
        }

        $betSlip = $this->service->getBetSlipData($match);

        return view('bets.slip', compact('match', 'betSlip'));
    }
}
