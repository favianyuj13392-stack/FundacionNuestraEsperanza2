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
                    ->dehydrated()
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('Tipo de contenido')
                    ->options([
                        'text' => 'Texto',
                        'number' => 'Número',
                        'image' => 'Imagen',
                    ])
                    ->live() 
                    ->required(),
                Forms\Components\FileUpload::make('value')
                    ->label('Subir Logo / Imagen')
                    ->disk('public')
                    ->directory('settings')
                    ->image()
                    ->visible(fn (Forms\Get $get) => $get('type') === 'image')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('value')
                    ->label('Valor (Texto/Enlace/Número)')
                    ->required(fn (Forms\Get $get) => $get('type') !== 'image') 
                    ->visible(fn (Forms\Get $get) => $get('type') !== 'image')
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
                    ->color('gray'), 
                    
                // 1. Columna para la img
                Tables\Columns\ImageColumn::make('value_image')
                    ->label('Imagen')
                    ->state(fn ($record) => $record->type === 'image' ? $record->value : null)
                    ->disk('public'),

                // 2. Columna para el texto (Oculta el texto de la ruta si es imagen)
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor (Lo que se muestra en la web)')
                    ->limit(50)
                    ->searchable()
                    ->state(fn ($record) => $record->type !== 'image' ? $record->value : 'Imagen'),
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
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('group', ['General', 'Redes Sociales']);
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
