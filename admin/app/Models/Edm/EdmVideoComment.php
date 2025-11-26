<?php

namespace App\Models\Edm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EdmVideoComment extends Model
{
    use SoftDeletes;

    protected $table = 'edm_video_comments';

    protected $fillable = [
        'status',
        'created_by',
        'updated_by',
        'video_id',
        'user_id',
        'parent_id',
        'content',
    ];

    public function video()
    {
        return $this->belongsTo(EdmVideo::class, 'video_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(EdmVideoComment::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(EdmVideoComment::class, 'parent_id');
    }
}
