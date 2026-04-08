<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NavLinkResource\Pages;
use App\Filament\Resources\NavLinkResource\RelationManagers;
use App\Models\NavLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NavLinkResource extends Resource
{
    protected static ?string $navigationGroup = 'Navegación';
    protected static ?string $label = 'Links del Menú';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\TextInput::make('url')->required(),
            Forms\Components\Select::make('location')
                ->options([
                    'header' => 'Solo Header',
                    'footer' => 'Solo Footer',
                    'both' => 'Ambos',
                ]),
            Forms\Components\TextInput::make('order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título del Enlace')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('url')
                    ->label('Ruta / URL')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('location')
                    ->label('Ubicación')
                    ->colors([
                        'primary' => 'both',
                        'success' => 'header',
                        'warning' => 'footer',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'header' => 'Solo Header',
                        'footer' => 'Solo Footer',
                        'both' => 'Ambos',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('order', 'asc') // Ordena por defecto basándose en la columna order
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListNavLinks::route('/'),
            'create' => Pages\CreateNavLink::route('/create'),
            'edit' => Pages\EditNavLink::route('/{record}/edit'),
        ];
    }
}
