<?php

namespace App\Filament\Resources\MsgMessageResource\Pages;

use App\Filament\Resources\MsgMessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMsgMessage extends EditRecord
{
    protected static string $resource = MsgMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
