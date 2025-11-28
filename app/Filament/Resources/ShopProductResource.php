<?php
// app/Filament/Resources/ShopProductResource.php
namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Enums\ShopOrderState;
use App\Filament\Resources\ShopProductResource\Pages;
use App\Models\Shop\ShopProduct;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ShopProductResource extends Resource
{
    protected static ?string $model           = ShopProduct::class;
    protected static ?string $navigationIcon  = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Ressources';
    protected static ?string $navigationGroup = 'Boutique & Ressources';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations principales')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('status')
                        ->label('Actif ?')
                        ->default(true),

                    Forms\Components\TextInput::make('title')
                        ->label('Titre')
                        ->required()
                        ->maxLength(255)
                        ->reactive()
                        ->afterStateUpdated(fn($state, callable $set) =>
                            $set('slug', Str::slug($state))
                        ),

                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->maxLength(191)
                        ->helperText('Utilisé dans les URLs publiques.'),

                    Forms\Components\Select::make('product_type')
                        ->label('Type de ressource')
                        ->options(collect(ProductType::cases())->mapWithKeys(
                            fn($t) => [$t->value => $t->label()]
                        ))
                        ->required(),

                    Forms\Components\Toggle::make('is_digital')
                        ->label('Ressource numérique ?')
                        ->helperText('Ex : e-book, PDF à télécharger.'),
                ]),

            Forms\Components\Section::make('Prix & stock')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('Prix (en centimes)')
                        ->numeric()
                        ->helperText('Ex : 5000 = 50.00 USD'),

                    Forms\Components\TextInput::make('currency')
                        ->label('Devise')
                        ->default('USD')
                        ->maxLength(3),

                    Forms\Components\TextInput::make('stock_qty')
                        ->label('Stock')
                        ->numeric()
                        ->helperText('Nombre d’unités disponibles.'),
                ]),

            Forms\Components\Section::make('Média')
                ->columns(2)
                ->schema([
                    FileUpload::make('cover_url')
                        ->label('Image principale')
                        ->disk('s3')
                        ->directory('shop/covers')
                        ->visibility('private')
                        ->image()
                        ->imageEditor()
                        ->maxSize(4096)
                        ->preserveFilenames(false)
                        ->getUploadedFileNameForStorageUsing(fn(TemporaryUploadedFile $file) =>
                            Str::ulid() . '.' . $file->getClientOriginalExtension()
                        ),

                    FileUpload::make('images')
                        ->label('Galerie')
                        ->disk('s3')
                        ->directory('shop/images')
                        ->visibility('private')
                        ->image()
                        ->imageEditor()
                        ->multiple()
                        ->reorderable()
                        ->preserveFilenames(false)
                        ->getUploadedFileNameForStorageUsing(fn(TemporaryUploadedFile $file) =>
                            Str::ulid() . '.' . $file->getClientOriginalExtension()
                        )
                        ->helperText('Plusieurs images possibles.'),
                ]),

            Forms\Components\Section::make('Contenu')
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->rows(4),

                    FileUpload::make('file_url')
                        ->label('Fichier numérique (si e-book, PDF, etc.)')
                        ->disk('s3')
                        ->directory('shop/files')
                        ->visibility('private')
                        ->helperText('Fichier téléchargeable pour les ressources numériques.')
                        ->preserveFilenames(false)
                        ->getUploadedFileNameForStorageUsing(fn(TemporaryUploadedFile $file) =>
                            Str::ulid() . '.' . $file->getClientOriginalExtension()
                        ),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // options pour l’état métier
        $stateOptions = class_exists(ShopOrderState::class)
            ? collect(ShopOrderState::cases())
            ->mapWithKeys(fn(ShopOrderState $s) => [$s->value => $s->label()])
            ->all()
            : [
            'pending'   => 'En attente',
            'paid'      => 'Payée',
            'failed'    => 'Échouée',
            'canceled'  => 'Annulée',
            'shipped'   => 'Expédiée',
            'completed' => 'Terminée',
        ];
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_url')
                    ->label('')
                    ->disk('s3')
                    ->circular()
                    ->size(40),

                Tables\Columns\TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn($state) =>
                        $state == 1 ? 'En ligne' : 'Déclassé'
                    )
                    ->colors([
                        'success' => fn($state) => $state == 1,
                        'danger'  => fn($state)  => $state == 0,
                    ])
                    ->icon(fn($state) =>
                        $state == 1
                            ? 'heroicon-o-check-circle'
                            : 'heroicon-o-x-circle'
                    ),
                Tables\Columns\BadgeColumn::make('product_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => $state instanceof ProductType ? $state->label() : $state),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('USD', true),

                Tables\Columns\TextColumn::make('stock_qty')
                    ->label('Stock'),
            ]) // 🔍 Filtres
            ->filters([
                // Filtre par type de produit
                SelectFilter::make('product_type')
                    ->label('Type de produit')
                    ->options([
                        'book'        => 'Livre',
                        'accessories' => 'Accessoire',
                        'clothes'     => 'Vêtement',
                        'other'       => 'Autre',
                    ]),

                // Filtre par statut (en ligne / déclassé)
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        1 => 'En ligne',
                        0 => 'Déclassé',
                    ]),

                // Filtre par fourchette de prix
                Filter::make('price_range')
                    ->label('Prix')
                    ->form([
                        Forms\Components\TextInput::make('min_price')
                            ->label('Prix min.')
                            ->numeric()
                            ->placeholder('Ex. 10'),

                        Forms\Components\TextInput::make('max_price')
                            ->label('Prix max.')
                            ->numeric()
                            ->placeholder('Ex. 100'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['min_price'] ?? null),
                                fn(Builder $q) => $q->where('price', '>=', $data['min_price'])
                            )
                            ->when(
                                filled($data['max_price'] ?? null),
                                fn(Builder $q) => $q->where('price', '<=', $data['max_price'])
                            );
                    }),
            ])

            // 📌 Tri par défaut : d’abord les produits en ligne (status=1), puis le reste
            ->defaultSort('status', 'desc')
            // optionnel : ensuite par date ou titre
            ->defaultSort('created_at', 'desc')

            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])->bulkActions([
            Tables\Actions\DeleteBulkAction::make()
                ->label('Supprimer la sélection'),

            self::bulkChangeOrderState($stateOptions), // change state
            self::bulkChangeOrderStatus(),             // change status 0/1
            self::bulkChangeProductStatus(),           // ⬅️ on ajoute l’action ci-dessous
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListShopProducts::route('/'),
            'create' => Pages\CreateShopProduct::route('/create'),
            'edit'   => Pages\EditShopProduct::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        // Nombre total de produits actifs
        $count = ShopProduct::query()->where('status', 1)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // couleur du badge : primary, success, danger, warning, info, gray…
        return 'primary';
    }
    protected static function bulkChangeProductStatus(): BulkAction
    {
        return BulkAction::make('change_product_status')
            ->label('Activer / Désactiver les produits')
            ->icon('heroicon-o-adjustments-horizontal')
            ->form([
                Forms\Components\Select::make('status')
                    ->label('Nouveau statut')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
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
                    ->title('Produits mis à jour')
                    ->body($records->count() . ' produit(s) ont été activés/désactivés.')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
    protected static function bulkChangeOrderState(array $stateOptions): BulkAction
    {
        return BulkAction::make('change_order_state')
            ->label('Changer l’état des commandes')
            ->icon('heroicon-o-arrow-path')
            ->form([
                Forms\Components\Select::make('state')
                    ->label('Nouvel état')
                    ->options($stateOptions)
                    ->required(),
            ])
            ->action(function (array $data, Collection $records) {
                foreach ($records as $record) {
                    $record->update([
                        'state' => $data['state'],
                    ]);
                }

                Notification::make()
                    ->title('Commandes mises à jour')
                    ->body($records->count() . ' commande(s) ont changé d’état.')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
    protected static function bulkChangeOrderStatus(): BulkAction
    {
        return BulkAction::make('change_order_status')
            ->label('Activer / Désactiver les commandes')
            ->icon('heroicon-o-adjustments-horizontal')
            ->form([
                Forms\Components\Select::make('status')
                    ->label('Nouveau statut')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
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
                    ->title('Statuts des commandes mis à jour')
                    ->body($records->count() . ' commande(s) activée(s)/désactivée(s).')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
