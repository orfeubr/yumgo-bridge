<?php

namespace App\Filament\Restaurant\Resources\ProductResource\Pages;

use App\Filament\Restaurant\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // Redirecionar para a lista após criar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Validar se pode criar produto antes de salvar
     */
    protected function beforeCreate(): void
    {
        $tenant = tenancy()->tenant;

        if (!$tenant->canCreateProduct()) {
            $stats = $tenant->usageStats();
            $limit = $stats['products']['limit'];

            Notification::make()
                ->danger()
                ->title('Limite de produtos atingido')
                ->body("Você atingiu o limite de {$limit} produtos do seu plano. Faça upgrade para adicionar mais produtos.")
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
    }
}
