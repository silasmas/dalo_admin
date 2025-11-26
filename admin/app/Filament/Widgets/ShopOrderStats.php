<?php

namespace App\Filament\Resources\ShopOrderResource\Widgets;

use App\Enums\ShopOrderState;
use App\Models\Shop\ShopOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class ShopOrderStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $now   = Carbon::now();
        $start = $now->copy()->startOfMonth();

        $pending   = ShopOrder::where('state', ShopOrderState::Pending)->count();
        $paid      = ShopOrder::where('state', ShopOrderState::Paid)->count();
        $completed = ShopOrder::where('state', ShopOrderState::Completed)->count();

        $revenueMonth = ShopOrder::where('state', ShopOrderState::Paid)
            ->whereBetween('created_at', [$start, $now])
            ->sum('total');

        return [
            Card::make('Commandes en attente', $pending)
                ->description('À traiter')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Card::make('Commandes payées', $paid)
                ->description('En cours de préparation / expédition')
                ->descriptionIcon('heroicon-o-credit-card')
                ->color('primary'),

            Card::make('Revenus ce mois-ci', number_format($revenueMonth, 2) . ' USD')
                ->description($completed . ' commandes terminées')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
        ];
    }
}
