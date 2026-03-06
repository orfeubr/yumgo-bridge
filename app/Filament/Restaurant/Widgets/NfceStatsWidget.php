<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\FiscalNote;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class NfceStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Notas emitidas hoje
        $todayNotes = FiscalNote::whereDate('created_at', Carbon::today())->count();

        // Notas emitidas este mês
        $monthNotes = FiscalNote::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Notas com sucesso (autorizadas)
        $successNotes = FiscalNote::where('status', 'autorizada')->count();
        $totalNotes = FiscalNote::count();
        $successRate = $totalNotes > 0 ? round(($successNotes / $totalNotes) * 100, 1) : 0;

        // Notas com erro
        $errorNotes = FiscalNote::whereIn('status', ['rejeitada', 'erro'])->count();

        // Valor total faturado no mês
        $monthRevenue = FiscalNote::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'autorizada')
            ->sum('total_value');

        return [
            Stat::make('Notas Hoje', $todayNotes)
                ->description('Emitidas hoje')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success')
                ->chart([7, 12, 18, 15, 22, 28, $todayNotes]),

            Stat::make('Notas este Mês', $monthNotes)
                ->description('Total no mês atual')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary')
                ->chart(array_fill(0, 7, rand(10, 50))),

            Stat::make('Taxa de Sucesso', $successRate . '%')
                ->description("{$successNotes} autorizadas de {$totalNotes}")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($successRate >= 95 ? 'success' : ($successRate >= 80 ? 'warning' : 'danger'))
                ->chart([$successRate, $successRate - 5, $successRate + 2, $successRate]),

            Stat::make('Faturamento Mês', 'R$ ' . number_format($monthRevenue, 2, ',', '.'))
                ->description('Total faturado (NFC-e autorizadas)')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
        ];
    }
}
