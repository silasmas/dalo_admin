<?php

namespace App\Models;

use App\Models\User;
use App\Enums\MessageChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MsgMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'msg_messages';

    protected $fillable = [
        'status','created_by','updated_by','parent_id','channel',
        'from_id','from_name','from_email','from_phone',
        'subject','body','attachments_json','closed_at','priority',
    ];

    protected $casts = [
        'channel' => MessageChannel::class,
        'attachments_json' => 'array',
        'created_at' => 'datetime','updated_at' => 'datetime','deleted_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relations basiques
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }
}
