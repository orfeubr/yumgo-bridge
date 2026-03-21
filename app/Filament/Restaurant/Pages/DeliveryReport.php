<?php

namespace App\Filament\Restaurant\Pages;

use App\Models\Delivery;
use App\Models\DeliveryDriver;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class DeliveryReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = '💰 Relatório de Entregas';
    protected static ?string $title = 'Relatório de Entregas - Pagamento';
    protected static ?string $navigationGroup = '🚚 Entregas';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.restaurant.pages.delivery-report';

    // Filtros
    public $startDate;
    public $endDate;
    public $driverId = null;
    public $paymentStatus = 'all'; // all, paid, unpaid

    public function mount(): void
    {
        // Padrão: últimos 7 dias
        $this->startDate = now()->subDays(7)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getDeliveriesByDriver(): Collection
    {
        $query = Delivery::query()
            ->with(['order.customer', 'driver'])
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [
                $this->startDate . ' 00:00:00',
                $this->endDate . ' 23:59:59'
            ]);

        // Filtro por entregador
        if ($this->driverId) {
            $query->where('driver_id', $this->driverId);
        }

        // Filtro por status de pagamento
        if ($this->paymentStatus === 'paid') {
            $query->whereNotNull('paid_at');
        } elseif ($this->paymentStatus === 'unpaid') {
            $query->whereNull('paid_at');
        }

        $deliveries = $query->orderBy('delivered_at', 'desc')->get();

        // Agrupar por entregador
        return $deliveries->groupBy('driver_id')->map(function ($driverDeliveries, $driverId) {
            $driver = DeliveryDriver::find($driverId);

            return [
                'driver' => $driver,
                'deliveries' => $driverDeliveries,
                'total_deliveries' => $driverDeliveries->count(),
                'total_amount' => $driverDeliveries->sum('delivery_fee'),
                'paid_deliveries' => $driverDeliveries->whereNotNull('paid_at')->count(),
                'unpaid_deliveries' => $driverDeliveries->whereNull('paid_at')->count(),
            ];
        });
    }

    public function getDriverOptions(): array
    {
        return DeliveryDriver::query()
            ->where('is_active', true)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function markAsPaid(int $driverId): void
    {
        try {
            $updated = Delivery::query()
                ->where('driver_id', $driverId)
                ->where('status', 'delivered')
                ->whereBetween('delivered_at', [
                    $this->startDate . ' 00:00:00',
                    $this->endDate . ' 23:59:59'
                ])
                ->whereNull('paid_at')
                ->update([
                    'paid_at' => now(),
                    'paid_by' => auth()->id(),
                ]);

            $driver = DeliveryDriver::find($driverId);

            Notification::make()
                ->success()
                ->title('Pagamento Registrado!')
                ->body("{$updated} entrega(s) de {$driver->name} marcadas como pagas.")
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erro ao registrar pagamento')
                ->body($e->getMessage())
                ->send();
        }
    }

    public function applyFilters(): void
    {
        // Método chamado quando filtros são alterados
        // A página será re-renderizada automaticamente
    }

    public function setQuickPeriod(string $period): void
    {
        match($period) {
            'today' => [
                $this->startDate = now()->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            'yesterday' => [
                $this->startDate = now()->subDay()->format('Y-m-d'),
                $this->endDate = now()->subDay()->format('Y-m-d'),
            ],
            'week' => [
                $this->startDate = now()->subDays(7)->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            'month' => [
                $this->startDate = now()->subDays(30)->format('Y-m-d'),
                $this->endDate = now()->format('Y-m-d'),
            ],
            default => null
        };
    }
}
