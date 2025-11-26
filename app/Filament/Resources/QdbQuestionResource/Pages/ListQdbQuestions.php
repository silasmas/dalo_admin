<?php

namespace App\Filament\Resources\QdbQuestionResource\Pages;


use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\QdbQuestionResource;

class ListQdbQuestions extends ListRecords
{
    protected static string $resource = QdbQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
