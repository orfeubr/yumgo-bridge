<?php

namespace App\Filament\Restaurant\Resources\WeeklyMenuResource\Pages;

use App\Filament\Restaurant\Resources\WeeklyMenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWeeklyMenu extends CreateRecord
{
    protected static string $resource = WeeklyMenuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Cardápio semanal criado com sucesso!';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove os campos de produtos dos dias (serão salvos depois)
        $this->productsByDay = [
            'monday' => $data['monday_products'] ?? [],
            'tuesday' => $data['tuesday_products'] ?? [],
            'wednesday' => $data['wednesday_products'] ?? [],
            'thursday' => $data['thursday_products'] ?? [],
            'friday' => $data['friday_products'] ?? [],
            'saturday' => $data['saturday_products'] ?? [],
            'sunday' => $data['sunday_products'] ?? [],
        ];

        unset(
            $data['monday_products'],
            $data['tuesday_products'],
            $data['wednesday_products'],
            $data['thursday_products'],
            $data['friday_products'],
            $data['saturday_products'],
            $data['sunday_products']
        );

        return $data;
    }

    protected function afterCreate(): void
    {
        $menu = $this->record;
        $order = 0;

        $dayMapping = [
            'monday' => 'monday',
            'tuesday' => 'tuesday',
            'wednesday' => 'wednesday',
            'thursday' => 'thursday',
            'friday' => 'friday',
            'saturday' => 'saturday',
            'sunday' => 'sunday',
        ];

        foreach ($this->productsByDay as $day => $productIds) {
            foreach ($productIds as $productId) {
                $menu->items()->create([
                    'day_of_week' => $dayMapping[$day],
                    'product_id' => $productId,
                    'order' => $order++,
                    'is_available' => true,
                ]);
            }
        }
    }

    private array $productsByDay = [];
}
