<?php

namespace App\Filament\Restaurant\Resources\DeliveryDriverResource\Pages;

use App\Filament\Restaurant\Resources\DeliveryDriverResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryDriver extends EditRecord
{
    protected static string $resource = DeliveryDriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
