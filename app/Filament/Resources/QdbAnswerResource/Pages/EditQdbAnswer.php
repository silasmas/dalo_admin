<?php

namespace App\Filament\Resources\QdbAnswerResource\Pages;

use App\Filament\Resources\QdbAnswerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQdbAnswer extends EditRecord
{
    protected static string $resource = QdbAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
