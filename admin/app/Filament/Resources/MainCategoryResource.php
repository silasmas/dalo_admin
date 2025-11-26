<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\MainCategory;
use Filament\Resources\Resource;
use App\Filament\Resources\MainCategoryResource\Pages;

class MainCategoryResource extends Resource
{
    protected static ?string $model = MainCategory::class;

    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Catégories';
    protected static ?string $modelLabel = 'Catégorie';

    public static function form(Form $form): Form
    {
       return $form
    ->schema([
        Forms\Components\Section::make('Informations principales')
            ->columns(2)
            ->schema([
                Forms\Components\Toggle::make('status')
                    ->label('Active ?')
                    ->default(true)
                    ->helperText('Active/désactive cette catégorie. Si désactivée, elle ne sera pas affichée dans l’application.'),

                Forms\Components\TextInput::make('type')
                    ->label('Type')
                    ->maxLength(64)
                    ->required()
                    ->placeholder('Ex : EDM_CATEGORY, APPT_SERVICE')
                    ->helperText('Type logique auquel cette catégorie appartient. Exemple : "EDM_CATEGORY" pour les vidéos Edifie-moi.'),

                Forms\Components\TextInput::make('cat_name')
                    ->label('Nom de la catégorie')
                    ->maxLength(191)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (! empty($state)) {
                            $set('cat_key', Str::slug($state));
                        }
                    })
                    ->helperText('Nom lisible de la catégorie. Le slug sera généré automatiquement.'),

                Forms\Components\TextInput::make('cat_key')
                    ->label('Clé (slug interne)')
                    ->maxLength(128)
                    ->required()
                    ->helperText('Identifiant interne, utilisé dans le système. Généré automatiquement depuis le nom mais modifiable si nécessaire.'),
            ]),

        Forms\Components\Section::make('Organisation')
            ->columns(1)
            ->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('Catégorie parent')
                    ->relationship('parent', 'cat_name')
                    ->searchable()
                    ->nullable()
                    ->helperText('Permet d’organiser les catégories en hiérarchie. Laisse vide pour une catégorie principale.'),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->nullable()
                    ->helperText('Texte d’information sur la catégorie (facultatif).'),
            ]),

        Forms\Components\Section::make('Métadonnées avancées')
            ->schema([
                Forms\Components\KeyValue::make('metadata')
                    ->label('Métadonnées (JSON)')
                    ->nullable()
                    ->helperText('Informations supplémentaires stockées sous forme de JSON (options, paramètres, etc.).'),
            ]),
    ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cat_key')
                    ->label('Clé')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('cat_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('parent.cat_name')
                    ->label('Parent')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('status')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(
                        MainCategory::query()
                            ->select('type')
                            ->distinct()
                            ->pluck('type', 'type')
                            ->toArray()
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('type');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMainCategories::route('/'),
            'create' => Pages\CreateMainCategory::route('/create'),
            'edit'   => Pages\EditMainCategory::route('/{record}/edit'),
        ];
    }
}
