<?php

namespace App\Filament\Resources\DonationTierResource\Pages;

use App\Filament\Resources\DonationTierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDonationTier extends EditRecord
{
    protected static string $resource = DonationTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
