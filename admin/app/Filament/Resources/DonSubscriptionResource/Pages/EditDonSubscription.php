<?php

namespace App\Filament\Resources\DonSubscriptionResource\Pages;

use App\Filament\Resources\DonSubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonSubscription extends EditRecord
{
    protected static string $resource = DonSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
