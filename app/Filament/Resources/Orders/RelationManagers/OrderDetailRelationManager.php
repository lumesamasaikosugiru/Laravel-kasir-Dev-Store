<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'OrderDetail';

    // protected static ?string $relatedResource = ProductResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product'),
                TextColumn::make('product.price')
                    ->label('Harga')
                    ->money('idr', true),
                TextColumn::make('qty'),
                TextColumn::make('subtotal')
                    ->money('idr', true),
            ]);
        // ->headerActions([
        //     // CreateAction::make()

        // ]);
    }
}
