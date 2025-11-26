<?php

namespace App\Models\Gallery;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GalImage extends Model
{
    use SoftDeletes;

    protected $table = 'gal_images';

    protected $fillable = [
        'status',
        'created_by',
        'updated_by',
        'gallery_id',
        'title',
        'caption',
        'alt_text',
        'file_url',
        'bytes',
        'width',
        'height',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    public function gallery()
    {
        return $this->belongsTo(GalGallery::class, 'gallery_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
