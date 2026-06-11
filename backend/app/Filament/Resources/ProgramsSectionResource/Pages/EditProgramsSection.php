<?php

namespace App\Filament\Resources\ProgramsSectionResource\Pages;

use App\Filament\Resources\ProgramsSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgramsSection extends EditRecord
{
    protected static string $resource = ProgramsSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
