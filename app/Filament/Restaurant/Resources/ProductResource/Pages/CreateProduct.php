<?php

namespace App\Filament\Restaurant\Resources\ProductResource\Pages;

use App\Filament\Restaurant\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // Redirecionar para a lista após criar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
