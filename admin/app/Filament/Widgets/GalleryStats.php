<?php

namespace App\Filament\Resources\GalImageResource\Widgets;


use App\Models\Gallery\GalImage;
use App\Models\Gallery\GalGallery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class GalleryStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $galleryCount = GalGallery::where('status', 1)->count();
        $imagesCount  = GalImage::where('status', 1)->count();

        $mostPopulated = GalGallery::withCount('images')
            ->orderByDesc('images_count')
            ->first();

        return [
            Card::make('Galeries actives', $galleryCount)
                ->description('Groupes d’images publiées')
                ->descriptionIcon('heroicon-o-rectangle-group')
                ->color('primary'),

            Card::make('Images totales', $imagesCount)
                ->description('Photos dans toutes les galeries')
                ->descriptionIcon('heroicon-o-photo')
                ->color('success'),

            Card::make('Galerie la plus fournie', $mostPopulated?->title ?? 'Aucune')
                ->description(($mostPopulated?->images_count ?? 0) . ' images')
                ->descriptionIcon('heroicon-o-star')
                ->color('warning'),
        ];
    }
}
