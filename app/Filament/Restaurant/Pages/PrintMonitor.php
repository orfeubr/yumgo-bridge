<?php

namespace App\Filament\Restaurant\Pages;

use App\Events\NewOrderEvent;
use App\Models\BridgeStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

class PrintMonitor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $slug = 'monitor-impressao'; // URL em português
    protected static string $view = 'filament.restaurant.pages.print-monitor';
    protected static ?string $navigationLabel = 'Monitor de Impressão';
    protected static ?string $title = 'Monitor de Impressão';
    protected static ?int $navigationSort = 95;
    protected static ?string $navigationGroup = '⚙️ Configurações';

    /**
     * Polling: atualiza a cada 5 segundos
     */
    protected function getRefreshInterval(): ?int
    {
        return 5;
    }

    /**
     * ⭐ Status do Bridge (do banco de dados)
     */
    #[Computed]
    public function bridgeStatus(): array
    {
        $bridge = BridgeStatus::first();

        if (!$bridge) {
            return [
                'status' => 'offline',
                'message' => 'Bridge nunca conectado',
                'color' => 'gray',
                'icon' => 'heroicon-o-x-circle',
                'version' => null,
                'last_seen' => null,
                'printers' => [],
            ];
        }

        $isOnline = $bridge->isOnline();

        return [
            'status' => $isOnline ? 'online' : 'offline',
            'message' => $isOnline
                ? 'Conectado (' . $bridge->last_seen . ')'
                : 'Offline (' . $bridge->last_seen . ')',
            'color' => $isOnline ? 'success' : 'danger',
            'icon' => $isOnline ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            'version' => $bridge->version,
            'last_seen' => $bridge->last_seen,
            'printers' => $bridge->printers ?? [],
        ];
    }

    /**
     * ⭐ Pedidos com falha de impressão (últimas 24h)
     */
    #[Computed]
    public function failedPrints(): Collection
    {
        return Order::printFailed()
            ->with(['customer'])
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->customer->name ?? 'N/A',
                    'total' => 'R$ ' . number_format($order->total, 2, ',', '.'),
                    'error' => $order->print_error,
                    'attempts' => $order->print_attempts,
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                ];
            });
    }

    /**
     * ⭐ Pedidos pendentes de impressão (últimas 24h)
     */
    #[Computed]
    public function pendingPrints(): Collection
    {
        return Order::where('print_status', 'pending')
            ->with(['customer'])
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->customer->name ?? 'N/A',
                    'total' => 'R$ ' . number_format($order->total, 2, ',', '.'),
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                ];
            });
    }

    /**
     * ⭐ Histórico recente (últimas 20 impressões bem-sucedidas)
     */
    #[Computed]
    public function recentPrints(): Collection
    {
        return Order::where('print_status', 'printed')
            ->with(['customer'])
            ->orderBy('printed_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->customer->name ?? 'N/A',
                    'total' => 'R$ ' . number_format($order->total, 2, ',', '.'),
                    'printed_at' => $order->printed_at?->format('d/m/Y H:i'),
                ];
            });
    }

    /**
     * ⭐ Estatísticas (últimas 24h)
     */
    #[Computed]
    public function stats(): array
    {
        $yesterday = now()->subDay();

        return [
            'total' => Order::where('created_at', '>=', $yesterday)->count(),
            'printed' => Order::where('print_status', 'printed')
                ->where('created_at', '>=', $yesterday)
                ->count(),
            'pending' => Order::where('print_status', 'pending')
                ->where('created_at', '>=', $yesterday)
                ->count(),
            'failed' => Order::printFailed()
                ->where('created_at', '>=', $yesterday)
                ->count(),
        ];
    }

    /**
     * ⭐ Badge no menu mostrando falhas
     */
    public static function getNavigationBadge(): ?string
    {
        $failedCount = Order::printFailed()
            ->where('created_at', '>=', now()->subDay())
            ->count();

        return $failedCount > 0 ? (string) $failedCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    /**
     * ⭐ Actions (botões no topo da página)
     */
    protected function getHeaderActions(): array
    {
        return [
            // Reimprimir pedido específico
            Action::make('reprint')
                ->label('Reimprimir Pedido')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\TextInput::make('order_number')
                        ->label('Número do Pedido')
                        ->required()
                        ->placeholder('Ex: 1234'),
                ])
                ->action(function (array $data) {
                    $order = Order::where('order_number', $data['order_number'])->first();

                    if (!$order) {
                        \Filament\Notifications\Notification::make()
                            ->title('Pedido não encontrado')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Disparar evento de reimpressão
                    event(new NewOrderEvent($order, true)); // true = forceReprint

                    \Filament\Notifications\Notification::make()
                        ->title('Reimpressão solicitada')
                        ->body("Pedido #{$order->order_number} será reimpresso")
                        ->success()
                        ->send();
                }),

            // Reimprimir TODOS os pedidos com falha
            Action::make('reprint_all_failed')
                ->label('Reimprimir Todas Falhas')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reimprimir todos os pedidos com falha?')
                ->modalDescription('Isso vai reimprimir TODOS os pedidos que falharam nas últimas 24h.')
                ->action(function () {
                    $failedOrders = Order::printFailed()
                        ->where('created_at', '>=', now()->subDay())
                        ->get();

                    foreach ($failedOrders as $order) {
                        event(new NewOrderEvent($order, true)); // true = forceReprint
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('Reimpressão em massa iniciada')
                        ->body("{$failedOrders->count()} pedidos serão reimpressos")
                        ->success()
                        ->send();
                }),

            // Marcar falhas como vistas
            Action::make('mark_as_seen')
                ->label('Marcar Falhas como Vistas')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->action(function () {
                    // Marcar como "printing" para não aparecer mais na lista
                    Order::printFailed()
                        ->where('created_at', '>=', now()->subDay())
                        ->update(['print_status' => 'printing']);

                    \Filament\Notifications\Notification::make()
                        ->title('Falhas marcadas como vistas')
                        ->success()
                        ->send();
                }),

            // Testar impressora
            Action::make('test_print')
                ->label('Testar Impressora')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Select::make('order_id')
                        ->label('Selecione um Pedido para Teste')
                        ->options(
                            Order::where('created_at', '>=', now()->subDay())
                                ->orderBy('created_at', 'desc')
                                ->limit(50)
                                ->pluck('order_number', 'id')
                        )
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $order = Order::find($data['order_id']);

                    if (!$order) {
                        \Filament\Notifications\Notification::make()
                            ->title('Pedido não encontrado')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Disparar evento de impressão de teste (força reimpressão)
                    event(new NewOrderEvent($order, true));

                    \Filament\Notifications\Notification::make()
                        ->title('Impressão de teste disparada')
                        ->body("Pedido #{$order->order_number}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
