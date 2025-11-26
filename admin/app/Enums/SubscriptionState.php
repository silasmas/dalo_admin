<?php

// app/Enums/SubscriptionState.php
namespace App\Enums;

enum SubscriptionState: string
{
    case Active    = 'active';
    case Paused    = 'paused';
    case Canceled  = 'canceled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'Active',
            self::Paused    => 'En pause',
            self::Canceled  => 'Annulée',
            self::Completed => 'Terminée',
        };
    }
}
