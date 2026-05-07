<?php

namespace App\Filament\Resources\DonationTierResource\Pages;

use App\Filament\Resources\DonationTierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDonationTiers extends ListRecords
{
    protected static string $resource = DonationTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
