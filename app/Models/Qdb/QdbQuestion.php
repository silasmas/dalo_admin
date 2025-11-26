<?php

namespace App\Models\Qdb;

use App\Enums\Qdb\QuestionState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class QdbQuestion extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'qdb_questions';

    protected $fillable = [
        'status','created_at','updated_at','deleted_at',
        'created_by','updated_by',
        'title','slug','body','state',
        'author_user_id','author_name','author_email','author_phone',
        'answer_id','sermon_id','edm_id','nb_likes','categories_tags',
    ];

    protected $casts = [
        'state'      => QuestionState::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'nb_likes'   => 'integer',
    ];

    // Relations
    public function answers()
    {
        return $this->hasMany(QdbAnswer::class, 'question_id');
    }

    public function acceptedAnswer()
    {
        return $this->belongsTo(QdbAnswer::class, 'answer_id');
    }

    public function likes()
    {
        return $this->hasMany(QdbLike::class, 'question_id');
    }
}
