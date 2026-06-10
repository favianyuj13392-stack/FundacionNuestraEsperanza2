<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Filament\Resources\CampaignResource\RelationManagers;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Campaign::class, 'slug', ignoreRecord: true),
                Forms\Components\Select::make('type')
                    ->options([
                        'general' => 'General',
                        'specific' => 'Específica',
                        'emergency' => 'Emergencia',
                    ])
                    ->default('specific')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('currency_id')
                    ->relationship('currency', 'iso_code')
                    ->required(),
                Forms\Components\TextInput::make('monetary_goal')
                    ->numeric()
                    ->required(),
                Forms\Components\DatePicker::make('start_date'),
                Forms\Components\DatePicker::make('end_date'),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Activa',
                        'inactive' => 'Inactiva',
                        'completed' => 'Completada',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->columnSpanFull()
                    ->saveUploadedFileUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file) {
                        \Cloudinary\Configuration\Configuration::instance([
                            'cloud' => [
                                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                                'api_key'    => env('CLOUDINARY_API_KEY'),
                                'api_secret' => env('CLOUDINARY_API_SECRET'),
                            ],
                            'url' => [
                                'secure' => true
                            ]
                        ]);

                        $upload = new \Cloudinary\Api\Upload\UploadApi();
                        $result = $upload->upload($file->getRealPath(), [
                            'folder' => 'campaigns',
                        ]);

                        return $result['secure_url'];
                    })
                    ->getUploadedFileUrlUsing(fn ($state) => $state)
                    ->deleteUploadedFileUsing(function ($state) {
                        if (!$state || !str_contains($state, 'cloudinary.com')) {
                            return;
                        }

                        \Cloudinary\Configuration\Configuration::instance([
                            'cloud' => [
                                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                                'api_key'    => env('CLOUDINARY_API_KEY'),
                                'api_secret' => env('CLOUDINARY_API_SECRET'),
                            ],
                            'url' => [
                                'secure' => true
                            ]
                        ]);

                        $parts = explode('/upload/', $state);
                        if (count($parts) === 2) {
                            $subParts = explode('/', $parts[1], 2);
                            if (count($subParts) === 2) {
                                $publicId = pathinfo($subParts[1], PATHINFO_DIRNAME) === '.' 
                                    ? pathinfo($subParts[1], PATHINFO_FILENAME) 
                                    : pathinfo($subParts[1], PATHINFO_DIRNAME) . '/' . pathinfo($subParts[1], PATHINFO_FILENAME);
                                
                                try {
                                    $upload = new \Cloudinary\Api\Upload\UploadApi();
                                    $upload->destroy($publicId);
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error('Error deleting image from Cloudinary: ' . $e->getMessage());
                                }
                            }
                        }
                    }),
                Forms\Components\FileUpload::make('report_pdf_path')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('campaign-reports')
                    ->label('Informe Final (PDF)')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path'),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monetary_goal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'completed' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            RelationManagers\ExpensesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
