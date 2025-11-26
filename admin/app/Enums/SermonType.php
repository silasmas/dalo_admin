<?php
namespace App\Enums;

enum SermonType:string
{
    case Predication = 'predication';
    case Enseignement = 'enseignement';
    case Emission = 'emission';

    public function label(): string
    {
        return match ($this) {
            self::Predication => 'Prédication',
            self::Enseignement => 'Enseignement',
            self::Emission => 'Émission',
        };
    }
}
