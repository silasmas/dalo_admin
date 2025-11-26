<?php

namespace App\Filament\Resources\GalGalleryResource\Pages;

use App\Filament\Resources\GalGalleryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGalGallery extends EditRecord
{
    protected static string $resource = GalGalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
