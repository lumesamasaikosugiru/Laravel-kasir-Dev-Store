<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Customer;
use App\Models\OrderDetail;
use App\Models\Product;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                Group::make()
                    ->schema([
                        DateTimePicker::make('date_sell')
                            ->required()
                            ->default(now())
                            ->disabled()
                            ->hiddenLabel()
                            ->dehydrated()
                            ->prefix('Date:')
                            ->columnSpanFull(),

                        // Bagian atas kiri: Customer Info
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

                                Placeholder::make('phone')
                                    ->content(fn(Get $get) => Customer::find($get('customer_id'))?->phone ?? '-'),

                                Placeholder::make('address')
                                    ->content(fn(Get $get) => Customer::find($get('customer_id'))?->address ?? '-'),
                            ])
                            ->columns(2)
                            ->columnSpan(2), // ambil 2 kolom dari grid utama

                        // Bagian atas kanan: Informasi Pembayaran
                        Section::make('Informasi Pembayaran')
                            ->description('Metode & status pembayaran')
                            ->schema([
                                // Total harga di samping
                                TextInput::make('total_price')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->numeric()
                                    ->columnSpanFull()
                            ]),
                        // Bagian bawah full: Detail Penjualan
                        Section::make('Detail Penjualan')
                            ->description('Data produk terjual')
                            ->schema([
                                Repeater::make('OrderDetail')
                                    ->relationship()
                                    ->schema([
                                        Select::make('product_id')
                                            ->relationship('product', 'name')
                                            ->reactive()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
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
                                            ->disabled()
                                            ->numeric()
                                            ->dehydrated()
                                            ->formatStateUsing(
                                                fn($state, Get $get) =>
                                                $state ?? Product::find($get('product_id'))?->price ?? 0
                                            ),

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
                                            ->numeric()
                                            ->dehydrated(),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                    ])
                    ->columns(4)
                    ->columnSpanFull() // grid utama 3 kolom
            );
    }

}
