<?php

namespace App\Filament\Restaurant\Resources\WeeklyMenuResource\Pages;

use App\Filament\Restaurant\Resources\WeeklyMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWeeklyMenu extends EditRecord
{
    protected static string $resource = WeeklyMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Cardápio atualizado com sucesso!';
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Carregar produtos existentes por dia
        $items = $this->record->items;

        $data['monday_products'] = $items->where('day_of_week', 'monday')->pluck('product_id')->toArray();
        $data['tuesday_products'] = $items->where('day_of_week', 'tuesday')->pluck('product_id')->toArray();
        $data['wednesday_products'] = $items->where('day_of_week', 'wednesday')->pluck('product_id')->toArray();
        $data['thursday_products'] = $items->where('day_of_week', 'thursday')->pluck('product_id')->toArray();
        $data['friday_products'] = $items->where('day_of_week', 'friday')->pluck('product_id')->toArray();
        $data['saturday_products'] = $items->where('day_of_week', 'saturday')->pluck('product_id')->toArray();
        $data['sunday_products'] = $items->where('day_of_week', 'sunday')->pluck('product_id')->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Armazenar produtos por dia antes de salvar
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

    protected function afterSave(): void
    {
        // Deletar todos os items existentes
        $this->record->items()->delete();

        // Recriar com os novos produtos
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
                $this->record->items()->create([
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
