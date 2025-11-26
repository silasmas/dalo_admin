<?php
namespace App\Enums;

enum NewsStatus:string
{
    case Draft     = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived  = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Scheduled => 'Planifiée',
            self::Published => 'Publiée',
            self::Archived => 'Archivée',
        };
    }
}
