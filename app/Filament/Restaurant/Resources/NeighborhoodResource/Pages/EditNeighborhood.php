<?php

namespace App\Filament\Restaurant\Resources\NeighborhoodResource\Pages;

use App\Filament\Restaurant\Resources\NeighborhoodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNeighborhood extends EditRecord
{
    protected static string $resource = NeighborhoodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
