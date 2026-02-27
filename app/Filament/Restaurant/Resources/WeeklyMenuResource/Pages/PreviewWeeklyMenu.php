<?php

namespace App\Filament\Restaurant\Resources\WeeklyMenuResource\Pages;

use App\Filament\Restaurant\Resources\WeeklyMenuResource;
use App\Models\WeeklyMenu;
use Filament\Resources\Pages\Page;

class PreviewWeeklyMenu extends Page
{
    protected static string $resource = WeeklyMenuResource::class;

    protected static string $view = 'filament.restaurant.resources.weekly-menu-resource.pages.preview-weekly-menu';

    public WeeklyMenu $record;

    public function mount(int | string $record): void
    {
        $this->record = WeeklyMenu::with(['items.product'])->findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Visualizar: ' . $this->record->name;
    }

    public function getItemsByDay(): array
    {
        $itemsByDay = [];
        $days = WeeklyMenu::getDaysOfWeek();

        foreach ($days as $dayKey => $dayLabel) {
            $items = $this->record->items()
                ->where('day_of_week', $dayKey)
                ->with('product')
                ->orderBy('order')
                ->get();

            if ($items->isNotEmpty()) {
                $itemsByDay[$dayKey] = [
                    'label' => $dayLabel,
                    'items' => $items,
                ];
            }
        }

        return $itemsByDay;
    }
}
