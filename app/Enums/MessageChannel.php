<?php
namespace App\Enums;

enum MessageChannel:string
{
    case Contact = 'contact';
    case Support = 'support';
    case Feedback = 'feedback';
    case Prayer  = 'prayer_request';
    case Other   = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Contact => 'Contact',
            self::Support => 'Support',
            self::Feedback => 'Retour',
            self::Prayer => 'Demande de prière',
            self::Other => 'Autre',
        };
    }
}
