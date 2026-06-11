<?php

namespace App\Filament\Resources\ProgramsSectionResource\Pages;

use App\Filament\Resources\ProgramsSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProgramsSections extends ListRecords
{
    protected static string $resource = ProgramsSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
