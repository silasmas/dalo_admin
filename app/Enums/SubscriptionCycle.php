<?php
// app/Enums/SubscriptionCycle.php
namespace App\Enums;

enum SubscriptionCycle: string
{
    case Monthly = 'monthly';
    case Annualy = 'annualy';
    case Weekly  = 'weekly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Mensuel',
            self::Annualy => 'Annuel',
            self::Weekly  => 'Hebdomadaire',
        };
    }
}
