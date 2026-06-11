<?php

namespace App\Filament\Resources\AboutUsSectionResource\Pages;

use App\Filament\Resources\AboutUsSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAboutUsSection extends EditRecord
{
    protected static string $resource = AboutUsSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
