<?php

namespace App\Filament\Restaurant\Resources\ProductResource\Pages;

use App\Filament\Restaurant\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        $tenant = tenancy()->tenant;
        $canCreate = $tenant->canCreateProduct();
        $stats = $tenant->usageStats();

        return [
            Actions\CreateAction::make()
                ->disabled(!$canCreate)
                ->before(function () use ($tenant, $canCreate) {
                    if (!$canCreate) {
                        Notification::make()
                            ->danger()
                            ->title('Limite de produtos atingido')
                            ->body('Você atingiu o limite de produtos do seu plano. Faça upgrade para adicionar mais produtos.')
                            ->persistent()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('upgrade')
                                    ->label('Fazer Upgrade')
                                    ->url(route('filament.restaurant.pages.manage-subscription'))
                                    ->button(),
                            ])
                            ->send();

                        $this->halt();
                    }
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductResource\Widgets\ProductLimitWidget::class,
        ];
    }
}
