<?php
// app/Filament/Resources/ShopProductResource.php
namespace App\Filament\Resources;

use App\Enums\ProductType;
use App\Filament\Resources\ShopProductResource\Pages;
use App\Models\Shop\ShopProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ShopProductResource extends Resource
{
    protected static ?string $model = ShopProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
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
                            Str::ulid().'.'.$file->getClientOriginalExtension()
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
                            Str::ulid().'.'.$file->getClientOriginalExtension()
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
                            Str::ulid().'.'.$file->getClientOriginalExtension()
                        ),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
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

                Tables\Columns\BadgeColumn::make('product_type')
                    ->label('Type')
                    ->formatStateUsing(fn($state) => $state instanceof ProductType ? $state->label() : $state),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('USD', true),

                Tables\Columns\TextColumn::make('stock_qty')
                    ->label('Stock'),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
}
