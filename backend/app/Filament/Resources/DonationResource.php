<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationResource\Pages;
use App\Models\Donation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Donations';
    protected static ?string $navigationLabel = 'Donaciones Recibidas';
    protected static ?string $pluralModelLabel = 'Donaciones Recibidas';
    protected static ?string $modelLabel = 'Donación';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles Financieros')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Monto')
                            ->disabled()
                            ->formatStateUsing(fn ($record) => $record ? ($record->currency_id == 2 ? '$ ' : 'Bs ') . number_format((float)$record->amount, 2) : ''),
                        Forms\Components\Select::make('currency_id')
                            ->label('Moneda')
                            ->options([
                                1 => 'Bolivianos (BOB)',
                                2 => 'Dólares (USD)',
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('status')
                            ->label('Estado')
                            ->disabled(),
                        Forms\Components\TextInput::make('provider')
                            ->label('Pasarela / Método')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'bnb' => 'BNB QR Simple',
                                'cybersource' => 'ATC Cybersource (Tarjeta)',
                                default => $state,
                            }),
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Es Donación Mensual Recurrente')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Fecha y Hora de Recepción')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Información del Donante y Campaña')
                    ->schema([
                        Forms\Components\TextInput::make('donor_name')
                            ->label('Nombre del Donante')
                            ->disabled()
                            ->formatStateUsing(function ($record) {
                                if (!$record) return '';
                                if ($record->donor) {
                                    return trim($record->donor->first_name . ' ' . $record->donor->last_name);
                                }
                                return $record->is_anonymous ? 'Donante Anónimo' : 'No especificado';
                            }),
                        Forms\Components\TextInput::make('donor_email')
                            ->label('Correo Electrónico')
                            ->disabled()
                            ->formatStateUsing(fn ($record) => $record?->donor?->email ?? ($record?->is_anonymous ? 'Anónimo' : 'No registrado')),
                        Forms\Components\TextInput::make('campaign_name')
                            ->label('Campaña Destino')
                            ->disabled()
                            ->formatStateUsing(fn ($record) => $record?->campaign?->name ?? 'Campaña General / Fondo Libre'),
                        Forms\Components\TextInput::make('provider_payment_id')
                            ->label('Referencia de Transacción / Voucher')
                            ->disabled(),
                        Forms\Components\TextInput::make('provider_subscription_id')
                            ->label('ID de Suscripción Recurrente')
                            ->disabled()
                            ->visible(fn ($record) => filled($record?->provider_subscription_id)),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('donor.first_name')
                    ->label('Donante')
                    ->formatStateUsing(function ($record) {
                        if ($record->donor) {
                            $name = trim($record->donor->first_name . ' ' . $record->donor->last_name);
                            return $name ?: $record->donor->email;
                        }
                        return $record->is_anonymous ? 'Anónimo' : 'Donante Web';
                    })
                    ->description(fn ($record) => $record->donor?->email ?? null)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('donor', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($record) => ($record->currency_id == 2 ? '$ ' : 'Bs ') . number_format((float)$record->amount, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('currency.iso_code')
                    ->label('Moneda')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'BOB' => 'warning',
                        'USD' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('provider')
                    ->label('Método')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bnb' => '📱 QR Simple',
                        'cybersource' => '💳 Tarjeta (ATC)',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'bnb' => 'info',
                        'cybersource' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('is_recurring')
                    ->label('Frecuencia')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? '🔄 Mensual' : '⚡ Única vez')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('campaign.name')
                    ->label('Campaña')
                    ->default('General / Libre')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'succeeded' => '✅ Exitosa',
                        'pending' => '⏳ Pendiente',
                        'failed' => '❌ Fallida',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'succeeded' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('provider_payment_id')
                    ->label('Referencia / Voucher')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // 1. Filtro de Temporalidad (Rango de Fechas)
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Desde Fecha'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Hasta Fecha'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                // 2. Filtro por Moneda
                Tables\Filters\SelectFilter::make('currency_id')
                    ->label('Moneda')
                    ->options([
                        1 => 'Bolivianos (BOB)',
                        2 => 'Dólares (USD)',
                    ]),

                // 3. Filtro por Método de Pago / Proveedor
                Tables\Filters\SelectFilter::make('provider')
                    ->label('Método de Pago')
                    ->options([
                        'bnb' => 'Código QR Simple (BNB)',
                        'cybersource' => 'Tarjeta de Crédito/Débito (ATC)',
                    ]),

                // 4. Filtro por Frecuencia
                Tables\Filters\SelectFilter::make('is_recurring')
                    ->label('Frecuencia')
                    ->options([
                        1 => 'Mensual (Recurrente)',
                        0 => 'Única vez',
                    ]),

                // 5. Filtro por Campaña
                Tables\Filters\SelectFilter::make('campaign_id')
                    ->label('Campaña')
                    ->relationship('campaign', 'name'),

                // 6. Filtro por Estado
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado de Pago')
                    ->options([
                        'succeeded' => 'Exitosa',
                        'pending' => 'Pendiente',
                        'failed' => 'Fallida',
                    ]),

                // 7. Filtro por Anonimato
                Tables\Filters\TernaryFilter::make('is_anonymous')
                    ->label('Anonimato')
                    ->placeholder('Todos los donantes')
                    ->trueLabel('Solo Anónimos')
                    ->falseLabel('Solo Identificados'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // No delete bulk for financial audit integrity
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
            'index' => Pages\ListDonations::route('/'),
            'view' => Pages\ViewDonation::route('/{record}'),
        ];
    }
}
