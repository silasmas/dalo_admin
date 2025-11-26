<?php

namespace App\Enums;

/**
 * 📌 Mappe exactement les codes de ta colonne `status`
 * 1 = activated, 2 = pending, 3 = disabled, 0 = deleted,
 * 4 = validé mais en attente des infos personnelles
 * 5 = en cours de création (doit valider OTP)
 */
enum UserStatus: int
{
    case Deleted    = 0;
    case Activated  = 1;
    case Pending    = 2;
    case Disabled   = 3;
    case ValidatedAwaitingInfo = 4;
    case CreatingAwaitingOtp   = 5;

    public function label(): string
    {
        return match ($this) {
            self::Deleted               => 'Supprimé',
            self::Activated             => 'Activé',
            self::Pending               => 'En attente',
            self::Disabled              => 'Désactivé',
            self::ValidatedAwaitingInfo => 'Validé — infos manquantes',
            self::CreatingAwaitingOtp   => 'Création — OTP',
        };
    }

    public function color(): string
    {
        // couleurs Filament pour BadgeColumn
        return match ($this) {
            self::Activated             => 'success',
            self::Pending               => 'warning',
            self::Disabled              => 'danger',
            self::Deleted               => 'gray',
            self::ValidatedAwaitingInfo => 'info',
            self::CreatingAwaitingOtp   => 'primary',
        };
    }
}
