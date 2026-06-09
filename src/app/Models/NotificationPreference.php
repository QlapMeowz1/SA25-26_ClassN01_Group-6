<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $fillable = [
        'interactions_web',
        'matches_web',
        'betting_web',
        'system_web',
        'critical_email',
        'match_reminders',
        'betting_updates',
    ];

    protected $casts = [
        'interactions_web' => 'boolean',
        'matches_web' => 'boolean',
        'betting_web' => 'boolean',
        'system_web' => 'boolean',
        'critical_email' => 'boolean',
        'match_reminders' => 'boolean',
        'betting_updates' => 'boolean',
    ];
}
