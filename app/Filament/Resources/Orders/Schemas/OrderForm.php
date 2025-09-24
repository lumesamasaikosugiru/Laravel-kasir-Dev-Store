<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('date_sell')
                    ->required()
                    ->default(now())
                    ->disabled()
                    ->hiddenLabel()
                    ->dehydrated()
                    ->prefix('Date:'),

                Section::make('Customer Info')
                    ->description('Data terkait pelanggan dan penjualan')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $customer = Customer::find($state);
                                $set('phone', $customer->phone ?? null);
                                $set('address', $customer->address ?? null);
                            }),

                        TextInput::make('phone')
                            ->disabled(),
                        TextInput::make('address')
                            ->disabled(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Detail Penjualan')
                    ->description('Data Poduk terjuan')
                    ->schema([
                        Repeater::make('OrderDetail')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $product = Product::find($state);
                                        $price = $product->price ?? 0;
                                        $set('price', $price);
                                        $qty = $get('qty') ?? 1;
                                        $set('qty', $qty);
                                        $subtotal = $price * $qty;
                                        $set('subtotal', $subtotal);

                                        $items = $get('../../OrderDetail') ?? [];
                                        $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                        $set('../../total_price', $total);
                                    }),

                                TextInput::make('price')
                                    ->disabled(),
                                TextInput::make('qty')
                                    ->numeric()
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $price = $get('price') ?? 0;
                                        $set('subtotal', $price * $state);

                                        $items = $get('../../OrderDetail') ?? [];
                                        $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                        $set('../../total_price', $total);
                                    }),
                                TextInput::make('subtotal')
                                    ->disabled()
                                    ->dehydrated(),
                            ])->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                TextInput::make('total_price')
                    ->required()
                    ->numeric()
            ]);
    }
}
