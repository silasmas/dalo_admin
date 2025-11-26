<?php

namespace App\Filament\Resources\QdbAnswerResource\Pages;

use App\Filament\Resources\QdbAnswerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQdbAnswers extends ListRecords
{
    protected static string $resource = QdbAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
