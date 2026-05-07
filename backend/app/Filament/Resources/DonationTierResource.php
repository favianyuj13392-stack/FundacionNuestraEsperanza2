<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationTierResource\Pages;
use App\Models\DonationTier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DonationTierResource extends Resource
{
    protected static ?string $model = DonationTier::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Donations';
    protected static ?string $modelLabel = 'Donation Tier';
    protected static ?string $pluralModelLabel = 'Donation Tiers';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('Bs')
                    ->label('Monto'),
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->maxLength(255)
                    ->label('Etiqueta (Ej: Padrino)'),
                Forms\Components\TextInput::make('order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->label('Orden de aparición'),
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true)
                    ->label('Activo'),
                // Bloqueado a BOB (2) por seguridad. El Gateway BNB procesa en Bolivianos.
                Forms\Components\Hidden::make('currency_id')
                    ->default(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->label('Orden'),
                Tables\Columns\TextColumn::make('amount')
                    ->money('BOB')
                    ->sortable()
                    ->label('Monto'),
                Tables\Columns\TextColumn::make('label')
                    ->searchable()
                    ->label('Etiqueta'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Activo'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order', 'asc')
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
            'index' => Pages\ListDonationTiers::route('/'),
            'create' => Pages\CreateDonationTier::route('/create'),
            'edit' => Pages\EditDonationTier::route('/{record}/edit'),
        ];
    }
}
