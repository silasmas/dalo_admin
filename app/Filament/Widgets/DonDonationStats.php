<?php

namespace App\Filament\Resources\DonDonationResource\Widgets;

use App\Models\Don\DonDonation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class DonDonationStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $now   = Carbon::now();
        $startMonth = $now->copy()->startOfMonth();
        $startDay   = $now->copy()->startOfDay();

        $totalCount = DonDonation::count();
        $monthCount = DonDonation::whereBetween('created_at', [$startMonth, $now])->count();
        $dayCount   = DonDonation::whereBetween('created_at', [$startDay, $now])->count();

        $totalAmount = DonDonation::where('donation_status', 'paid')->sum('amount');
        $monthAmount = DonDonation::where('donation_status', 'paid')
            ->whereBetween('created_at', [$startMonth, $now])
            ->sum('amount');

        return [
            Card::make('Total dons (tous temps)', $totalCount)
                ->description(number_format($totalAmount, 2) . ' USD collectés')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Card::make('Dons ce mois-ci', $monthCount)
                ->description(number_format($monthAmount, 2) . ' USD ce mois')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('primary'),

            Card::make('Dons aujourd’hui', $dayCount)
                ->description('Dons enregistrés depuis minuit')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
