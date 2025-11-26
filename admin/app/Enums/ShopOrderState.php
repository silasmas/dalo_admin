<?php

// app/Enums/ShopOrderState.php
namespace App\Enums;

enum ShopOrderState: string
{
    case Pending   = 'pending';
    case Paid      = 'paid';
    case Failed    = 'failed';
    case Canceled  = 'canceled';
    case Shipped   = 'shipped';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending   => 'En attente',
            self::Paid      => 'Payé',
            self::Failed    => 'Échec',
            self::Canceled  => 'Annulé',
            self::Shipped   => 'Expédié',
            self::Completed => 'Terminé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'warning',
            self::Paid      => 'success',
            self::Failed    => 'danger',
            self::Canceled  => 'gray',
            self::Shipped   => 'info',
            self::Completed => 'success',
        };
    }
}
