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
use Filament\Forms\Components\Actions\Action;


class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(
                Group::make()
                    ->schema([
                        // Bagian Kiri: Customer Info + Order Details
                        Group::make()
                            ->schema([
                                // Date di atas kiri
                                DateTimePicker::make('date_sell')
                                    ->required()
                                    ->default(now())
                                    ->disabled()
                                    ->hiddenLabel()
                                    ->dehydrated()
                                    ->prefix('Date:')
                                    ->columnSpanFull(),

                                // Customer Info
                                Section::make('Customer Information')
                                    ->description('Data terkait pelanggan dan penjualan')
                                    ->schema([
                                        Select::make('customer_id')
                                            ->relationship('customer', 'name')
                                            ->label('Name')
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
                                    ->columns(3)
                                    ->columnSpanFull(),

                                // Order Details
                                Section::make('Order Details')
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

                                                        $discount = $get('../../discount');
                                                        $discount_amount = $total * $discount / 100;
                                                        $set('../../discount_amount', $discount_amount);
                                                        $set('../../total_payment', $total - $discount);
                                                    }),

                                                TextInput::make('price')
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->prefix('IDR')
                                                    ->formatStateUsing(
                                                        fn($state, Get $get) =>
                                                        $state ?? Product::find($get('product_id'))?->price ?? 0
                                                    ),

                                                TextInput::make('qty')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->default(1)
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                        $price = $get('price') ?? 0;
                                                        $set('subtotal', $price * $state);

                                                        $items = $get('../../OrderDetail') ?? [];
                                                        $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                                        $set('../../total_price', $total);


                                                        $discount = $get('../../discount');
                                                        $discount_amount = $total * $discount / 100;
                                                        $set('../../discount_amount', $discount_amount);
                                                        $set('../../total_payment', $total - $discount);

                                                    }),

                                                TextInput::make('subtotal')
                                                    ->readOnly()
                                                    ->numeric()
                                                    ->default(0)
                                                    ->prefix('IDR'),
                                            ])
                                            ->columns(2)
                                            ->hiddenLabel()
                                            ->addActionLabel(' ➕ Add Product')

                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(4), // kiri

                        // Bagian Kanan: Payment Info
                        Section::make('Payment Information')
                            ->description('Metode & status pembayaran')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'New' => 'New',
                                        'Processing' => 'Processing',
                                        'Cancelled' => 'Cancelled',
                                        'Completed' => 'Completed',
                                    ])
                                    ->columnSpanFull()
                                    ->default('New'),

                                TextInput::make('total_price')
                                    ->required()
                                    ->readOnly()
                                    ->numeric()
                                    ->columnSpanFull()
                                    ->prefix('IDR')
                                    ->default(0),

                                TextInput::make('discount')
                                    ->columnSpan(3)
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(80)
                                    ->suffix('%')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $discount = floatval($state) ?? 0;
                                        $total_price = $get('total_price') ?? 0;
                                        $discount_amount = $total_price * $discount / 100;
                                        $set('discount_amount', $discount_amount);
                                        $set('total_payment', $total_price - $discount_amount);
                                    }),

                                TextInput::make('discount_amount')
                                    ->default(0)
                                    ->readOnly()
                                    ->columnSpan(3)
                                    ->prefix('IDR'),

                                TextInput::make('total_payment')
                                    ->readOnly()
                                    ->prefix('IDR')
                                    ->columnSpanFull()
                                    ->default(0),

                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->columnSpan(3)
                                    ->options([
                                        'Cash' => 'Cash',
                                        'Credit' => 'Credit',
                                        'Debit' => 'Debit',
                                        'Qris' => 'Qris',
                                    ])->default('Cash'),

                                Select::make('payment_status')
                                    ->label('Payment Status')
                                    ->columnSpan(3)
                                    ->options([
                                        'Paid' => 'Paid',
                                        'Unpaid' => 'Unpaid',
                                        'Failed' => 'Failed',
                                    ])->default('Unpaid')
                            ])
                            ->columns(6)
                            ->columnSpan(2), // kanan
                    ])
                    ->columns(6) // grid utama
                    ->columnSpanFull()
            );
    }

}
