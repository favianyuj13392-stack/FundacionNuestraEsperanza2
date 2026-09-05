<?php

namespace App\Filament\Resources\DonationResource\Pages;

use App\Filament\Resources\DonationResource;
use App\Filament\Widgets\DonationOverviewWidget;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDonations extends ListRecords
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            DonationOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos'),
            'today' => Tab::make('📅 Hoy')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today())),
            'this_month' => Tab::make('🗓️ Este Mes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->startOfMonth())),
            'bob' => Tab::make('🇧🇴 Bolivianos (BOB)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('currency_id', 1)),
            'usd' => Tab::make('🇺🇸 Dólares (USD)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('currency_id', 2)),
            'recurring' => Tab::make('🔄 Recurrentes')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_recurring', true)),
            'qr' => Tab::make('📱 QR Simple')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('provider', 'bnb')),
            'card' => Tab::make('💳 Tarjeta ATC')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('provider', 'cybersource')),
        ];
    }
}