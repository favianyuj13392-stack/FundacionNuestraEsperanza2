<?php

namespace App\Filament\Resources\NewsSectionResource\Pages;

use App\Filament\Resources\NewsSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNewsSection extends CreateRecord
{
    protected static string $resource = NewsSectionResource::class;
}
