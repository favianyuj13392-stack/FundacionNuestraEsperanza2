<?php

namespace App\Filament\Resources\DonationTierResource\Pages;

use App\Filament\Resources\DonationTierResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDonationTiers extends ListRecords
{
    protected static string $resource = DonationTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos'),
            'bob' => Tab::make('🇧🇴 Bolivianos (BOB)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('currency_id', 2)),
            'usd' => Tab::make('🇺🇸 Dólares (USD)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('currency_id', 1)),
        ];
    }
}
