<?php

namespace App\Filament\Resources\Orders\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Waiting', Order::where('status', 'New')->count())
                ->description('Order Baru')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),

            Stat::make('Completed', Order::where('status', 'Completed')->count())
                ->description('Order Diselesaikan')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('On Process', Order::where('status', 'Processing')->count())
                ->description('Order yang diproses')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),

            Stat::make('Income Completed Orders', 'IDR. ' . number_format(Order::where('status', 'Completed')->sum('total_payment'), 0))
                ->description('Total Pendapatan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
        ];
    }
}
