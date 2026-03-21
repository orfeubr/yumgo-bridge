<?php

namespace App\Filament\Restaurant\Resources\CashRegisterResource\Pages;

use App\Filament\Restaurant\Resources\CashRegisterResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRegister extends ViewRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Sem ações de edição - caixas são read-only após criação
        ];
    }
}
