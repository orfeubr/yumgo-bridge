<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\Order;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class RestaurantStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Pedidos de hoje
        $todayOrders = Order::whereDate('created_at', today())->count();
        $yesterdayOrders = Order::whereDate('created_at', today()->subDay())->count();
        $ordersTrend = $todayOrders - $yesterdayOrders;

        // Faturamento de hoje
        $todayRevenue = Order::whereDate('created_at', today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $yesterdayRevenue = Order::whereDate('created_at', today()->subDay())
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $revenueTrend = $todayRevenue - $yesterdayRevenue;

        // Ticket médio
        $avgTicket = $todayOrders > 0 ? $todayRevenue / $todayOrders : 0;

        // Novos clientes
        $newCustomers = Customer::whereDate('created_at', today())->count();

        return [
            Stat::make('Pedidos Hoje', $todayOrders)
                ->description($ordersTrend >= 0 ? "+{$ordersTrend} em relação a ontem" : "{$ordersTrend} em relação a ontem")
                ->descriptionIcon($ordersTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($ordersTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 4, 10, 5, 12, 4, $todayOrders]),

            Stat::make('Faturamento Hoje', 'R$ ' . Number::format($todayRevenue, 2))
                ->description($revenueTrend >= 0 ? '+R$ ' . Number::format($revenueTrend, 2) : 'R$ ' . Number::format($revenueTrend, 2))
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->chart([150, 200, 180, 220, 250, 190, $todayRevenue]),

            Stat::make('Ticket Médio', 'R$ ' . Number::format($avgTicket, 2))
                ->description('Valor médio por pedido')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Novos Clientes', $newCustomers)
                ->description('Cadastrados hoje')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),
        ];
    }
}
