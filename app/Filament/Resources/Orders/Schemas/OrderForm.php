<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Info')
                    ->description('Data terkait pelanggan dan penjualan')
                    ->schema([
                        Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->required(),
                    ])
                    ->columnSpanFull(),
                DateTimePicker::make('date_sell')
                    ->required()
                    ->default(now()),

                TextInput::make('total_price')
                    ->required()
                    ->numeric(),
            ]);
    }
}
