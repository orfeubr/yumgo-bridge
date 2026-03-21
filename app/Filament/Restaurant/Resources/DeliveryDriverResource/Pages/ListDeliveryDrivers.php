<?php

namespace App\Filament\Restaurant\Resources\DeliveryDriverResource\Pages;

use App\Filament\Restaurant\Resources\DeliveryDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryDrivers extends ListRecords
{
    protected static string $resource = DeliveryDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
