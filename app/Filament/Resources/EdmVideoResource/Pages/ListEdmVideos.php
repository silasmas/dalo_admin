<?php

namespace App\Filament\Resources\EdmVideoResource\Pages;

use App\Filament\Resources\EdmVideoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEdmVideos extends ListRecords
{
    protected static string $resource = EdmVideoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
