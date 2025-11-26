<?php

namespace App\Models\Gallery;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GalGallery extends Model
{
    use SoftDeletes;

    protected $table = 'gal_galleries';

    protected $fillable = [
        'status',
        'created_by',
        'updated_by',
        'parent_id',
        'slug',
        'title',
        'description',
        'cover_url',
        'visibility',
        'images_count',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    // Relations
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function images()
    {
        return $this->hasMany(GalImage::class, 'gallery_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public')->where('status', 1);
    }

    // Génération du slug si vide
    protected static function booted()
    {
        static::creating(function (GalGallery $gallery) {
            if (empty($gallery->slug)) {
                $gallery->slug = Str::slug($gallery->title . '-' . now()->timestamp);
            }
        });
    }
}
