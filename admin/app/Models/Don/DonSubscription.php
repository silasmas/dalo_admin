<?php
// app/Models/Don/DonSubscription.php
namespace App\Models\Don;

use App\Enums\SubscriptionCycle;
use App\Enums\SubscriptionState;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonSubscription extends Model
{
    protected $table = 'don_subscriptions';

    protected $fillable = [
        'status',
        'user_id',
        'code',
        'subscription_type_id',
        'donation_type_id',
        'amount',
        'currency',
        'start_date',
        'end_date',
        'state',
        'next_due_at',
        'last_paid_at',
        'cycle',
        'autopay',
        'notes',
        'reminder_5d_sent_at',
    ];

    protected $casts = [
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'deleted_at'           => 'datetime',
        'start_date'           => 'date',
        'end_date'             => 'date',
        'next_due_at'          => 'datetime',
        'last_paid_at'         => 'datetime',
        'state'                => SubscriptionState::class,
        'cycle'                => SubscriptionCycle::class,
        'autopay'              => 'boolean',
        'reminder_5d_sent_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(DonDonation::class, 'subscription_id');
    }
}
