<?php

namespace App\Filament\Resources\DonDonationResource\Pages;

use App\Filament\Resources\DonDonationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonDonation extends EditRecord
{
    protected static string $resource = DonDonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
