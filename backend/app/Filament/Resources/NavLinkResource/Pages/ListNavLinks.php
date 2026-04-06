<?php

namespace App\Filament\Resources\NavLinkResource\Pages;

use App\Filament\Resources\NavLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNavLinks extends ListRecords
{
    protected static string $resource = NavLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
