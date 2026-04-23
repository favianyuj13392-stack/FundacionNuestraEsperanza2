<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSectionResource\Pages;
use App\Filament\Resources\HomeSectionResource\RelationManagers;
use App\Models\HomeSection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;
    protected static ?string $navigationGroup = 'Sección';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('Identificación de la Sección')
                ->schema([
                    Forms\Components\TextInput::make('identifier')
                        ->label('Identificador Técnico (identifier)')
                        ->helperText('Debe coincidir con el nombre en el código (ej: "hero", "stats")')
                        ->required()
                        ->unique(table: 'home_sections', column: 'identifier', ignoreRecord: true)
                        ->disabled(fn ($record) => $record !== null) 
                        ->dehydrated(), 

                    Forms\Components\TextInput::make('title')
                        ->label('Nombre de la Sección (Para el Admin)')
                        ->required(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('¿Sección Visible?')
                        ->default(true)
                        ->onColor('success'),

                    Forms\Components\Select::make('order')
                        ->label('Posición en la página')
                        ->options(array_combine(range(1, 20), range(1, 20))) // Genera opciones del 1 al 20
                        ->default(fn () => \App\Models\HomeSection::max('order') + 1) // Sugiere el siguiente
                        ->required(),
                    CuratorPicker::make('image')
                    ->label('Imagen de la Sección')
                    ->buttonLabel('Seleccionar de la Biblioteca')
                    ->directory('home-sections') // RF-05: Carpeta organizada
                    ->imageCropAspectRatio('16:9'), // RNF-06: Optimización
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Sección de Inicio'),
            
            // ¡Magia! ToggleColumn permite prender y apagar directo desde la tabla
            ToggleColumn::make('is_active')
                ->label('Visible'),
        ])
        ->actions([]) // Puedes quitar EditAction porque el ToggleColumn ya hace el trabajo
        ->bulkActions([]);
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
            'index' => Pages\ListHomeSections::route('/'),
            'create' => Pages\CreateHomeSection::route('/create'),
            'edit' => Pages\EditHomeSection::route('/{record}/edit'),
        ];
    }
}
