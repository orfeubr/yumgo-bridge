<?php

namespace App\Filament\Restaurant\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class PrintMonitor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static string $view = 'filament.restaurant.pages.print-monitor';
    protected static ?string $navigationLabel = 'Monitor de Impressão';
    protected static ?string $title = 'Monitor de Impressão';
    protected static ?int $navigationSort = 95;
    protected static ?string $navigationGroup = '⚙️ Configurações';

    public function mount(): void
    {
        // Atualizar timestamp de visualização
        Cache::put('print_monitor_last_view_' . tenant('id'), now(), 3600);
    }

    /**
     * Status do Bridge (online/offline)
     */
    public function getBridgeStatus(): array
    {
        $tenantId = tenant('id');
        $lastHeartbeat = Cache::get("bridge_heartbeat_{$tenantId}");

        if (!$lastHeartbeat) {
            return [
                'status' => 'offline',
                'message' => 'Bridge não conectado',
                'color' => 'danger',
            ];
        }

        $secondsAgo = now()->diffInSeconds($lastHeartbeat);

        if ($secondsAgo < 60) {
            return [
                'status' => 'online',
                'message' => 'Conectado há ' . $secondsAgo . 's',
                'color' => 'success',
            ];
        }

        return [
            'status' => 'stale',
            'message' => 'Última conexão há ' . round($secondsAgo / 60) . ' minutos',
            'color' => 'warning',
        ];
    }

    /**
     * Impressoras configuradas
     */
    public function getPrinters(): array
    {
        return Cache::get("bridge_printers_" . tenant('id'), []);
    }

    /**
     * Últimas impressões (histórico)
     */
    public function getPrintHistory(): array
    {
        $history = Cache::get("print_history_" . tenant('id'), []);
        return array_slice($history, -50); // Últimas 50
    }

    /**
     * Disparar impressão de teste
     */
    public function testPrint(): void
    {
        $tenantId = tenant('id');

        // Criar evento de teste
        event(new \App\Events\TestPrintEvent($tenantId));

        \Filament\Notifications\Notification::make()
            ->title('Evento de teste disparado!')
            ->body('Verifique se o Bridge imprimiu.')
            ->success()
            ->send();
    }

    /**
     * Limpar cache
     */
    public function clearCache(): void
    {
        $tenantId = tenant('id');

        Cache::forget("bridge_heartbeat_{$tenantId}");
        Cache::forget("bridge_printers_{$tenantId}");
        Cache::forget("print_history_{$tenantId}");

        \Filament\Notifications\Notification::make()
            ->title('Cache limpo!')
            ->success()
            ->send();
    }

    /**
     * Polling para atualizar status em tempo real
     */
    protected function getRefreshInterval(): ?int
    {
        return 5; // Atualiza a cada 5 segundos
    }
}
