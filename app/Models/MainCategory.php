<?php

namespace App\Models;

use App\Models\Edm\EdmVideo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MainCategory extends Model
{
    use SoftDeletes;

    protected $table = 'main_categories';

    protected $fillable = [
        'status',
        'created_by',
        'updated_by',
        'type',
        'cat_key',
        'cat_name',
        'description',
        'parent_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
    public function edmvideo()
    {
        return $this->hasMany(EdmVideo::class, 'id');
    }
}
