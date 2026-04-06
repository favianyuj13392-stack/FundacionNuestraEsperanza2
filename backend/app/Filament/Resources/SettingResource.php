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
    protected static ?string $navigationGroup = 'Identidad y Redes';
    protected static ?string $navigationLabel = 'Configuración General';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Clave (No modificar)')
                    ->disabled()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('value')
                    ->label('Logo / Imagen')
                    ->disk('public')
                    ->directory('settings')
                    ->image()
                    ->visible(fn ($record) => $record?->type === 'image')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('value')
                    ->label('Valor (Texto/Enlace/Número)')
                    ->required()
                    ->visible(fn ($record) => $record?->type !== 'image')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('group', ['General', 'Redes Sociales']))
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
