<?php

namespace App\Filament\Resources\GalImageResource\Pages;

use App\Filament\Resources\GalImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGalImages extends ListRecords
{
    protected static string $resource = GalImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
