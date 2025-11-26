<?php

namespace App\Models\Appt;

use App\Models\User; // ⬅️ adapte au bon modèle
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    protected $table = 'appt_appointments';

    protected $fillable = [
        'status',
        'created_by',
        'updated_by',
        'service_id',
        'user_id',
        'staff_user_id',
        'provider_name',
        'scheduled_at',
        'end_at',
        'app_status',
        'notes',
        'cancel_reason',
        'canceled_by',
        'canceled_at',
        'titre',
        'description',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'end_at'       => 'datetime',
        'canceled_at'  => 'datetime',
    ];

    // Client
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->where('default_role', '5');
    }

    // Pasteur / staff affecté
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id')->where('default_role', '2');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'canceled_by');
    }

    // TODO : remplace par ton vrai modèle Service
    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class, 'service_id');
    }

    // Scopes pratiques
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now());
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now());
    }

    public function scopeForStaff($query, $staffId)
    {
        return $query->where('staff_user_id', $staffId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
