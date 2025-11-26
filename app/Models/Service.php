<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status','created_by','updated_by','name','slug','description','magener_id',
    ];

    protected $casts = [
        'status'=>'int',
        'created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime',
    ];

    public function manager() { return $this->belongsTo(User::class,'magener_id'); }
}
