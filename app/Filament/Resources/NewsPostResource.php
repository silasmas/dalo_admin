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
use Illuminate\Support\Collection;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Actions\DeleteBulkAction;
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
                    // Forms\Components\TextInput::make('category_id')->label('Catégorie (ID)')
                    //     ->numeric()->helperText('Sélectionne la catégorie (à remplacer par un Select si tu as la table).'),
                    Forms\Components\Select::make('category_id')
    ->label('Catégorie')
    ->options(
        \App\Models\MainCategory::query()
            ->where('type', 'news_type')
            ->where('status', 1)
            ->orderBy('cat_name')
            ->pluck('cat_name', 'id')
            ->toArray()
    )
    ->searchable()
    ->preload()
    ->required()
    ->helperText('Choisissez une catégorie active pour cette actualité.'),

                ])->columnSpan(8),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {

         // 🔹 On prépare une seule fois la liste des statuts possibles
    $statusOptions = collect(\App\Enums\NewsStatus::cases())
        ->mapWithKeys(fn (\App\Enums\NewsStatus $case) => [
            $case->value => $case->label(),
        ])
        ->all();
        return $table->defaultSort('id', 'desc')->columns([
            Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
            // Tables\Columns\ImageColumn::make('cover_url')->label('Cover')->size(48)->circular(),
            ImageColumn::make('cover_url')
                ->label('Cover')
                ->disk('s3')
                 ->size(64)->visibility('private')
                ->defaultImageUrl(asset('assets/images/avatar-default.png'))
                ->size(60)
                ->square(),
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
// 🔽 Actif ou non (0 ou 1)
            SelectColumn::make('status')
                ->label('Actif ?')
                ->options([
                    1 => 'Actif',
                    0 => 'Inactif'
                ])
                ->selectablePlaceholder(false)
                ->sortable(),
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
        ])->bulkActions([
                // 👉 Groupé : icône “…” dans la barre des actions de masse
                Tables\Actions\BulkActionGroup::make([
                    // 1️⃣ SUPPRESSION MULTIPLE
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection'),
BulkAction::make('change_active_status')
        ->label('Activer / Désactiver')
        ->icon('heroicon-o-check-circle')
        ->form([
            Forms\Components\Select::make('status')
                ->label('Nouveau statut')
                ->options([
                    1 => 'Actif',
                    0 => 'Inactif'
                ])
                ->required(),
        ])
        ->action(function (array $data, Collection $records) {
            foreach ($records as $record) {
                $record->update([
                    'status' => $data['status'],
                ]);
            }

            Notification::make()
                ->title('Mise à jour réussie')
                ->body($records->count().' éléments activés/désactivés.')
                ->success()
                ->send();
        })
        ->deselectRecordsAfterCompletion(),
                    // 2️⃣ CHANGER LE STATUT DE PLUSIEURS D’UN COUP
                    BulkAction::make('change_status')
                        ->label('Changer le statut')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('news_status')
                                ->label('Nouveau statut')
                                ->options($statusOptions)
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            /** @var \Illuminate\Support\Collection $records */
                            $records->each->update([
                                'news_status' => $data['news_status'],
                            ]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    ]),
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
