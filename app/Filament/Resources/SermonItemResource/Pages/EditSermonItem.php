<?php

namespace App\Filament\Resources\SermonItemResource\Pages;

use App\Filament\Resources\SermonItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSermonItem extends EditRecord
{
    protected static string $resource = SermonItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
