<?php

namespace App\Filament\Resources\EdmVideoResource\Widgets;

use App\Models\Edm\EdmVideo;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Carbon;

class EdmVideoStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $now   = Carbon::now();
        $startMonth = $now->copy()->startOfMonth();

        $totalVideos  = EdmVideo::where('status', 1)->count();
        $monthVideos  = EdmVideo::where('status', 1)
            ->whereBetween('created_at', [$startMonth, $now])
            ->count();

        $totalLikes     = EdmVideo::sum('likes_count');
        $totalFavorites = EdmVideo::sum('favorites_count');
        $totalComments  = EdmVideo::sum('comments_count');

        return [
            Card::make('Vidéos actives', $totalVideos)
                ->description($monthVideos . ' ajoutées ce mois')
                ->descriptionIcon('heroicon-o-video-camera')
                ->color('primary'),

            Card::make('Engagement (likes)', $totalLikes)
                ->description('Total des likes sur toutes les vidéos')
                ->descriptionIcon('heroicon-o-hand-thumb-up')
                ->color('success'),

            Card::make('Favoris & commentaires', $totalFavorites . ' fav / ' . $totalComments . ' comm')
                ->description('Indicateurs d’édification & interaction')
                ->descriptionIcon('heroicon-o-chat-bubble-left-right')
                ->color('info'),
        ];
    }
}
