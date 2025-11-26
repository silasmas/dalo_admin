<?php

namespace App\Models\Qdb;

use App\Enums\Qdb\AnswerStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class QdbAnswer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'qdb_answers';

    protected $fillable = [
        'status','created_at','updated_at','deleted_at',
        'created_by','updated_by',
        'question_id','answer','answer_status',
        'author_user_id','author_name','author_email',
        'is_official','is_accepted','nb_likes',
        'versets_refs_json','sources_json',
    ];

    protected $casts = [
        'answer_status'    => AnswerStatus::class,
        'is_official'      => 'boolean',
        'is_accepted'      => 'boolean',
        'versets_refs_json'=> 'array',
        'sources_json'     => 'array',
        'nb_likes'         => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    public function question()
    {
        return $this->belongsTo(QdbQuestion::class, 'question_id');
    }

    public function likes()
    {
        return $this->hasMany(QdbLike::class, 'answer_id');
    }
}
