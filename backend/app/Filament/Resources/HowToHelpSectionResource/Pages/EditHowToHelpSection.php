<?php

namespace App\Filament\Resources\HowToHelpSectionResource\Pages;

use App\Filament\Resources\HowToHelpSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHowToHelpSection extends EditRecord
{
    protected static string $resource = HowToHelpSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
