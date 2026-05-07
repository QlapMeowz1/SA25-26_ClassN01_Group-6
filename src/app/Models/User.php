<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\GameMatch;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'rank',
        'elo_rating',
        'virtual_coins',
        'wins',
        'losses',
        'bio',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function challenges()
    {
        return $this->hasMany(Challenge::class, 'challenger_id');
    }

    public function receivedChallenges()
    {
        return $this->hasMany(Challenge::class, 'opponent_id');
    }

    public function joinRequests()
    {
        return $this->hasMany(JoinRequest::class, 'requester_id');
    }

    public function matches()
    {
        return $this->hasMany(GameMatch::class, 'player1_id')
                    ->union($this->hasMany(GameMatch::class, 'player2_id')->getQuery());
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id');
    }

    public function tournaments()
    {
        return $this->belongsToMany(Tournament::class, 'tournament_participants', 'user_id', 'tournament_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function getRankPercentage()
    {
        $ranks = ['Beginner', 'Intermediate', 'Advanced', 'Professional'];
        $index = array_search($this->rank, $ranks);
        return (($index + 1) / count($ranks)) * 100;
    }

    public function getWinRate()
    {
        $total = $this->wins + $this->losses;
        return $total > 0 ? round(($this->wins / $total) * 100, 2) : 0;
    }
}
