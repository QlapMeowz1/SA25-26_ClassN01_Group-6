<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenger_id',
        'opponent_id',
        'status',
        'message',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function challenger()
    {
        return $this->belongsTo(User::class, 'challenger_id');
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function joinRequests()
    {
        return $this->morphMany(JoinRequest::class, 'requestable');
    }

    public function gameMatch()
    {
        return $this->hasOne(GameMatch::class, 'challenge_id');
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isExpired()
    {
        return now()->gt($this->expires_at);
    }
}
