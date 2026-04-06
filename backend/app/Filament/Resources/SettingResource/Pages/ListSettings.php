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
                
            'Estadísticas' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'Estadísticas'))
                ->icon('heroicon-m-chart-bar'),
                
            'Textos Institucionales' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'Textos Institucionales'))
                ->icon('heroicon-m-document-text'),
        ];
    }
}
