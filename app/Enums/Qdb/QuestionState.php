<?php

namespace App\Enums\Qdb;

enum QuestionState: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Hidden = 'hidden';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Brouillon',
            self::Published => 'Publiée',
            self::Hidden    => 'Cachée',
            self::Archived  => 'Archivée',
        };
    }
}
