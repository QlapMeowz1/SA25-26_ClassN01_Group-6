<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BetService
{
    /** Place a bet for a user on a match */
    public function placeBet(User $user, GameMatch $match, int $amount, int $predictedWinnerId): Bet
    {
        return DB::transaction(function () use ($user, $match, $amount, $predictedWinnerId) {
            if ($user->virtual_coins < $amount) {
                throw new \InvalidArgumentException('Insufficient coins');
            }

            $bet = Bet::create([
                'user_id' => $user->id,
                'match_id' => $match->id,
                'bet_on_user_id' => $predictedWinnerId,
                'amount' => $amount,
                'status' => 'pending',
            ]);

            $user->virtual_coins -= $amount;
            $user->save();

            return $bet;
        });
    }

    /** Calculate simple odds for a match (placeholder) */
    public function getMatchOdds(GameMatch $match): array
    {
        // Basic implementation: equal odds if both players set, otherwise 1:1
        $p1 = 1.0; $p2 = 1.0;
        if ($match->player1 && $match->player2) {
            // crude odds based on ELO
            $a = $match->player1->elo_rating ?? 1200;
            $b = $match->player2->elo_rating ?? 1200;
            $exp = ($b - $a) / 400;
            $prob1 = 1 / (1 + pow(10, $exp));
            $prob2 = 1 - $prob1;
            $p1 = max(1.01, round(1 / max(0.01, $prob1), 2));
            $p2 = max(1.01, round(1 / max(0.01, $prob2), 2));
        }

        return [
            'player1_odds' => $p1,
            'player2_odds' => $p2,
        ];
    }

    /** Settle bets after match completion */
    public function settleBetsAfterMatch(GameMatch $match): void
    {
        DB::transaction(function () use ($match) {
            $bets = Bet::where('match_id', $match->id)->get();
            foreach ($bets as $bet) {
                // reuse existing model settle if present
                if (method_exists($bet, 'settle')) {
                    $bet->settle();
                } else {
                    if ($match->isCompleted() && $match->winner_id === $bet->bet_on_user_id) {
                        $bet->status = 'won';
                        $bet->payout = ($bet->amount * 2);
                    } else {
                        $bet->status = 'lost';
                        $bet->payout = 0;
                    }
                    $bet->save();
                }

                if ($bet->status === 'won') {
                    $user = $bet->user;
                    $user->virtual_coins += $bet->payout;
                    $user->save();
                }
            }
        });
    }

    /** Get user's bet history */
    public function getUserBetHistory(User $user)
    {
        return Bet::with('gameMatch', 'betOnUser')->where('user_id', $user->id)->latest()->get();
    }
}
