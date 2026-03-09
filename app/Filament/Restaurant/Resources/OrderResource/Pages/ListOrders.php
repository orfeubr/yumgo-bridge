<?php

namespace App\Filament\Restaurant\Resources\OrderResource\Pages;

use App\Filament\Restaurant\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderLimitWidget::class,
        ];
    }
}
