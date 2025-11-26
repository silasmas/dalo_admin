<?php

namespace App\Filament\Resources\AppointmentResource\Widgets;

use App\Models\Appt\Appointment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class AppointmentStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $today = Carbon::today();
        $now   = Carbon::now();

        $upcoming = Appointment::where('scheduled_at', '>=', $now)
            ->whereIn('app_status', ['pending','confirmed'])
            ->count();

        $todayDone = Appointment::whereDate('scheduled_at', $today)
            ->where('app_status', 'done')
            ->count();

        $noShow = Appointment::where('app_status', 'no_show')->count();

        return [
            Card::make('RDV à venir', $upcoming)
                ->description('En attente ou confirmés')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),

            Card::make('RDV terminés aujourd’hui', $todayDone)
                ->description('Servis ce jour')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Card::make('Absents (no-show)', $noShow)
                ->description('Rendez-vous non honorés')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
