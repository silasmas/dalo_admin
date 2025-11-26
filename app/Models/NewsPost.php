<?php

namespace App\Models;

use MainCategory;
use App\Models\User;
use App\Enums\BodyFormat;
use App\Enums\NewsStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NewsPost extends Model
{
    use SoftDeletes;

    protected $table = 'news_posts';

    protected $fillable = [
        'status','created_by','updated_by','category_id','title','slug','summary',
        'body','body_format','cover_url','news_status','published_at',
        'starts_at','ends_at','expires_at','featured','location_name','location_addr',
        'external_url','read_count',
    ];

    protected $casts = [
        'body_format' => BodyFormat::class,
        'news_status' => NewsStatus::class,
        'featured' => 'bool',
        'status' => 'int',
        'published_at'=>'datetime','starts_at'=>'datetime','ends_at'=>'datetime','expires_at'=>'datetime',
        'created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime',
    ];

    // public function category(){ return $this->belongsTo(MainCategory::class,'category_id'); } // si dispo
    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
}
