<?php

// app/Models/Don/DonDonation.php
namespace App\Models\Don;

use App\Enums\DonationStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonDonation extends Model
{
    protected $table = 'don_donations';

    protected $fillable = [
        'status',
        'user_id',
        'subscription_id',
        'donation_type_id',
        'amount',
        'currency',
        'donor_name',
        'donor_email',
        'donor_phone',
        'donation_status',
        'paid_at',
        'payment_id',
        'reference',
        'notes',
    ];

    protected $casts = [
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
        'paid_at'         => 'datetime',
        'donation_status' => DonationStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(DonSubscription::class, 'subscription_id');
    }
}
