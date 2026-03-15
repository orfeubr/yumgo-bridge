<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\Neighborhood;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NeighborhoodStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Neighborhood::count();
        $enabled = Neighborhood::where('is_active', true)->count();
        $disabled = $total - $enabled;
        $avgFee = Neighborhood::where('is_active', true)->avg('delivery_fee') ?? 0;

        return [
            Stat::make('Total de Bairros', $total)
                ->description('Bairros cadastrados no sistema')
                ->descriptionIcon('heroicon-m-map')
                ->color('primary'),

            Stat::make('Bairros Ativos', $enabled)
                ->description('Bairros disponíveis para delivery')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Bairros Inativos', $disabled)
                ->description('Bairros desativados')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Taxa Média', 'R$ ' . number_format($avgFee, 2, ',', '.'))
                ->description('Taxa média dos bairros ativos')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}
