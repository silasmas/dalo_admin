<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Tables\Actions;
use Filament\Tables\Columns;
use App\Models\Gallery\GalImage;
use Filament\Resources\Resource;
use App\Models\Gallery\GalGallery;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\FileUpload;
use App\Filament\Resources\GalImageResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Filament\Resources\GalImageResource\Widgets\GalleryStats;

class GalImageResource extends Resource
{
    protected static ?string $model = GalImage::class;

    protected static ?string $navigationGroup = 'Galerie';
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'Images';
    protected static ?string $modelLabel = 'Image';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('Image')
            ->columns(2)
            ->schema([
                // Champ pour UPLOAD MULTIPLE (création uniquement)
                Forms\Components\Select::make('gallery_id')
                    ->label('Galerie parente')
                    ->relationship('gallery', 'title') // ⬅️ adapte si c’est 'name'
                    ->searchable()
                    ->required(),

                Forms\Components\Toggle::make('status')
                    ->label('Active ?')
                    ->default(true),
        FileUpload::make('images')
            ->label('Images à téléverser')
            ->disk('s3')
            ->directory('galleries/images')
            ->visibility('private')    // ou 'public' si tu préfères
            ->image()
            ->imageEditor()
            ->multiple()               // ⬅️ important
            ->maxSize(8192)           // 8 Mo par image
            ->preserveFilenames(false)
            ->getUploadedFileNameForStorageUsing(
                fn (TemporaryUploadedFile $file) =>
                    Str::ulid().'.'.$file->getClientOriginalExtension()
            )
            ->required(fn ($livewire) => $livewire instanceof Pages\CreateGalImage)
            ->hidden(fn ($livewire) => $livewire instanceof Pages\EditGalImage)
            ->helperText('Tu peux sélectionner plusieurs images à la fois. Elles seront toutes ajoutées à la galerie sélectionnée.'),

        // Champ pour une seule image (édition uniquement)
        FileUpload::make('file_url')
            ->label('Fichier image')
            ->disk('s3')
            ->directory('galleries/images')
            ->visibility('private')
            ->image()
            ->imageEditor()
            ->maxSize(8192)
            ->preserveFilenames(false)
            ->getUploadedFileNameForStorageUsing(
                fn (TemporaryUploadedFile $file) =>
                    Str::ulid().'.'.$file->getClientOriginalExtension()
            )
            ->required(fn ($livewire) => $livewire instanceof Pages\EditGalImage)
            ->hidden(fn ($livewire) => $livewire instanceof Pages\CreateGalImage)
            ->helperText('Utilisé uniquement pour modifier une image existante.'),

        Forms\Components\DateTimePicker::make('taken_at')
            ->label('Date de prise de vue')
            ->seconds(false)
            ->nullable(),

        Forms\Components\TextInput::make('bytes')
            ->label('Taille (bytes)')
            ->numeric()
            ->disabled(),

        Forms\Components\TextInput::make('width')
            ->label('Largeur (px)')
            ->numeric()
            ->disabled(),

        Forms\Components\TextInput::make('height')
            ->label('Hauteur (px)')
            ->numeric()
            ->disabled(),
            ]),
            ]);
    }

    public static function table(Table $table): Table
    {
 return $table
        ->columns([
            Columns\ImageColumn::make('file_url')
                ->label('Image')
                ->disk('s3')
                 ->size(64)->visibility('private')
                ->defaultImageUrl(asset('assets/images/avatar-default.png'))
                ->size(60)
                ->square(),

            Columns\TextColumn::make('title')
                ->label('Titre')
                ->limit(30)
                ->searchable(),

            Columns\TextColumn::make('gallery.title')
                ->label('Galerie')
                ->sortable()
                ->searchable(),

            Columns\IconColumn::make('status')
                ->label('Active ?')
                ->boolean(),
        ])

        // 🔹 Groupement par galerie parente
        ->groups([
            Group::make('gallery.title')
                ->label('Galerie')
                ->collapsible(),   // possibilité de plier/déplier
        ])
        ->defaultGroup('gallery.title')

        ->actions([
            // Action pour ouvrir la galerie en slider
            Actions\Action::make('preview')
                ->label('Voir')
                ->icon('heroicon-o-eye')
                ->modalHeading(fn (GalImage $record) =>
                    'Galerie : ' . ($record->gallery->title ?? 'Sans titre')
                )
                ->modalContent(fn (GalImage $record) =>
                    view('filament.galleries.preview-slider', [
                        'images'    => $record->gallery
                            ? $record->gallery->images()->where('status', 1)->orderBy('id')->get()
                            : collect([$record]),
                        'currentId' => $record->id,
                    ])
                )
                ->modalWidth('4xl'),

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ])
        ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGalImages::route('/'),
            'create' => Pages\CreateGalImage::route('/create'),
            'edit'   => Pages\EditGalImage::route('/{record}/edit'),
        ];
    }
     public static function getNavigationBadge(): ?string
    {
        $count = GalImage::query()->where('status', 1)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
     public static function getHeaderWidgets(): array
    {
        return [
            GalleryStats::class,
        ];
    }
}
