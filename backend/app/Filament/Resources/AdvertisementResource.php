<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdvertisementResource\Pages;
use App\Filament\Resources\AdvertisementResource\RelationManagers;
use App\Models\Advertisement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Awcodes\Curator\Components\Tables\CuratorColumn;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Illuminate\Support\Str;

class AdvertisementResource extends Resource
{
    protected static ?string $model = Advertisement::class;
    protected static ?string $navigationGroup = 'Web Content';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Contenido del Anuncio')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required(),

                        CuratorPicker::make('image')
                            ->label('Images')
                            ->columnSpanFull()
                            ->directory('advertisements'),

                        RichEditor::make('content')
                            ->label('Text')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('title')->required(),
                    ]),

                Section::make('Configuración y Fechas')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('¿Activo ahora?')
                            ->default(true),
                        
                        DateTimePicker::make('starts_at')
                            ->label('Publicar desde'),

                        DateTimePicker::make('ends_at')
                            ->label('Vence el (Opcional)'),
                    ])
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order') 
            ->defaultSort('order', 'asc')
            ->columns([
                // RF-05: Mostrar la miniatura de la imagen desde Curator
                CuratorColumn::make('image')
                    ->label('Image')
                    ->size(40),
                Tables\Columns\TextColumn::make('order')
                    ->label('Order')
                    ->badge(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                // Toggle directo en la tabla para activar/desactivar rápido
                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('starts_at')
                    ->label('Starts At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Just Active'),
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
            'index' => Pages\ListAdvertisements::route('/'),
            'create' => Pages\CreateAdvertisement::route('/create'),
            'edit' => Pages\EditAdvertisement::route('/{record}/edit'),
        ];
    }
}
