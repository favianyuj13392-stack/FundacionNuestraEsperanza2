<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatResource\Pages;
use App\Filament\Resources\StatResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class StatResource extends Resource
{
    protected static ?string $model = Setting::class; 
    protected static ?string $navigationGroup = 'Contenido Web';
    protected static ?string $navigationLabel = 'Contadores / Stats';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Identificador Interno (Ej: stats_donantes)')
                    ->required()
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('value')
                    ->label('Cantidad')
                    ->numeric()
                    ->required(),
                Forms\Components\Hidden::make('group')->default('Estadísticas'),
                Forms\Components\Hidden::make('type')->default('number'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('group', 'Estadísticas'))
            ->columns([
                Tables\Columns\TextColumn::make('key')->label('Estadística'),
                Tables\Columns\TextColumn::make('value')->label('Cantidad'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStats::route('/'),
            'create' => Pages\CreateStat::route('/create'),
            'edit' => Pages\EditStat::route('/{record}/edit'),
        ];
    }
}
