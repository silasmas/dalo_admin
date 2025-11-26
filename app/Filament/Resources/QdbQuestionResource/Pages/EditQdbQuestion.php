<?php

namespace App\Filament\Resources\QdbQuestionResource\Pages;

use App\Filament\Resources\QdbQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQdbQuestion extends EditRecord
{
    protected static string $resource = QdbQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
