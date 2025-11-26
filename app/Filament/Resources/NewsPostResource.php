<?php
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\NewsPost;
use Filament\Forms\Form;
use App\Enums\BodyFormat;
use App\Enums\NewsStatus;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use App\Filament\Resources\NewsPostResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class NewsPostResource extends Resource
{
    protected static ?string $model           = NewsPost::class;
    protected static ?string $navigationIcon  = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Actualités';
    protected static ?string $modelLabel      = 'Actualité';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\Section::make('Contenu')->schema([
                    Forms\Components\TextInput::make('title')->label('Titre')->required()
                        ->helperText('Titre public de l’article.')
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                    Forms\Components\TextInput::make('slug')->label('Slug')->maxLength(191)
                        ->helperText('Chemin URL unique (auto-généré, modifiable).'),
                    Forms\Components\Textarea::make('summary')->label('Résumé')->rows(3)
                        ->helperText('Court résumé utilisé sur les listes et le SEO.'),
                    Forms\Components\Select::make('body_format')->label('Format du corps')
                        ->options(collect(BodyFormat::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all())
                        ->native(false)->required()
                        ->helperText('Choisissez comment vous allez saisir le contenu.'),
                    Forms\Components\RichEditor::make('body')->label('Corps (HTML/Markdown/Texte)')
                        ->helperText('Éditeur riche (si Markdown ou Texte, l’affichage front adaptera).')
                        ->columnSpanFull(),
                ])->columns(2)->columnSpan(8),

                Forms\Components\Section::make('Mise en avant & média')->schema([
                    Forms\Components\FileUpload::make('cover_url')
                        ->label('Image de couverture')
                        ->disk('s3')->directory('news/' . now()->format('Y/m'))
                        ->image()->imageEditor()->maxSize(4096)->visibility('private')
                        ->getUploadedFileNameForStorageUsing(
                                fn(TemporaryUploadedFile $file) =>
                                Str::ulid() . '.' . $file->getClientOriginalExtension()
                            )
                        ->helperText('Image affichée sur la carte/entête.'),
                    Forms\Components\Toggle::make('featured')->label('Mis en avant')->default(false)
                        ->helperText('Afficher en “Une” sur la page d’accueil.'),
                ])->columnSpan(4),

                Forms\Components\Section::make('Publication')->schema([
                    Forms\Components\Select::make('news_status')
                        ->label('Statut de publication')->native(false)->required()
                        ->options(collect(NewsStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all())
                        ->helperText('Brouillon, Planifiée, Publiée, Archivée.'),
                    Forms\Components\DateTimePicker::make('published_at')->label('Date de publication')->native(false),
                    Forms\Components\DateTimePicker::make('starts_at')->label('Début (événement)')->native(false),
                    Forms\Components\DateTimePicker::make('ends_at')->label('Fin (événement)')->native(false),
                    Forms\Components\DateTimePicker::make('expires_at')->label('Expire le')->native(false),
                    Forms\Components\TextInput::make('external_url')->label('URL externe')->maxLength(500)
                        ->helperText('Lien si l’article renvoie vers une source externe.'),
                    Forms\Components\TextInput::make('location_name')->label('Lieu')->maxLength(191),
                    Forms\Components\TextInput::make('location_addr')->label('Adresse')->maxLength(255),
                    Forms\Components\TextInput::make('category_id')->label('Catégorie (ID)')
                        ->numeric()->helperText('Sélectionne la catégorie (à remplacer par un Select si tu as la table).'),
                ])->columnSpan(8),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')->columns([
            Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
            Tables\Columns\ImageColumn::make('cover_url')->label('Cover')->size(48)->circular(),
            Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(40),
            Tables\Columns\BadgeColumn::make('news_status')
    ->label('Statut')
    ->formatStateUsing(function ($state) {
        // Si $state est déjà un enum, on le garde
        $status = $state instanceof \App\Enums\NewsStatus
            ? $state
            : \App\Enums\NewsStatus::tryFrom($state);

        // On renvoie le label de l’enum, ou la valeur brute en fallback
        return $status?->label() ?? (string) $state;
    })
    ->colors([
        'gray' => fn ($state) => (
            $state instanceof \App\Enums\NewsStatus
                ? $state
                : \App\Enums\NewsStatus::tryFrom($state)
        ) === \App\Enums\NewsStatus::Draft,

        'warning' => fn ($state) => (
            $state instanceof \App\Enums\NewsStatus
                ? $state
                : \App\Enums\NewsStatus::tryFrom($state)
        ) === \App\Enums\NewsStatus::Scheduled,

        'success' => fn ($state) => (
            $state instanceof \App\Enums\NewsStatus
                ? $state
                : \App\Enums\NewsStatus::tryFrom($state)
        ) === \App\Enums\NewsStatus::Published,

        'danger' => fn ($state) => (
            $state instanceof \App\Enums\NewsStatus
                ? $state
                : \App\Enums\NewsStatus::tryFrom($state)
        ) === \App\Enums\NewsStatus::Archived,
        ]),

            Tables\Columns\IconColumn::make('featured')->label('Une')->boolean(),
            Tables\Columns\TextColumn::make('published_at')->label('Publiée le')->dateTime('d/m/Y H:i')->sortable(),
            Tables\Columns\TextColumn::make('read_count')->label('Vues')->numeric()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('news_status')
                ->options(collect(NewsStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all()),
            Tables\Filters\TernaryFilter::make('featured')->label('Mis en avant'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
        ]);
    }

    // Audit
    public static function mutateFormDataBeforeCreate(array $data): array
    {$data['created_by'] = auth('admin')->id();return $data;}
    public static function mutateFormDataBeforeSave(array $data): array
    {$data['updated_by'] = auth('admin')->id();return $data;}

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNewsPosts::route('/'),
            'create' => Pages\CreateNewsPost::route('/create'),
            'view'   => Pages\ViewNewsPost::route('/{record}'),
            'edit'   => Pages\EditNewsPost::route('/{record}/edit'),
        ];
    }
}
