<?php

namespace App\Filament\Restaurant\Resources\FiscalNoteResource\Pages;

use App\Filament\Restaurant\Resources\FiscalNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFiscalNote extends EditRecord
{
    protected static string $resource = FiscalNoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
