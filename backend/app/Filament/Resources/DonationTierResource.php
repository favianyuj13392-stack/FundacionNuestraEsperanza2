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
                Forms\Components\Select::make('currency_id')
                    ->label('Moneda')
                    ->options([
                        1 => '🇧🇴 Bolivianos (BOB - Bs.)',
                        2 => '🇺🇸 Dólares Estadounidenses (USD - $)',
                    ])
                    ->default(1)
                    ->required()
                    ->live(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix(fn (Forms\Get $get) => $get('currency_id') == 2 ? '$' : 'Bs')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->label('Orden'),
                Tables\Columns\TextColumn::make('currency.iso_code')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'BOB' => 'warning',
                        'USD' => 'success',
                        default => 'gray',
                    })
                    ->label('Moneda'),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($record) => ($record->currency_id == 2 ? '$ ' : 'Bs ') . number_format((float)$record->amount, 2))
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
                Tables\Filters\SelectFilter::make('currency_id')
                    ->label('Filtrar por Moneda')
                    ->options([
                        1 => 'Bolivianos (BOB)',
                        2 => 'Dólares (USD)',
                    ]),
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
