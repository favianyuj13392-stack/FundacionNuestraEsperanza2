<?php

namespace App\Filament\Resources\NavLinkResource\Pages;

use App\Filament\Resources\NavLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNavLink extends EditRecord
{
    protected static string $resource = NavLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
