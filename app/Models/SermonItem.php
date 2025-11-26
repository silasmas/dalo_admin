<?php

namespace App\Models;

use App\Models\User;
use App\Enums\SermonType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SermonItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status','created_by','updated_by','type','title','description','preacher_name',
        'preacher_user_id','preached_on','is_live','featured','formats',
        'has_video','has_audio','has_text','url_video','url_audio','content_text',
        'cover_url','category_id','start_at','end_at',
        'views_count','likes_count','comments_count',
    ];

    protected $casts = [
        'type' => SermonType::class,
        'is_live' => 'bool','featured'=>'bool','has_video'=>'bool','has_audio'=>'bool','has_text'=>'bool',
        'formats' => 'array',
        'preached_on' => 'date',
        'start_at' => 'datetime','end_at' => 'datetime',
        'created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime',
    ];

    public function preacher() { return $this->belongsTo(User::class,'preacher_user_id'); }
}
