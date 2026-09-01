<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationLabel = 'Suscripciones (Socios)';
    protected static ?string $pluralModelLabel = 'Suscripciones';
    protected static ?string $modelLabel = 'Suscripción';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('campaign_id')
                    ->relationship('campaign', 'name')
                    ->searchable()
                    ->nullable(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('BOB'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Activa',
                        'paused' => 'Pausada',
                        'cancelled' => 'Cancelada',
                        'failed' => 'Fallida',
                    ])
                    ->required(),
                Forms\Components\DateTimePicker::make('next_charge_date'),
                Forms\Components\DateTimePicker::make('last_charge_date'),
                Forms\Components\Textarea::make('cancellation_reason')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('cancelled_at'),
                Forms\Components\TextInput::make('cybersource_payment_token')
                    ->label('Cybersource Payment Token')
                    ->disabled()
                    ->dehydrated(false)
                    ->maxLength(255),
                Forms\Components\TextInput::make('reactivation_token')
                    ->label('Token de Reactivación Actual')
                    ->disabled()
                    ->dehydrated(false)
                    ->maxLength(255),
                Forms\Components\TextInput::make('failed_attempts_count')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Donante')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('campaign.name')
                    ->label('Campaña')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'cancelled' => 'danger',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('next_charge_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('failed_attempts_count')
                    ->label('Fallos')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('pause')
                    ->label('Pausar')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Subscription $record) => $record->status === 'active')
                    ->form([
                        Forms\Components\Select::make('days')
                            ->label('Tiempo de pausa')
                            ->options([
                                '30' => '30 Días',
                                '60' => '60 Días',
                            ])
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data) {
                        $days = (int) $data['days'];
                        $record->status = 'paused';
                        $record->next_charge_date = $record->next_charge_date ? $record->next_charge_date->addDays($days) : now()->addDays($days);
                        $record->save();

                        Notification::make()
                            ->title('Suscripción Pausada')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Subscription $record) => in_array($record->status, ['active', 'paused', 'failed']))
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Motivo de Cancelación')
                            ->required(),
                    ])
                    ->action(function (Subscription $record, array $data) {
                        $record->status = 'cancelled';
                        $record->cancellation_reason = $data['cancellation_reason'];
                        $record->cancelled_at = now();
                        $record->save();

                        Notification::make()
                            ->title('Suscripción Cancelada')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reactivate_link')
                    ->label('Generar Enlace de Reactivación')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->modalSubmitActionLabel('Listo')
                    ->modalCancelActionLabel('Cerrar')
                    ->visible(fn (Subscription $record) => in_array($record->status, ['cancelled', 'failed', 'paused']))
                    ->form(function (Subscription $record) {
                        if (!$record->reactivation_token || !$record->reactivation_token_expires_at || $record->reactivation_token_expires_at->isPast()) {
                            $token = Str::uuid()->toString();
                            $record->reactivation_token = $token;
                            $record->reactivation_token_expires_at = now()->addHours(72);
                            $record->save();
                        } else {
                            $token = $record->reactivation_token;
                        }

                        $url = "http://localhost:3000/donar?reactivate_token={$token}";

                        return [
                            Forms\Components\TextInput::make('reactivation_url')
                                ->label('Enlace de Reactivación Creado (Copiá y enviá por WhatsApp/Email)')
                                ->default($url)
                                ->readOnly()
                                ->extraInputAttributes(['onclick' => 'this.select(); document.execCommand("copy"); alert("¡Enlace copiado al portapapeles!");']),
                            Forms\Components\Placeholder::make('info')
                                ->content('Hacé clic en el campo de arriba para copiar el enlace automáticamente al portapapeles y enviarlo al donante.'),
                        ];
                    })
                    ->action(function () {
                        Notification::make()
                            ->title('Enlace Generado Correctamente')
                            ->body('Podés copiarlo y enviárselo directamente al donante por WhatsApp o correo.')
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
