<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramsSectionResource\Pages;
use App\Filament\Resources\ProgramsSectionResource\RelationManagers;
use App\Models\ProgramsSection;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\TextInput;

class ProgramsSectionResource extends Resource
{
    protected static ?string $model = ProgramsSection::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Section';
    protected static ?string $modelLabel = 'Programs Section';
    protected static ?string $pluralModelLabel = 'Programs Sections';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Section Settings')
                ->columns(2)
                ->schema([
                    TextInput::make('identifier')
                        ->label('Identifier')
                        ->helperText('Unique internal name used by the frontend, e.g. "programs".')
                        ->required()
                        ->disabled(fn ($record) => $record !== null),

                    TextInput::make('order')
                        ->label('Order')
                        ->numeric()
                        ->default(fn () => ProgramsSection::max('order') + 1)
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
                        ->disk('cloudinary')
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
            'index' => Pages\ListProgramsSections::route('/'),
            'create' => Pages\CreateProgramsSection::route('/create'),
            'edit' => Pages\EditProgramsSection::route('/{record}/edit'),
        ];
    }
}
