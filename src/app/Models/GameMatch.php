<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameMatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'matches';

    protected $fillable = [
        'player1_id',
        'player2_id',
        'challenge_id',
        'status',
        'match_date',
        'location',
        'player1_score',
        'player2_score',
        'winner_id',
        'result_submitted_by',
        'result_confirmed_by',
        'result_submitted_at',
        'result_confirmed_at',
        'result_dispute_reason',
        'elo_change',
        'player1_odds',
        'player2_odds',
        'odds_updated_by',
        'odds_updated_at',
        'betting_status',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'player1_odds' => 'float',
        'player2_odds' => 'float',
        'odds_updated_at' => 'datetime',
        'result_submitted_at' => 'datetime',
        'result_confirmed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function joinRequests()
    {
        return $this->morphMany(JoinRequest::class, 'requestable');
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'match_id');
    }

    public function oddsUpdatedBy()
    {
        return $this->belongsTo(User::class, 'odds_updated_by');
    }

    public function hasManualOdds(): bool
    {
        return $this->player1_odds !== null && $this->player2_odds !== null;
    }

    public function canAcceptBets(): bool
    {
        $marketStatus = $this->betting_status ?? 'open';

        return $this->player1_id
            && $this->player2_id
            && !in_array($this->status, ['completed', 'cancelled'], true)
            && in_array($marketStatus, ['open', 'approved'], true);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function canSubmitResult()
    {
        return $this->status === 'in_progress';
    }
}
