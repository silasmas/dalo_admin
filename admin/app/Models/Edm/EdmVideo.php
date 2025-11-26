<?php

namespace App\Models\Edm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concernes\HasS3MediaUrls;
use App\Models\MainCategory;

class EdmVideo extends Model
{
    use SoftDeletes, HasS3MediaUrls;

    protected $table = 'edm_videos';

    protected $fillable = [
        'status',
        'created_by',
        'updated_by',
        'title',
        'description',
        'author_user_id',
        'author_name',
        'category_id',
        'hashtags_json',
        'url_video',
        'cover_url',
        'duration_sec',
        'featured',
        'published_at',
        'likes_count',
        'favorites_count',
        'comments_count',
        'shares_count',
    ];

    protected $casts = [
        'hashtags_json' => 'array',
        'published_at'  => 'datetime',
        'featured'      => 'boolean',
        'status'        => 'integer',
    ];

    // Auteur lié au user
    public function authorUser()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
    public function categorie()
    {
        return $this->belongsTo(MainCategory::class, 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Commentaires
    public function comments()
    {
        return $this->hasMany(EdmVideoComment::class, 'video_id');
    }

    // Likes
    public function likes()
    {
        return $this->hasMany(EdmVideoLike::class, 'video_id');
    }

    // Favoris
    public function favorites()
    {
        return $this->hasMany(EdmVideoFavorite::class, 'video_id');
    }

    // Scope pour les vidéos publiées
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('status', 1);
    }

    // Scope pour classer par popularité (likes + favoris + partages)
    public function scopeMostPopular($query)
    {
        return $query->orderByDesc('likes_count')
            ->orderByDesc('favorites_count')
            ->orderByDesc('shares_count');
    }
      public function getCoverUrlFullAttribute(): ?string
    {
        if (! $this->cover_url) {
            return null;
        }

        return Storage::disk('s3')->url($this->cover_url);
    }

    public function getVideoUrlFullAttribute(): ?string
    {
        if (! $this->url_video) {
            return null;
        }

        return Storage::disk('s3')->url($this->url_video);
    }
     // Accessor pratique pour Filament
    public function getCoverFullUrlAttribute(): string
    {
        return $this->mediaUrl('cover_url', ttl: 10)
            ?? asset('images/placeholder.png');
    }
    public function mediaUrl(string $column, int $ttl = 5, ?string $disk = null): ?string
{
    $disk = $disk ?? ($this->disk ?? config('filesystems.default', 's3'));
    // ...
}

}
