<?php

namespace App\Filament\Restaurant\Resources\WeeklyMenuResource\Pages;

use App\Filament\Restaurant\Resources\WeeklyMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeeklyMenus extends ListRecords
{
    protected static string $resource = WeeklyMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Cardápio'),
        ];
    }
}
