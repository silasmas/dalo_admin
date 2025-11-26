<?php

// app/Filament/Resources/ShopOrderResource.php
namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\ShopOrderState;
use App\Models\Shop\ShopOrder;
use Filament\Resources\Resource;
use Filament\Tables\Columns\SelectColumn;
use App\Filament\Resources\ShopOrderResource\Pages;
use App\Filament\Resources\ShopOrderResource\Widgets\ShopOrderStats;
use App\Filament\Resources\ShopOrderResource\RelationManagers\OrderItemsRelationManager;

class ShopOrderResource extends Resource
{
    protected static ?string $model = ShopOrder::class;
    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';
    protected static ?string $navigationLabel = 'Commandes';
    protected static ?string $navigationGroup = 'Boutique & Ressources';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Infos commande')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('code')->label('Code')->disabled(),
                    Forms\Components\Select::make('state')
                        ->label('État')
                        ->options(collect(ShopOrderState::cases())->mapWithKeys(
                            fn($s) => [$s->value => $s->label()]
                        )),
                    Forms\Components\TextInput::make('currency')->label('Devise')->disabled(),
                    Forms\Components\TextInput::make('total')->label('Total')->disabled(),
                ]),

            Forms\Components\Section::make('Livraison')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('shipping_name')->label('Nom')->disabled(),
                    Forms\Components\TextInput::make('shipping_phone')->label('Téléphone')->disabled(),
                    Forms\Components\TextInput::make('shipping_addr')->label('Adresse')->columnSpanFull()->disabled(),
                    Forms\Components\Textarea::make('shipping_notes')->label('Notes')->columnSpanFull()->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.firstname')
                    ->label('Partenaire')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD', true),

                Tables\Columns\BadgeColumn::make('state')
                    ->label('État')
                    ->formatStateUsing(fn($state) =>
                        $state instanceof ShopOrderState ? $state->label() : $state
                    )
                    ->colors(fn($state) => $state instanceof ShopOrderState
                        ? [$state->color()]
                        : []
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShopOrders::route('/'),
            // 'view'  => Pages\ViewShopOrder::route('/{record}'),
            'edit'  => Pages\EditShopOrder::route('/{record}/edit'),
        ];
    }
      public static function getNavigationBadge(): ?string
    {
        // ex. seulement les commandes en attente de paiement
        $count = ShopOrder::query()
            ->where('state', ShopOrderState::Pending)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning'; // jaune => "à traiter"
    }
      public static function getHeaderWidgets(): array
    {
        return [
            ShopOrderStats::class,
        ];
    }
}
