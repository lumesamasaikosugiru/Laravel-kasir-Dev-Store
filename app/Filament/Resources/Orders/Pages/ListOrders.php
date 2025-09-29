<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Widgets\OrderStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderStats::class
        ];
    }
    public function getTabs(): array
    {
        return [
            null => Tab::make('All'),
            'New' => Tab::make()->query(fn($query) => $query->where('status', 'New')),
            'Processing' => Tab::make()->query(fn($query) => $query->where('status', 'Processing')),
            'Cancelled' => Tab::make()->query(fn($query) => $query->where('status', 'Cancelled')),
            'Completed' => Tab::make()->query(fn($query) => $query->where('status', 'Completed'))
        ];
    }
}
