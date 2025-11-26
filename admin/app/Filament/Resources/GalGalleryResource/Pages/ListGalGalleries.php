<?php

namespace App\Filament\Resources\GalGalleryResource\Pages;

use App\Filament\Resources\GalGalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGalGalleries extends ListRecords
{
    protected static string $resource = GalGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
