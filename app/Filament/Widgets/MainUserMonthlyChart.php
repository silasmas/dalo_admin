<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\User;
use App\Models\MainUser;
use Filament\Widgets\LineChartWidget;

class MainUserMonthlyChart extends LineChartWidget
{
    protected static ?string $heading = 'Inscriptions (12 derniers mois)';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $labels[] = $m->format('m/Y');

            $data[] = User::whereYear('created_at', $m->year)
                ->whereMonth('created_at', $m->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Comptes créés',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
