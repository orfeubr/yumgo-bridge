<?php

namespace App\Filament\Restaurant\Resources\CashRegisterResource\Pages;

use App\Filament\Restaurant\Resources\CashRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashRegisters extends ListRecords
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
