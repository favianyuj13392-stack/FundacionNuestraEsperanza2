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
    protected static ?string $navigationGroup = 'Identity & Social';
    protected static ?string $navigationLabel = 'General Settings';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Section::make('Detalles de la Configuración')
                ->schema([
                    // La clave solo se lee, no se toca para no romper la web
                    Forms\Components\TextInput::make('key')
                        ->label('Nombre del ajuste')
                        ->formatStateUsing(fn ($state) => str_replace('_', ' ', ucfirst($state)))
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Hidden::make('type'),
                    // Campo de texto: Solo aparece si el tipo NO es imagen
                    // Aquí aparecerá tu link de Facebook, Instagram, etc.
                    Forms\Components\TextInput::make('value')
                        ->label('Contenido / Enlace')
                        ->required()
                        ->visible(fn ($record) => $record && $record->type !== 'image')
                        ->placeholder('Introduce el link o texto aquí...'),

                    // Campo de imagen: Solo aparece si el tipo ES imagen (como el logo)
                    Forms\Components\FileUpload::make('value')
                        ->label('Imagen / Logo')
                        ->disk('public')
                        ->directory('settings')
                        ->image()
                        ->visible(fn ($record) => $record && $record->type === 'image')
                        ->dehydrated(fn ($state) => filled($state)),
                    
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Tables\Columns\TextColumn::make('key')
                ->label('Configuración')
                ->formatStateUsing(fn ($state) => $state ? str_replace('_', ' ', ucfirst($state)) : 'Sin clave')
                ->searchable(),
        

            Tables\Columns\ImageColumn::make('value')
                ->label('Imagen / Logo')
                ->disk('public')
                // Usamos el operador null-safe (?->) por seguridad
                ->visible(fn ($record) => $record?->type === 'image')
                ->defaultImageUrl(url('/images/placeholder.png'))
                ->size(40), // Evita el icono de imagen rota

            // Columna de Texto: Solo se activa si el tipo NO es image
            Tables\Columns\TextColumn::make('value_text') 
                ->label('Contenido / Enlace')
                ->state(fn ($record) => $record?->type !== 'image' ? $record->value : null)
                ->limit(40)
                ->visible(fn ($record) => $record?->type !== 'image')
                ->placeholder('Sin valor asignado'), // Maneja el null visualmente
        ])
        ->actions([
            Tables\Actions\EditAction::make()->label('Editar'),
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
