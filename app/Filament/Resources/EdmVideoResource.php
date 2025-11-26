<?php
namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Support\S3Path;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Edm\EdmVideo;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\EdmVideoResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Filament\Resources\EdmVideoResource\Widgets\EdmVideoStats;

class EdmVideoResource extends Resource
{
    protected static ?string $model = EdmVideo::class;

    protected static ?string $navigationIcon  = 'heroicon-o-play-circle';
    protected static ?string $navigationGroup = 'Edifie-moi';
    protected static ?string $navigationLabel = 'Vidéos courtes';
    protected static ?string $modelLabel      = 'Vidéo courte';

    public static function form(Form $form): Form
    {
        return $form
    ->schema([
        // 1. Informations générales
        Forms\Components\Section::make('Informations générales')
            ->columns(2)
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Toggle::make('status')
                            ->label('Actif ?')
                            ->default(true)
                            ->helperText('Si désactivé, la vidéo ne sera pas visible dans l’application.'),

                        Forms\Components\Toggle::make('featured')
                            ->label('Mise en avant ?')
                            ->helperText('Permet de mettre la vidéo en avant sur la page d’accueil / carrousel.'),

                        Forms\Components\TextInput::make('title')
                            ->label('Titre')
                            ->maxLength(255)
                            ->required()
                            ->helperText('Titre principal de la vidéo, affiché aux utilisateurs.'),
                            Forms\Components\Textarea::make('description')
                                ->label('Description')
                                ->rows(3)
                                ->columnSpanFull()
                                ->helperText('Texte explicatif ou résumé de la vidéo (facultatif).'),
                    ]),

            ]),

        // 2. Auteur & catégorie
        Forms\Components\Section::make('Auteur & catégorie')
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('author_user_id')
                    ->label('Auteur (utilisateur)')
                    ->options(
                        User::query()
                            ->orderBy('firstname')
                            ->pluck('firstname', 'id')
                            ->toArray()
                    )
                    ->searchable()
                    ->placeholder('— Sélectionner un auteur —')
                    // ->reactive()
                    // ->afterStateUpdated(function ($state, Set $set) {
                    //     $user = User::find($state);
                    //     $set('author_name', $user?->firstname . ' ' . $user?->lastname);
                    // })
                    ->helperText('Choisissez l’utilisateur qui est à l’origine de cette vidéo.'),

                Forms\Components\TextInput::make('author_name')
                    ->label('Nom affiché')
                    ->maxLength(191)
                    ->helperText('Nom qui sera affiché publiquement (peut être différent du compte utilisateur).'),

                Forms\Components\Select::make('category_id')
                    ->label('Catégorie')
                    ->relationship('categorie', 'cat_name')
                    ->searchable()
                    ->required()
                    ->helperText('Choisissez la catégorie dans laquelle sera classée cette vidéo.'),
            ]),

        // 3. Média (vidéo + image de couverture)
        Forms\Components\Section::make('Média')
            ->columns(2)
            ->schema([
                FileUpload::make('url_video')
                    ->label('Vidéo')
                    ->disk('s3')
                    ->directory('videos')
                    ->visibility('private')  // ou 'public' selon ton usage
                    ->acceptedFileTypes([
                        'video/mp4', 'video/mpeg', 'video/quicktime',
                    ])
                    ->maxSize(102400) // 100 Mo
                    ->preserveFilenames(false)
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file) =>
                            Str::ulid() . '.' . $file->getClientOriginalExtension()
                    )
                    ->helperText('Téléverse la vidéo (mp4, mpeg ou quicktime). Taille max : 100 Mo.'),

                FileUpload::make('cover_url')
                    ->label('Image de couverture')
                    ->disk('s3')
                    ->directory('edm/covers')
                    ->visibility('private')  // ou 'public'
                    ->image()
                    ->imageEditor()
                    ->maxSize(4096) // 4 Mo
                    ->preserveFilenames(false)
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file) =>
                            Str::ulid() . '.' . $file->getClientOriginalExtension()
                    )
                    ->helperText('Image qui sera affichée en vignette de la vidéo (max 4 Mo).'),
            ]),

        // 4. Métadonnées
        Forms\Components\Section::make('Métadonnées')
            ->columns(3)
            ->schema([
                Forms\Components\TextInput::make('duration_sec')
                    ->label('Durée (sec)')
                    ->numeric()
                    ->helperText('Durée totale de la vidéo en secondes (facultatif, peut être calculé par script).'),

                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Date de publication')
                    ->seconds(false)
                    ->helperText('Date/heure à laquelle la vidéo est considérée comme publiée.'),

                Forms\Components\TagsInput::make('hashtags_json')
                    ->label('Hashtags')
                    ->helperText('Mots-clés pour faciliter la recherche. Ex : #foi, #jesus, #edifiemoi'),
            ]),

        // 5. Statistiques (lecture seule)
        Forms\Components\Section::make('Statistiques')
            ->columns(4)
            ->schema([
                Forms\Components\TextInput::make('likes_count')
                    ->label('Likes')
                    ->disabled()
                    ->helperText('Nombre total de likes reçus.'),

                Forms\Components\TextInput::make('favorites_count')
                    ->label('Favoris')
                    ->disabled()
                    ->helperText('Nombre d’utilisateurs ayant ajouté la vidéo en favori.'),

                Forms\Components\TextInput::make('comments_count')
                    ->label('Commentaires')
                    ->disabled()
                    ->helperText('Nombre de commentaires liés à cette vidéo.'),

                Forms\Components\TextInput::make('shares_count')
                    ->label('Partages')
                    ->disabled()
                    ->helperText('Nombre de fois où la vidéo a été partagée.'),
            ]),
    ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
               ImageColumn::make('cover_url')
                ->label('Couverture')
                ->disk('s3')      // ⬅️ Filament va faire Storage::disk('s3')->url($state)
                ->size(64)
                ->circular()->visibility('private')
                ->defaultImageUrl(asset('assets/images/avatar-default.png')),
                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('video')
                    ->label('Vidéo')
                    ->formatStateUsing(fn() => '▶️ Voir')
                    ->url(fn($record) => $record->video_url_full)
                    ->openUrlInNewTab(),
                // TextColumn::make('video_link')
                //     ->label('Vidéo')
                //     ->formatStateUsing(fn() => '▶️ Voir')
                //     ->url(fn($record) => S3Path::urlFromKey(
                //         S3Path::keyFromDb($record->url_video)
                //     ))
                //     ->openUrlInNewTab(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn($state) => $state ? 'Actif' : 'Inactif')
                    ->colors([
                        'success' => fn($state) => $state == 1,
                        'danger'  => fn($state)  => $state == 0,
                    ]),

                Tables\Columns\IconColumn::make('featured')
                    ->label('En avant')
                    ->boolean(),

                Tables\Columns\TextColumn::make('likes_count')
                    ->label('Likes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('favorites_count')
                    ->label('Favoris')
                    ->sortable(),

                Tables\Columns\TextColumn::make('comments_count')
                    ->label('Coms')
                    ->sortable(),

                Tables\Columns\TextColumn::make('shares_count')
                    ->label('Partages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publiée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label('Actif ?')
                    ->trueLabel('Actifs')
                    ->falseLabel('Inactifs')
                    ->queries(
                        true: fn($query)  => $query->where('status', 1),
                        false: fn($query) => $query->where('status', 0),
                        blank: fn($query) => $query
                    ),

                Tables\Filters\Filter::make('featured')
                    ->label('Mises en avant')
                    ->query(fn($query) => $query->where('featured', 1)),

                Tables\Filters\Filter::make('top_videos')
                    ->label('Top vidéos (likes/favoris/partages)')
                    ->query(fn($query) => $query->orderByDesc('likes_count')
                            ->orderByDesc('favorites_count')
                            ->orderByDesc('shares_count')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Action rapide pour activer/désactiver
                Tables\Actions\Action::make('toggleStatus')
                    ->label('Activer / Désactiver')
                    ->icon('heroicon-o-power')
                    ->action(function (EdmVideo $record) {
                        $record->status = $record->status ? 0 : 1;
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEdmVideos::route('/'),
            'create' => Pages\CreateEdmVideo::route('/create'),
            'edit'   => Pages\EditEdmVideo::route('/{record}/edit'),
        ];
    }
    public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            Section::make('Informations générales')
                ->schema([
                    TextEntry::make('title')->label('Titre'),
                    TextEntry::make('description')->label('Description'),
                ])
                ->columns(2),

            Section::make('Média')
                ->schema([
                    ImageEntry::make('cover_url')
                        ->label('Couverture')
                        ->disk('s3')
                        ->height(200)
                        ->visible(fn ($record) => !empty($record->cover_url)),

                    TextEntry::make('url_video')
                        ->label('Vidéo')
                        ->formatStateUsing(fn ($state) => basename($state)),
                ])
                ->columns(2),
        ]);
}
 public static function getNavigationBadge(): ?string
    {
        $count = EdmVideo::query()->where('status', 1)->count(); // vidéos actives
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
     public static function getHeaderWidgets(): array
    {
        return [
            EdmVideoStats::class,
        ];
    }
}
