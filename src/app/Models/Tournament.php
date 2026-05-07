<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'organizer_id',
        'start_date',
        'end_date',
        'max_participants',
        'status',
        'prize_pool',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'tournament_participants', 'tournament_id', 'user_id');
    }

    public function tournamentParticipants()
    {
        return $this->hasMany(TournamentParticipant::class);
    }

    public function isFull()
    {
        return $this->tournamentParticipants()->count() >= $this->max_participants;
    }

    public function hasParticipant($userId)
    {
        return $this->participants()->where('user_id', $userId)->exists();
    }
}
