<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Models\Gallery\GalGallery;
use App\Filament\Resources\GalGalleryResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Filament\Resources\GalImageResource\Widgets\GalleryStats;
use App\Filament\Resources\GalGalleryResource\RelationManagers\ImagesRelationManager as ImgRes;

class GalGalleryResource extends Resource
{
    protected static ?string $model = GalGallery::class;

    protected static ?string $navigationIcon  = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Galerie';
    protected static ?string $navigationLabel = 'Galeries photos';
    protected static ?string $modelLabel      = 'Galerie';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn ($state, callable $set, $get) =>
                                    $set('slug', $get('slug') ?: Str::slug($state))
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(191)
                            ->helperText('Identifiant unique pour l’URL, généré automatiquement si vide.'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Galerie parente')
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\Select::make('visibility')
                            ->label('Visibilité')
                            ->options([
                                'public'  => 'Publique',
                                'private' => 'Privée',
                            ])
                            ->default('public'),

                        Forms\Components\Toggle::make('status')
                            ->label('Active ?')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->rows(4),
                    ]),

                Forms\Components\Section::make('Image de couverture')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_url')
                            ->label('Cover')
                            ->disk('s3')
                            ->directory('galleries/covers')
                            ->visibility('private')
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096)
                            ->preserveFilenames(false)
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file) =>
                                    Str::ulid() . '.' . $file->getClientOriginalExtension()
                            )
                            ->helperText('Image de couverture de la galerie.'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_url')
                    ->label('Cover')
                     ->disk('s3')      // ⬅️ Filament va faire Storage::disk('s3')->url($state)
                ->size(64)->visibility('private')
                ->defaultImageUrl(asset('assets/images/avatar-default.png')),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('visibility')
                    ->label('Visibilité')
                    ->colors([
                        'success' => 'public',
                        'warning' => 'private',
                    ]),

                Tables\Columns\IconColumn::make('status')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('images_count')
                    ->label('Images')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Active ?')
                    ->trueLabel('Actives')
                    ->falseLabel('Inactives')
                    ->queries(
                        true: fn ($q) => $q->where('status', 1),
                        false: fn ($q) => $q->where('status', 0),
                        blank: fn ($q) => $q
                    ),

                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Visibilité')
                    ->options([
                        'public'  => 'Publique',
                        'private' => 'Privée',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

  public static function getRelations(): array
{
    return [
        \App\Filament\Resources\GalGalleryResource\RelationManagers\ImagesRelationManager::class,
    ];
}


    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGalGalleries::route('/'),
            'create' => Pages\CreateGalGallery::route('/create'),
            'edit'   => Pages\EditGalGallery::route('/{record}/edit'),
        ];
    }
     public static function getNavigationBadge(): ?string
    {
        $count = GalGallery::query()->where('status', 1)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }
     public static function getHeaderWidgets(): array
    {
        return [
            GalleryStats::class,
        ];
    }
}
