<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsSectionResource\Pages;
use App\Filament\Resources\NewsSectionResource\RelationManagers;
use App\Models\NewsSection;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\TextInput;

class NewsSectionResource extends Resource
{
    protected static ?string $model = NewsSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Section';
    protected static ?string $modelLabel = 'News Section';
    protected static ?string $pluralModelLabel = 'News Sections';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Section Settings')
                ->columns(2)
                ->schema([
                    TextInput::make('identifier')
                        ->label('Identifier')
                        ->helperText('Unique internal name used by the frontend, e.g. "news".')
                        ->required()
                        ->disabled(fn ($record) => $record !== null),

                    TextInput::make('order')
                        ->label('Order')
                        ->numeric()
                        ->default(fn () => NewsSection::max('order') + 1)
                        ->required(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Is Active')
                        ->default(true)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Content')
                ->schema([
                    TextInput::make('name')
                        ->label('Display Name')
                        ->required(),

                    TextInput::make('title')
                        ->label('Title'),

                    TextInput::make('subtitle')
                        ->label('Subtitle'),

                    Forms\Components\RichEditor::make('content')
                        ->label('Content'),

                    CuratorPicker::make('image')
                        ->label('Section Image')
                        ->buttonLabel('Select Image')
                        ->size('sm')
                        ->constrained(true),
                ]),

            Forms\Components\Section::make('SEO')
                ->description('Optional SEO metadata for the section.')
                ->icon('heroicon-o-magnifying-glass')
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta Title'),

                    Forms\Components\Textarea::make('meta_description')
                        ->label('Meta Description')
                        ->rows(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->columns([
                TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Section')
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Is Active'),
            ])
            ->defaultSort('order', 'asc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
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
            'index' => Pages\ListNewsSections::route('/'),
            'create' => Pages\CreateNewsSection::route('/create'),
            'edit' => Pages\EditNewsSection::route('/{record}/edit'),
        ];
    }
}
