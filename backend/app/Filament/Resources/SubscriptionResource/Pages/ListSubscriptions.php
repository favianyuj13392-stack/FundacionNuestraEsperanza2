<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('Todas'),
            'active' => \Filament\Resources\Components\Tab::make('Activas')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'active')),
            'paused' => \Filament\Resources\Components\Tab::make('Pausadas')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'paused')),
            'cancelled' => \Filament\Resources\Components\Tab::make('Canceladas')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'cancelled')),
            'failed' => \Filament\Resources\Components\Tab::make('Fallidas')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'failed')),
        ];
    }
}
