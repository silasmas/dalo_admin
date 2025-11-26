<?php

namespace App\Enums\Qdb;

enum AnswerStatus: string
{
    case Published = 'published';
    case Hidden    = 'hidden';
    case Draft     = 'draft';

    public function label(): string
    {
        return match ($this) {
            self::Published => 'Publiée',
            self::Hidden    => 'Cachée',
            self::Draft     => 'Brouillon',
        };
    }
}
