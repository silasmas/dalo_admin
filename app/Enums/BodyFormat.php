<?php
namespace App\Enums;

enum BodyFormat:string
{
    case Markdown = 'markdown';
    case Html     = 'html';
    case Text     = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Markdown => 'Markdown',
            self::Html => 'HTML',
            self::Text => 'Texte brut',
        };
    }
}
