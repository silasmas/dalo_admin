<?php

namespace App\Models\Qdb;

use Illuminate\Database\Eloquent\Model;

class QdbLike extends Model
{
    protected $table = 'qdb_likes';
    public $timestamps = false;

    protected $fillable = [
        'status','created_at','updated_at','deleted_at',
        'created_by','updated_by',
        'user_id','question_id','answer_id',
    ];

    public function question() { return $this->belongsTo(QdbQuestion::class, 'question_id'); }
    public function answer()   { return $this->belongsTo(QdbAnswer::class, 'answer_id'); }
}
