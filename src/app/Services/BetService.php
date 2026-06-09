<?php

namespace App\Services;

use App\Events\PoolUpdated;
use App\Models\Bet;
use App\Models\GameMatch;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BetService
{
    public function __construct(private readonly WalletService $wallets)
    {
    }

    /** Place a bet for a user on a match */
    public function placeBet(User $user, GameMatch $match, int $amount, int $predictedWinnerId): Bet
    {
        return DB::transaction(function () use ($user, $match, $amount, $predictedWinnerId) {
            $match = GameMatch::whereKey($match->id)->lockForUpdate()->firstOrFail();
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (!$match->player1_id || !$match->player2_id) {
                throw new \InvalidArgumentException('Betting opens after the match has two confirmed players.');
            }

            if ($match->status === 'completed') {
                throw new \InvalidArgumentException('Betting is closed for completed matches.');
            }

            if (!$match->canAcceptBets()) {
                throw new \InvalidArgumentException('This betting market is not open.');
            }

            if (!in_array($predictedWinnerId, [(int) $match->player1_id, (int) $match->player2_id], true)) {
                throw new \InvalidArgumentException('Choose a valid player for this match.');
            }

            if ($user->virtual_coins < $amount) {
                throw new \InvalidArgumentException('Insufficient coins');
            }

            $odds = $this->getMatchOdds($match);
            $selectedOdds = $predictedWinnerId === (int) $match->player1_id
                ? $odds['player1_odds']
                : $odds['player2_odds'];

            $bet = Bet::create([
                'user_id' => $user->id,
                'match_id' => $match->id,
                'bet_on_user_id' => $predictedWinnerId,
                'amount' => $amount,
                'odds' => $selectedOdds,
                'status' => 'pending',
            ]);

            $this->wallets->change(
                $user,
                -$amount,
                'bet_stake',
                'bet-stake-' . $bet->id,
                'Stake locked for ' . $this->matchLabel($match),
                $bet,
                $user,
                ['match_id' => $match->id, 'odds' => $selectedOdds]
            );
            $user->refresh();

            $matchLabel = $this->matchLabel($match);
            $pickName = User::whereKey($predictedWinnerId)->value('name') ?? 'your pick';

            Notification::create([
                'user_id' => $user->id,
                'title' => 'Bet Confirmed',
                'message' => "Your {$amount} coin bet on {$pickName} for {$matchLabel} is confirmed at {$selectedOdds}x odds.",
                'type' => 'bet_placed',
                'related_user_id' => $predictedWinnerId,
                'target_url' => route('bets.show', $bet->id),
            ]);

            if ((int) $user->virtual_coins <= 500) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Low Wallet Balance',
                    'message' => "Your wallet balance is now {$user->virtual_coins} coins after betting on {$matchLabel}.",
                    'type' => 'wallet_low',
                    'related_user_id' => $predictedWinnerId,
                    'target_url' => route('matches.show', $match->id),
                ]);
            }

            $this->broadcastPoolUpdate($match);

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

    public function getPoolData(GameMatch $match): array
    {
        $match->loadMissing(['player1', 'player2', 'bets']);

        $playerOnePool = (float) $match->bets->where('bet_on_user_id', $match->player1_id)->sum('amount');
        $playerTwoPool = (float) $match->bets->where('bet_on_user_id', $match->player2_id)->sum('amount');
        $totalPool = $playerOnePool + $playerTwoPool;
        $percentA = $totalPool > 0 ? (int) round(($playerOnePool / $totalPool) * 100) : 50;
        $percentB = 100 - $percentA;

        return [
            'match_id' => (int) $match->id,
            'percent_a' => $percentA,
            'percent_b' => $percentB,
            'pool_a' => $playerOnePool,
            'pool_b' => $playerTwoPool,
            'total_pool' => $totalPool,
            'bettor_count' => $match->bets->pluck('user_id')->unique()->count(),
            'player_a' => $match->player1?->name ?? 'Player 1',
            'player_b' => $match->player2?->name ?? 'Player 2',
            'market_state' => $match->status === 'in_progress' || $match->betting_status === 'locked' ? 'locked' : ($match->betting_status ?? 'open'),
        ];
    }

    public function broadcastPoolUpdate(GameMatch $match, bool $force = false): void
    {
        $lockKey = 'broadcast_lock_match_' . $match->id;

        if (!$force && Cache::has($lockKey)) {
            return;
        }

        $match->refresh();
        broadcast(new PoolUpdated((int) $match->id, $this->getPoolData($match)));
        if (!$force) {
            Cache::put($lockKey, true, 2);
        }
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
        if ($match->player1_odds !== null && $match->player2_odds !== null) {
            $p1 = max(1.01, (float) $match->player1_odds);
            $p2 = max(1.01, (float) $match->player2_odds);
            $inverse1 = 1 / $p1;
            $inverse2 = 1 / $p2;
            $total = max(0.01, $inverse1 + $inverse2);

            return [
                'player1_odds' => $p1,
                'player2_odds' => $p2,
                'player1_probability' => round(($inverse1 / $total) * 100, 1),
                'player2_probability' => round(($inverse2 / $total) * 100, 1),
                'is_manual' => true,
            ];
        }

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
            'is_manual' => false,
        ];
    }

    /** Settle bets after match completion */
    public function settleBetsAfterMatch(GameMatch $match): void
    {
        DB::transaction(function () use ($match) {
            $match = GameMatch::with(['player1', 'player2'])
                ->whereKey($match->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$match->isCompleted() || !$match->winner_id) {
                throw new \LogicException('A confirmed winner is required before settling bets.');
            }

            $bets = Bet::with(['user', 'betOnUser'])
                ->where('match_id', $match->id)
                ->whereIn('status', ['pending', 'live'])
                ->lockForUpdate()
                ->get();

            foreach ($bets as $bet) {
                $settlementKey = 'match-' . $match->id . '-bet-' . $bet->id . '-settled';
                if ($bet->settlement_key || \App\Models\WalletTransaction::where('reference', $settlementKey)->exists()) {
                    continue;
                }

                $user = $bet->user;
                if (!$user) {
                    continue;
                }

                if ((int) $match->winner_id === (int) $bet->bet_on_user_id) {
                    $payout = (int) round($bet->amount * ($bet->odds ?: 2));
                    $bet->update([
                        'status' => 'won',
                        'payout' => $payout,
                        'settled_at' => now(),
                        'settlement_key' => $settlementKey,
                    ]);

                    $transaction = $this->wallets->change(
                        $user,
                        $payout,
                        'bet_payout',
                        $settlementKey,
                        'Payout for ' . $this->matchLabel($match),
                        $bet,
                        null,
                        ['match_id' => $match->id]
                    );

                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'Bet Won',
                        'message' => "You won {$payout} coins from {$this->matchLabel($match)}. Wallet balance: {$transaction->balance_after} coins.",
                        'type' => 'bet_won',
                        'related_user_id' => $bet->bet_on_user_id,
                        'target_url' => route('bets.show', $bet->id),
                    ]);
                } else {
                    $bet->update([
                        'status' => 'lost',
                        'payout' => 0,
                        'settled_at' => now(),
                        'settlement_key' => $settlementKey,
                    ]);
                    $pickName = $bet->betOnUser?->name ?? 'your pick';

                    Notification::create([
                        'user_id' => $user->id,
                        'title' => 'Bet Lost',
                        'message' => "Your {$bet->amount} coin bet on {$pickName} for {$this->matchLabel($match)} was not successful.",
                        'type' => 'bet_lost',
                        'related_user_id' => $bet->bet_on_user_id,
                        'target_url' => route('bets.show', $bet->id),
                    ]);
                }
            }
        });
    }

    public function refundMatch(GameMatch $match, ?User $actor = null): int
    {
        return DB::transaction(function () use ($match, $actor) {
            $match = GameMatch::whereKey($match->id)->lockForUpdate()->firstOrFail();
            $bets = Bet::with('user')
                ->where('match_id', $match->id)
                ->whereIn('status', ['pending', 'live'])
                ->lockForUpdate()
                ->get();

            $refunded = 0;
            foreach ($bets as $bet) {
                $reference = 'match-' . $match->id . '-bet-' . $bet->id . '-refund';
                if ($bet->settlement_key || \App\Models\WalletTransaction::where('reference', $reference)->exists()) {
                    continue;
                }

                if ($bet->user) {
                    $this->wallets->change(
                        $bet->user,
                        (int) $bet->amount,
                        'bet_refund',
                        $reference,
                        'Refund for cancelled market ' . $this->matchLabel($match),
                        $bet,
                        $actor,
                        ['match_id' => $match->id]
                    );
                }

                $bet->update([
                    'status' => 'refunded',
                    'payout' => (int) $bet->amount,
                    'settled_at' => now(),
                    'settlement_key' => $reference,
                ]);
                $refunded++;
            }

            return $refunded;
        });
    }

    /** Get user's bet history */
    public function getUserBetHistory(User $user)
    {
        return Bet::with(['gameMatch.player1', 'gameMatch.player2', 'betOnUser'])->where('user_id', $user->id)->latest()->get();
    }

    private function matchLabel(GameMatch $match): string
    {
        $match->loadMissing(['player1', 'player2']);

        return ($match->player1?->name ?? 'Player 1') . ' vs ' . ($match->player2?->name ?? 'Player 2');
    }
}
