<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image',
        'video',
        'images',
        'videos',
        'likes_count',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'images' => 'array',
        'videos' => 'array',
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

    public function getEmbeddedImageUrlsAttribute(): array
    {
        if (is_array($this->images) && ! empty($this->images)) {
            return $this->images;
        }

        $content = (string) $this->content;
        preg_match_all('/https?:\/\/[^\s)\]\'";]+/i', $content, $matches);

        if (empty($matches[0])) {
            return [];
        }

        // filter likely image URLs (simple heuristic)
        $urls = array_filter($matches[0], function ($u) {
            return preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)|picsum\.photos|unsplash\.com/i', $u);
        });

        return array_values(array_map(function ($u) {
            return rtrim($u, '.,)\]\'"');
        }, $urls));
    }

    /**
     * Return a usable image URL for the post's primary image field.
     * If the `image` value is already an absolute URL, return it directly.
     * Otherwise, generate a storage URL via `Storage::url()`.
     */
    public function getImageUrlAttribute(): ?string
    {
        $img = $this->image;

        if (! $img) {
            return null;
        }

        // If already an absolute URL, return as-is
        if (preg_match('/^https?:\/\//i', $img)) {
            return $img;
        }

        try {
            return Storage::url($img);
        } catch (\Throwable $e) {
            return $img;
        }
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
