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

        $stats = [
            'total' => $history->count(),
            'pending' => $history->where('status', 'pending')->count(),
            'won' => $history->where('status', 'won')->count(),
            'lost' => $history->where('status', 'lost')->count(),
            'coins' => $user->virtual_coins,
            'wagered' => (int) $history->sum('amount'),
            'payout' => (int) $history->sum('payout'),
        ];

        $favoritePicks = $history
            ->groupBy('bet_on_user_id')
            ->map(fn ($bets) => $bets->count())
            ->sortDesc()
            ->take(3);

        return view('bets.index', compact('history', 'stats', 'favoritePicks'));
    }

    public function show($id)
    {
        $bet = Bet::with(['gameMatch.player1', 'gameMatch.player2', 'betOnUser', 'user'])->findOrFail($id);

        return view('bets.show', ['bet' => $bet]);
    }

    public function slip(GameMatch $match)
    {
        $match->load(['player1', 'player2', 'winner']);

        $betSlip = $this->service->getBetSlipData($match);

        return view('bets.slip', compact('match', 'betSlip'));
    }
}
