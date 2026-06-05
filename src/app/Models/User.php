<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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
        'theme',
        'role',
        'is_banned',
        'banned_at',
        'ban_reason',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'theme' => 'string',
        'role' => 'string',
        'is_banned' => 'boolean',
        'banned_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

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

    public function emailVerificationCodes()
    {
        return $this->hasMany(EmailVerificationCode::class);
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
        return $total > 0 ? (int) round(($this->wins / $total) * 100) : 0;
    }

    public function getAvatarUrlAttribute(): string
    {
        if (empty($this->avatar)) {
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><rect width="160" height="160" rx="80" fill="#e2e8f0"/><path d="M80 76c16.6 0 30-13.4 30-30S96.6 16 80 16 50 29.4 50 46s13.4 30 30 30zm0 12c-24.8 0-44 12.4-44 28v10h88v-10c0-15.6-19.2-28-44-28z" fill="#94a3b8"/></svg>';
            return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
        }

        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }

        return asset('avatars/' . $this->avatar);
    }
}
