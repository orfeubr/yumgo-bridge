<?php

namespace App\Filament\Restaurant\Resources\CategoryResource\Pages;

use App\Filament\Restaurant\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // Redirecionar para a lista após salvar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
