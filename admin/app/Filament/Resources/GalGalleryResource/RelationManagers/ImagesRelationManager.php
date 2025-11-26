<?php

namespace App\Filament\Resources\GalGalleryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';   // correspond à ->images() dans GalGallery
    protected static ?string $recordTitleAttribute = 'title';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Titre')
                    ->maxLength(255),

                Forms\Components\Textarea::make('caption')
                    ->label('Légende')
                    ->rows(3),

                Forms\Components\TextInput::make('alt_text')
                    ->label('Alt (accessibilité)')
                    ->maxLength(255),

                Forms\Components\FileUpload::make('file_url')
                    ->label('Image')
                    ->disk('s3')
                    ->directory('galleries/images')
                    ->visibility('private')
                    ->image()
                    ->imageEditor()
                    ->maxSize(8192)
                    ->preserveFilenames(false)
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file) =>
                            Str::ulid() . '.' . $file->getClientOriginalExtension()
                    )
                    ->required()
                    ->helperText('Image stockée sur S3.'),

                Forms\Components\DateTimePicker::make('taken_at')
                    ->label('Pris le')
                    ->seconds(false),

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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('file_url')
                    ->label('Image')
                    ->disk('s3')
                 ->visibility('private')
                ->defaultImageUrl(asset('assets/images/avatar-default.png'))
                    ->size(64),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->limit(30),

                Tables\Columns\TextColumn::make('caption')
                    ->label('Légende')
                    ->limit(40),

                Tables\Columns\TextColumn::make('taken_at')
                    ->label('Pris le')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('bytes')
                    ->label('Taille (KB)')
                    ->formatStateUsing(fn ($state) => $state ? round($state / 1024) . ' KB' : null),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->hydrateMeta($data);
    }

    public function mutateFormDataBeforeSave(array $data): array
    {
        return $this->hydrateMeta($data);
    }

    protected function hydrateMeta(array $data): array
    {
        // À compléter plus tard si tu veux remplir bytes/width/height automatiquement
        return $data;
    }
}
