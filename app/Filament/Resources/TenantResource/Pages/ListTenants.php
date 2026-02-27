<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            // 🧹 Botão para limpar cache manualmente
            Actions\Action::make('clear_cache')
                ->label('Limpar Cache')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Limpar todo o cache?')
                ->modalDescription('Isso vai limpar o cache de aplicação, views, rotas, configuração e Filament. As alterações mais recentes serão aplicadas imediatamente.')
                ->modalSubmitActionLabel('Sim, limpar cache')
                ->action(function () {
                    try {
                        // Limpar cache de aplicação
                        Cache::flush();

                        // Limpar caches do Laravel
                        Artisan::call('config:clear');
                        Artisan::call('route:clear');
                        Artisan::call('view:clear');
                        Artisan::call('cache:clear');

                        // Limpar cache do Filament
                        Artisan::call('filament:clear-cached-components');

                        // Limpar views dos tenants
                        Artisan::call('tenants:run', ['commandname' => 'view:clear']);

                        \Filament\Notifications\Notification::make()
                            ->title('Cache limpo com sucesso!')
                            ->body('Todo o cache foi limpo. Atualize a página (Ctrl+F5) para ver as mudanças.')
                            ->success()
                            ->duration(5000)
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erro ao limpar cache')
                            ->body('Erro: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
