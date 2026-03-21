<?php

namespace App\Filament\Restaurant\Resources\WaiterResource\Pages;

use App\Filament\Restaurant\Resources\WaiterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWaiter extends EditRecord
{
    protected static string $resource = WaiterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
