<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AllianceResource\Pages;
use App\Filament\Resources\AllianceResource\RelationManagers;
use App\Models\Alliance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class AllianceResource extends Resource
{
    protected static ?string $model = Alliance::class;
    protected static ?string $navigationGroup = 'Web Content';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Organización')
                    ->required(),
                CuratorPicker::make('logo')
                    ->label('Logotipo de la Alianza')
                    ->required()
                    ->constrained(true),
                Forms\Components\TextInput::make('url')
                    ->label('Enlace Web (URL)')
                    ->url(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Alianza Activa')
                    ->default(true),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Organization')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                // Mostramos una miniatura del logo si existe
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(30),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAlliances::route('/'),
            'create' => Pages\CreateAlliance::route('/create'),
            'edit' => Pages\EditAlliance::route('/{record}/edit'),
        ];
    }
}
