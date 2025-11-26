<?php
// app/Filament/Resources/ShopOrderResource/RelationManagers/OrderItemsRelationManager.php
namespace App\Filament\Resources\ShopOrderResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Articles de la commande';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_title')
                    ->label('Produit')
                    ->searchable(),

                Tables\Columns\TextColumn::make('qty')
                    ->label('Qté'),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('PU')
                    ->money('USD', true),

                Tables\Columns\TextColumn::make('currency')
                    ->label('Devise'),

                Tables\Columns\IconColumn::make('is_digital')
                    ->label('Numérique ?')
                    ->boolean(),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
