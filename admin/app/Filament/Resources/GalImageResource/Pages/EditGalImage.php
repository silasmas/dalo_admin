<?php

namespace App\Filament\Resources\GalImageResource\Pages;

use App\Filament\Resources\GalImageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGalImage extends EditRecord
{
    protected static string $resource = GalImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
