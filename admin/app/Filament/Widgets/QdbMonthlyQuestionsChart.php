<?php

namespace App\Filament\Widgets;

use App\Models\Qdb\QdbQuestion;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class QdbMonthlyQuestionsChart extends LineChartWidget
{
    protected static ?string $heading = 'Questions / 12 mois';

    protected function getData(): array
    {
        $rows = QdbQuestion::selectRaw('DATE_FORMAT(created_at,"%Y-%m") m, COUNT(*) c')
            ->groupBy('m')->orderBy('m')->limit(12)->get();

        return [
            'datasets' => [
                ['label' => 'Questions', 'data' => $rows->pluck('c')],
            ],
            'labels' => $rows->pluck('m'),
        ];
    }
}
