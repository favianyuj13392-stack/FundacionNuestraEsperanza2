<?php

namespace App\Filament\Resources\TestimonialsSectionResource\Pages;

use App\Filament\Resources\TestimonialsSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestimonialsSections extends ListRecords
{
    protected static string $resource = TestimonialsSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
