<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'likes_count',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    public function getEmbeddedImageUrlAttribute(): ?string
    {
        if (preg_match('/(?:Ảnh|Anh|Image|Photo)\s*:\s*(https?:\/\/\S+)/iu', (string) $this->content, $matches)) {
            return rtrim($matches[1], '.,)\]\'"');
        }

        if (preg_match('/https?:\/\/\S+/i', (string) $this->content, $matches)) {
            return rtrim($matches[0], '.,)\]\'"');
        }

        return null;
    }

    public function getDisplayContentAttribute(): string
    {
        $content = (string) $this->content;
        $imageUrl = $this->embedded_image_url;

        if ($imageUrl) {
            $content = preg_replace('/(?:Ảnh|Anh|Image|Photo)\s*:\s*' . preg_quote($imageUrl, '/') . '/iu', '', $content) ?? $content;
            $content = str_replace($imageUrl, '', $content);
        }

        $content = preg_replace('/\n{3,}/', "\n\n", $content) ?? $content;
        $content = preg_replace('/[ \t]+\n/', "\n", $content) ?? $content;

        return trim($content);
    }
}
