<?php

namespace App\Filament\Resources\SermonItemResource\Pages;

use App\Filament\Resources\SermonItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSermonItems extends ListRecords
{
    protected static string $resource = SermonItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
