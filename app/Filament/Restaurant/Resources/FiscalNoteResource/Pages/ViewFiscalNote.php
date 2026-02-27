<?php

namespace App\Filament\Restaurant\Resources\FiscalNoteResource\Pages;

use App\Filament\Restaurant\Resources\FiscalNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFiscalNote extends ViewRecord
{
    protected static string $resource = FiscalNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
