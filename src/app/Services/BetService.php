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

    /**
     * Build a sportsbook-style insight payload for a match.
     * Returns odds, implied probabilities, risk labels, and estimated payout.
     */
    public function getMatchInsights(GameMatch $match, ?int $selectedUserId = null, ?int $amount = null): array
    {
        $odds = $this->getMatchOdds($match);

        $player1Probability = isset($odds['player1_probability']) ? (float) $odds['player1_probability'] : 0.5;
        $player2Probability = isset($odds['player2_probability']) ? (float) $odds['player2_probability'] : 0.5;

        // Normalize probabilities to 0-1 even if the odds helper returns percentages.
        if ($player1Probability > 1) {
            $player1Probability = $player1Probability / 100;
        }
        if ($player2Probability > 1) {
            $player2Probability = $player2Probability / 100;
        }

        $selectedProbability = null;
        $selectedOdds = null;
        $riskLevel = null;
        $riskTone = null;
        $expectedReturn = null;

        if ($selectedUserId === $match->player1_id) {
            $selectedProbability = $player1Probability;
            $selectedOdds = $odds['player1_odds'] ?? 1;
        } elseif ($selectedUserId === $match->player2_id) {
            $selectedProbability = $player2Probability;
            $selectedOdds = $odds['player2_odds'] ?? 1;
        }

        if ($selectedProbability !== null) {
            if ($selectedProbability >= 0.68) {
                $riskLevel = 'Low risk';
                $riskTone = 'emerald';
            } elseif ($selectedProbability >= 0.45) {
                $riskLevel = 'Balanced';
                $riskTone = 'amber';
            } else {
                $riskLevel = 'High risk';
                $riskTone = 'rose';
            }

            if ($amount !== null && $selectedOdds !== null) {
                $expectedReturn = (int) round($amount * $selectedOdds);
            }
        }

        return [
            'player1_odds' => $odds['player1_odds'],
            'player2_odds' => $odds['player2_odds'],
            'player1_probability' => $player1Probability,
            'player2_probability' => $player2Probability,
            'selected_probability' => $selectedProbability,
            'selected_odds' => $selectedOdds,
            'risk_level' => $riskLevel,
            'risk_tone' => $riskTone,
            'expected_return' => $expectedReturn,
        ];
    }

    /**
     * Build sportsbook-style slip data for both players on a match.
     */
    public function getBetSlipData(GameMatch $match, ?int $selectedUserId = null, ?int $amount = null): array
    {
        $match->loadMissing(['player1', 'player2']);

        $insights = $this->getMatchInsights($match, $selectedUserId, $amount);
        $communityCounts = Bet::where('match_id', $match->id)
            ->select('bet_on_user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('bet_on_user_id')
            ->pluck('total', 'bet_on_user_id')
            ->toArray();

        $communityTotal = array_sum($communityCounts);

        $players = [];

        foreach ([$match->player1, $match->player2] as $player) {
            if (!$player) {
                continue;
            }

            $playerOdds = $player->id === $match->player1_id
                ? ($insights['player1_odds'] ?? 1)
                : ($insights['player2_odds'] ?? 1);

            $probability = $player->id === $match->player1_id
                ? (float) ($insights['player1_probability'] ?? 50)
                : (float) ($insights['player2_probability'] ?? 50);

            if ($probability > 1) {
                $probability = $probability / 100;
            }

            $form = $this->getRecentFormScore($player);
            $communityPickCount = (int) ($communityCounts[$player->id] ?? 0);
            $communityPickRatio = $communityTotal > 0 ? round(($communityPickCount / $communityTotal) * 100) : 0;

            $confidence = (int) round(
                ($probability * 100 * 0.6)
                + (($form['score'] ?? 50) * 0.25)
                + ($communityPickRatio * 0.15)
            );

            if ($confidence >= 75) {
                $riskLevel = 'Low risk';
                $riskTone = 'emerald';
            } elseif ($confidence >= 55) {
                $riskLevel = 'Balanced';
                $riskTone = 'amber';
            } else {
                $riskLevel = 'High risk';
                $riskTone = 'rose';
            }

            $players[] = [
                'id' => $player->id,
                'name' => $player->name,
                'rank' => $player->rank,
                'elo' => $player->elo_rating,
                'odds' => $playerOdds,
                'probability' => round($probability * 100, 1),
                'form_score' => $form['score'],
                'form_label' => $form['label'],
                'form_tone' => $form['tone'],
                'community_pick_count' => $communityPickCount,
                'community_pick_ratio' => $communityPickRatio,
                'confidence' => $confidence,
                'risk_level' => $riskLevel,
                'risk_tone' => $riskTone,
                'expected_return' => $amount !== null ? (int) round($amount * $playerOdds) : null,
                'selected' => $selectedUserId === $player->id,
            ];
        }

        if ($selectedUserId === null && !empty($players)) {
            usort($players, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);
            $players[0]['selected'] = true;
        }

        return [
            'players' => $players,
            'odds' => $insights,
        ];
    }

    private function getRecentFormScore(User $user): array
    {
        $recentMatches = GameMatch::query()
            ->where('status', 'completed')
            ->where(function ($query) use ($user) {
                $query->where('player1_id', $user->id)
                    ->orWhere('player2_id', $user->id);
            })
            ->orderByDesc('match_date')
            ->limit(5)
            ->get();

        if ($recentMatches->isEmpty()) {
            return [
                'score' => 50,
                'label' => 'No recent form',
                'tone' => 'slate',
            ];
        }

        $wins = $recentMatches->filter(function (GameMatch $match) use ($user) {
            return (int) $match->winner_id === (int) $user->id;
        })->count();

        $losses = $recentMatches->count() - $wins;
        $score = (int) round(($wins / max(1, $recentMatches->count())) * 100);

        if ($score >= 75) {
            $label = 'Hot';
            $tone = 'emerald';
        } elseif ($score >= 50) {
            $label = 'Steady';
            $tone = 'amber';
        } else {
            $label = 'Cold';
            $tone = 'rose';
        }

        return [
            'score' => $score,
            'label' => $label . ' form',
            'tone' => $tone,
            'wins' => $wins,
            'losses' => $losses,
        ];
    }

    /** Calculate simple odds for a match (placeholder) */
    public function getMatchOdds(GameMatch $match): array
    {
        // Basic implementation: equal odds if both players set, otherwise 1:1.
        // We also return implied probabilities so the UI can show a professional betting summary.
        $p1 = 1.0;
        $p2 = 1.0;
        $prob1 = 0.5;
        $prob2 = 0.5;

        if ($match->player1 && $match->player2) {
            // Crude odds based on ELO.
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
            'player1_probability' => round($prob1 * 100, 1),
            'player2_probability' => round($prob2 * 100, 1),
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
        return Bet::with(['gameMatch.player1', 'gameMatch.player2', 'betOnUser'])->where('user_id', $user->id)->latest()->get();
    }
}
