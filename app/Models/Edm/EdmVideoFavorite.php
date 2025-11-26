<?php

namespace App\Models\Edm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EdmVideoFavorite extends Model
{
    use SoftDeletes;

    protected $table = 'edm_video_favorites';

    protected $fillable = [
        'status',
        'created_by',
        'updated_by',
        'video_id',
        'user_id',
    ];

    public function video()
    {
        return $this->belongsTo(EdmVideo::class, 'video_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
