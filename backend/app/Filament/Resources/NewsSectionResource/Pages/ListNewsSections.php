<?php

namespace App\Filament\Resources\NewsSectionResource\Pages;

use App\Filament\Resources\NewsSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNewsSections extends ListRecords
{
    protected static string $resource = NewsSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
