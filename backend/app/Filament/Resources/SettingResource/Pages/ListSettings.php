<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab; 
use Illuminate\Database\Eloquent\Builder;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            
            'Redes Sociales' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'Redes Sociales'))
                ->icon('heroicon-m-share'), // Ícono opcional bonito
                
            'Logo y nombre' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'General'))
                ->icon('heroicon-m-document-text'),
            
        ];
    }
}
