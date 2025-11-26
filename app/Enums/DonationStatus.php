<?php

// app/Enums/DonationStatus.php
namespace App\Enums;

enum DonationStatus: string
{
    case Pending  = 'pending';
    case Paid     = 'paid';
    case Failed   = 'failed';
    case Canceled = 'canceled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'En attente',
            self::Paid     => 'Payé',
            self::Failed   => 'Échoué',
            self::Canceled => 'Annulé',
            self::Refunded => 'Remboursé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending  => 'warning',
            self::Paid     => 'success',
            self::Failed   => 'danger',
            self::Canceled => 'gray',
            self::Refunded => 'info',
        };
    }
}
