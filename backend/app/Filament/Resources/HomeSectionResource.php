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

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
        TextInput::make('name')
            ->label('Nombre de la Sección')
            ->disabled(), // Deshabilitado para que el admin no lo cambie y rompa la web
        Toggle::make('is_active')
            ->label('¿Mostrar en la página de inicio?'),
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
