<?php

namespace App\Filament\Resources\HowToHelpSectionResource\Pages;

use App\Filament\Resources\HowToHelpSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHowToHelpSections extends ListRecords
{
    protected static string $resource = HowToHelpSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
