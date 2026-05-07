<?php

namespace App\Services;

class EloService
{
    const K_FACTOR = 32;

    public static function calculateElo($playerRating, $opponentRating, $playerWon)
    {
        $expectedScore = 1 / (1 + pow(10, ($opponentRating - $playerRating) / 400));
        $actualScore = $playerWon ? 1 : 0;
        $eloChange = self::K_FACTOR * ($actualScore - $expectedScore);
        
        return (int)round($eloChange);
    }

    public static function updatePlayerRatings($match)
    {
        if (!$match->winner_id) {
            return;
        }

        $player1 = $match->player1;
        $player2 = $match->player2;

        $player1Won = $match->winner_id === $player1->id;
        $player2Won = $match->winner_id === $player2->id;

        $eloChangePlayer1 = self::calculateElo($player1->elo_rating, $player2->elo_rating, $player1Won);
        $eloChangePlayer2 = self::calculateElo($player2->elo_rating, $player1->elo_rating, $player2Won);

        $player1->elo_rating += $eloChangePlayer1;
        $player2->elo_rating += $eloChangePlayer2;

        if ($player1Won) {
            $player1->wins += 1;
            $player2->losses += 1;
        } else {
            $player2->wins += 1;
            $player1->losses += 1;
        }

        $player1->rank = self::getRankByElo($player1->elo_rating);
        $player2->rank = self::getRankByElo($player2->elo_rating);

        $player1->save();
        $player2->save();

        $match->elo_change = $eloChangePlayer1;
        $match->save();

        return [
            'player1' => [
                'elo_change' => $eloChangePlayer1,
                'new_rating' => $player1->elo_rating,
            ],
            'player2' => [
                'elo_change' => $eloChangePlayer2,
                'new_rating' => $player2->elo_rating,
            ],
        ];
    }

    public static function getRankByElo($elo)
    {
        if ($elo < 1400) {
            return 'Beginner';
        } elseif ($elo < 1600) {
            return 'Intermediate';
        } elseif ($elo < 1800) {
            return 'Advanced';
        } else {
            return 'Professional';
        }
    }
}
