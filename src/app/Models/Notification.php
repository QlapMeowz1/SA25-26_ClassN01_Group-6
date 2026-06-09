<?php

namespace App\Models;

use App\Events\UserNotificationUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'related_user_id',
        'is_read',
        'is_pinned',
        'target_url',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Notification $notification) {
            broadcast(new UserNotificationUpdated($notification));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();
    }
}
