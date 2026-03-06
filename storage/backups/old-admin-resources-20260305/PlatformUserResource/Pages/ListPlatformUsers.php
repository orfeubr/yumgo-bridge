<?php

namespace App\Filament\Admin\Resources\PlatformUserResource\Pages;

use App\Filament\Admin\Resources\PlatformUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlatformUsers extends ListRecords
{
    protected static string $resource = PlatformUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
