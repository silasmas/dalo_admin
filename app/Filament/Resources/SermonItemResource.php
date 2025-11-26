<?php
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Enums\SermonType;
use App\Models\SermonItem;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\SermonItemResource\Pages;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SermonItemResource extends Resource
{
    protected static ?string $model           = SermonItem::class;
    protected static ?string $navigationIcon  = 'heroicon-o-microphone';
    protected static ?string $navigationLabel = 'Prédications';
    protected static ?string $modelLabel      = 'Prédication/Enseignement';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\Section::make('Informations')
                    ->columnSpan(8)
                    ->schema([
                        Forms\Components\Select::make('type')->label('Type')->native(false)->required()
                            ->options(collect(SermonType::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all())
                            ->helperText('Choisissez le type de contenu.'),
                        Forms\Components\TextInput::make('title')->label('Titre')->required()
                            ->helperText('Titre de la prédication/enseignement.'),
                        Forms\Components\Textarea::make('description')->label('Description')->rows(3),
                        Forms\Components\TextInput::make('preacher_name')->label('Orateur (nom)')->maxLength(191),
                        Forms\Components\DatePicker::make('preached_on')->label('Prêché le')->native(false),

                        Forms\Components\Toggle::make('featured')->label('Mis en avant')->default(false),
                    ]),
                Forms\Components\Section::make('Médias')
                    ->columnSpan(4)
                    ->schema([

                             FileUpload::make('cover_url')
                            ->label('Image de couverture')
                            ->disk('s3')
                            ->directory('sermons/covers')
                            ->visibility('private')
                            ->image()
                            ->imageEditor()
                            ->maxSize(4096) // 4 Mo
                            ->preserveFilenames(false)
                            ->formatStateUsing(fn ($state) => $state ? ltrim($state, '/') : null)
                            ->getUploadedFileNameForStorageUsing(
                                fn(TemporaryUploadedFile $file) =>
                                Str::ulid() . '.' . $file->getClientOriginalExtension()
                            ),
                        Forms\Components\Toggle::make('has_video')->label('Vidéo')->default(false),
                        Forms\Components\TextInput::make('url_video')->label('URL vidéo')->maxLength(500),
                        Forms\Components\Toggle::make('has_audio')->label('Audio')->default(false),
                        Forms\Components\TextInput::make('url_audio')->label('URL audio')->maxLength(500),
                        Forms\Components\Toggle::make('has_text')->label('Texte')->default(false),
                    ]),
                Forms\Components\Section::make('Contenu texte')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\RichEditor::make('content_text')->label('Texte intégral')
                            ->helperText('Collez le texte de l’enseignement si disponible.')
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Diffusion en direct (si applicable)')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Toggle::make('is_live')->label('Live en cours')->default(false),
                        Forms\Components\DateTimePicker::make('start_at')->label('Début')->native(false),
                        Forms\Components\DateTimePicker::make('end_at')->label('Fin')->native(false),
                    ]),
            ]),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table->defaultSort('id', 'desc')->columns([
            Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
            Tables\Columns\ImageColumn::make('cover_url')->label('Cover')
              ->disk('s3')              // <- Filament va automatiquement générer l’URL complète
    ->visibility('private')    // optionnel si les fichiers sont publics
                ->defaultImageUrl(asset('assets/images/avatar-default.png')),
            Tables\Columns\TextColumn::make('title')->label('Titre')->searchable()->limit(40),
            Tables\Columns\BadgeColumn::make('type')
    ->label('Type')
    ->formatStateUsing(function ($state) {
        // Si Filament nous donne déjà l'enum
        if ($state instanceof SermonType) {
            return $state->label();   // ou ->name / ->value selon ta méthode
        }

        // Si c'est encore une string en BDD
        return SermonType::tryFrom($state)?->label() ?? (string) $state;
    })
    ->colors([
        'primary' => function ($state) {
            $enum = $state instanceof SermonType ? $state : SermonType::tryFrom($state);

            return $enum === SermonType::Predication;
        },
        'info' => function ($state) {
            $enum = $state instanceof SermonType ? $state : SermonType::tryFrom($state);

            return $enum === SermonType::Enseignement;
        },
        'warning' => function ($state) {
            $enum = $state instanceof SermonType ? $state : SermonType::tryFrom($state);

            return $enum === SermonType::Emission;
        },
    ]),
            Tables\Columns\IconColumn::make('is_live')->label('Live')->boolean(),
            Tables\Columns\TextColumn::make('preached_on')->label('Date')->date('d/m/Y')->sortable(),
            Tables\Columns\IconColumn::make('featured')->label('Une')->boolean(),
            Tables\Columns\TextColumn::make('views_count')->label('Vues')->numeric()->sortable(),
        ])->filters([
            Tables\Filters\SelectFilter::make('type')
                ->options(collect(SermonType::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->all()),
            Tables\Filters\TernaryFilter::make('is_live')->label('En direct'),
            Tables\Filters\TernaryFilter::make('featured')->label('Mis en avant'),
        ])->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
            Tables\Actions\ForceDeleteAction::make(),
        ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {$data['created_by'] = auth('admin')->id();return $data;}
    public static function mutateFormDataBeforeSave(array $data): array
    {$data['updated_by'] = auth('admin')->id();return $data;}

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSermonItems::route('/'),
            'create' => Pages\CreateSermonItem::route('/create'),
            'view'   => Pages\ViewSermonItem::route('/{record}'),
            'edit'   => Pages\EditSermonItem::route('/{record}/edit'),
        ];
    }
//     public function infolist(Infolist $infolist): Infolist
// {
//     return $infolist
//         ->schema([
//             Section::make('Aperçu du média')
//                 ->schema([
//                     ImageEntry::make('cover_url')
//                         ->label('Image de couverture')
//                         ->disk('s3')
//                         ->height(200)
//                         ->visible(fn ($record) => !empty($record->cover_url)),
//                 ]),
//         ]);
// }
}
