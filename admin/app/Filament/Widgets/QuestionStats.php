<?php

namespace App\Filament\Resources\QuestionResource\Widgets;

use App\Models\FaqQuestion;
use App\Models\Qdb\QdbQuestion;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class QuestionStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $total      = QdbQuestion::count();
        $pending    = QdbQuestion::where('status', 'pending')->count();
        $answered   = QdbQuestion::where('status', 'answered')->count();

        return [
            Card::make('Questions totales', $total)
                ->description('Toutes les questions reçues')
                ->descriptionIcon('heroicon-o-question-mark-circle')
                ->color('primary'),

            Card::make('En attente de réponse', $pending)
                ->description('À traiter par l’équipe')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color('warning'),

            Card::make('Questions répondues', $answered)
                ->description('Déjà traitées / résolues')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
