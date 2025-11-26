<?php
// app/Enums/ProductType.php
namespace App\Enums;

enum ProductType: string
{
    case Book        = 'book';
    case Accessories = 'accessories';
    case Clothes     = 'clothes';
    case Other       = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Book        => 'Livre',
            self::Accessories => 'Accessoire',
            self::Clothes     => 'Vêtement',
            self::Other       => 'Autre',
        };
    }
}
