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
use Awcodes\Curator\Components\Forms\CuratorPicker;

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;
    protected static ?string $navigationGroup = 'Section';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // BLOQUE 1: Identificación y Estado
                Forms\Components\Section::make('Section Settings')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('identifier')
                            ->label('Identifier')
                            ->helperText('Name unique for internal use, e.g. "programs", "about_us".')
                            ->required()
                            ->disabled(fn ($record) => $record !== null)
                            ->dehydrated(),
                        
                        Forms\Components\TextInput::make('order')
                            ->label('Order')
                            ->numeric()
                            ->default(fn () => HomeSection::max('order') + 1)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Is Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                // BLOQUE 2: Contenido Visual (RF-05)
                Forms\Components\Section::make('Section Content')
                    ->description('Personalice visual content for this section.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Name In Display')
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Title'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Content'),

                        CuratorPicker::make('image')
                            ->label('Principal Image')
                            ->buttonLabel('Select Image')
                            ->size('sm')
                            ->constrained(true),
                    ]),

                // BLOQUE 3: SEO (RF-06)
                Forms\Components\Section::make('SEO Optimization')
                    ->description('Optional SEO settings to improve search engine visibility.')
                    ->icon('heroicon-o-magnifying-glass')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->placeholder('Ej: Fundación Esperanza | Programa Alimentario'),
                        
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
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
