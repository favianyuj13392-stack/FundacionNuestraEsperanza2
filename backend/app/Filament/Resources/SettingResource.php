<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('group')
                    ->label('Grupo')
                    ->disabled(), // Lo deshabilitamos para que no lo cambien
                
                Forms\Components\TextInput::make('key')
                    ->label('Clave (No modificar)')
                    ->disabled(), 

                Forms\Components\Textarea::make('value')
                    ->label('Valor (Enlace o Número)')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Clave interna')
                    ->searchable()
                    ->color('gray'), // Lo ponemos gris para que no llame tanto la atención
                    
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor (Lo que se muestra en la web)')
                    ->limit(50)
                    ->searchable(),
            ])
            
            /*->defaultGroup(
                Tables\Grouping\Group::make('group')
                    ->label('Sección')
                    ->collapsible()
            )*/
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar'), // Un toque más en español
            ])
            ->bulkActions([
                // Vacío por seguridad
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
