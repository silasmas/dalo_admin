<?php

namespace App\Filament\Widgets;

use App\Models\Qdb\QdbAnswer;
use App\Models\Qdb\QdbQuestion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class QdbOverviewStats extends BaseWidget
{
    protected function getCards(): array
    {
        $qCount = QdbQuestion::count();
        $aCount = QdbAnswer::count();
        $pubQ   = QdbQuestion::where('state','published')->count();

        return [
            Card::make('Questions', number_format($qCount)),
            Card::make('Réponses', number_format($aCount)),
            Card::make('Questions publiées', number_format($pubQ)),
        ];
    }
}
