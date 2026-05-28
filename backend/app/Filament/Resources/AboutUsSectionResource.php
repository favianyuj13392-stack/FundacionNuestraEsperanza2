<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AboutUsSectionResource\Pages;
use App\Filament\Resources\AboutUsSectionResource\RelationManagers;
use App\Models\AboutUsSection;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class AboutUsSectionResource extends Resource
{
    protected static ?string $model = AboutUsSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Section';
    protected static ?string $modelLabel = 'About Us Section';
    protected static ?string $pluralModelLabel = 'About Us Sections';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Section Settings')
                ->columns(2)
                ->schema([
                    TextInput::make('identifier')
                        ->label('Identifier')
                        ->helperText('Unique internal name used by the frontend, e.g. "about_us".')
                        ->required()
                        ->disabled(fn ($record) => $record !== null),

                    TextInput::make('order')
                        ->label('Order')
                        ->numeric()
                        ->default(fn () => AboutUsSection::max('order') + 1)
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
            'index' => Pages\ListAboutUsSections::route('/'),
            'create' => Pages\CreateAboutUsSection::route('/create'),
            'edit' => Pages\EditAboutUsSection::route('/{record}/edit'),
        ];
    }
}
