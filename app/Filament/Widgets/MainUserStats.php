<?php

namespace App\Filament\Widgets;


use App\Enums\UserStatus;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MainUserStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total utilisateurs', User::count())
                ->description('Tous statuts confondus')
                ->icon('heroicon-o-user-group'),

            Stat::make('Activés', User::where('status', UserStatus::Activated)->count())
                ->description('Comptes actifs')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('En attente', User::where('status', UserStatus::Pending)->count())
                ->description('À valider')
                ->color('warning')
                ->icon('heroicon-o-clock'),
        ];
    }
}
