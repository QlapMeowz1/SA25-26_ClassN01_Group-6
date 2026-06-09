<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GameMatch;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_id',
        'bet_on_user_id',
        'amount',
        'odds',
        'status',
        'payout',
        'settled_at',
        'settlement_key',
    ];

    protected $casts = [
        'amount' => 'float',
        'odds' => 'float',
        'payout' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gameMatch()
    {
        return $this->belongsTo(GameMatch::class, 'match_id');
    }

    public function betOnUser()
    {
        return $this->belongsTo(User::class, 'bet_on_user_id');
    }

    public function settle()
    {
        if ($this->gameMatch->isCompleted()) {
            if ($this->gameMatch->winner_id === $this->bet_on_user_id) {
                $this->status = 'won';
                $this->payout = $this->calculatePayout();
            } else {
                $this->status = 'lost';
                $this->payout = 0;
            }
            $this->save();
        }
    }

    public function isWon()
    {
        return $this->status === 'won';
    }

    public function calculatePayout()
    {
        return (float) round($this->amount * ($this->odds ?: 2), 2);
    }
}
