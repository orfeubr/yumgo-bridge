<?php

namespace App\Filament\Restaurant\Resources\WaiterResource\Pages;

use App\Filament\Restaurant\Resources\WaiterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWaiters extends ListRecords
{
    protected static string $resource = WaiterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
